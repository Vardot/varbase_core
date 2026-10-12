<?php

declare(strict_types=1);

namespace Drupal\varbase_core\Plugin\Action;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eca\Attribute\EcaAction;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Service\YamlParser;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Sends an outgoing HTTP request and stores the response in a token.
 *
 * A deliberately small replacement for eca_helper_http_request: ECA Helper
 * is unsupported (SA-CONTRIB-2026-193). The response token keeps the same
 * shape ([token:status], [token:client_error], [token:body], [token:json],
 * [token:headers]) so existing models need only the plugin id changed.
 *
 * Left out on purpose: free-form Guzzle request options (they accept "sink",
 * "verify" and others that write files or weaken TLS), multipart uploads
 * (they open arbitrary local paths) and cookies. Only http and https URLs
 * are accepted, and every request has a timeout.
 */
#[Action(
  id: 'varbase_core_http_request',
  label: new TranslatableMarkup('HTTP request')
)]
#[EcaAction(
  description: new TranslatableMarkup('Send an HTTP request and store the response in a token.')
)]
final class HttpRequest extends ConfigurableActionBase {

  /**
   * The methods this action may send.
   */
  private const METHODS = ['get', 'post', 'put', 'patch', 'delete', 'head'];

  /**
   * The longest timeout allowed, in seconds.
   */
  private const MAX_TIMEOUT = 30;

  /**
   * The YAML parser.
   */
  protected YamlParser $yamlParser;

  /**
   * The HTTP client.
   */
  protected ClientInterface $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->yamlParser = $container->get('eca.service.yaml_parser');
    $instance->httpClient = $container->get('http_client');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'method' => 'get',
      'url' => '',
      'data' => '',
      'data_serialization' => 'raw',
      'query_parameters' => '',
      'headers' => '',
      'timeout' => '5',
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Method'),
      '#default_value' => $this->configuration['method'],
      '#options' => array_combine(self::METHODS, array_map('strtoupper', self::METHODS)),
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#description' => $this->t('An http or https URL. Tokens are replaced.'),
      '#default_value' => $this->configuration['url'],
      '#maxlength' => 2048,
      '#required' => TRUE,
    ];
    $form['data'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Data'),
      '#description' => $this->t('The request body. Tokens are replaced.'),
      '#default_value' => $this->configuration['data'],
    ];
    $form['data_serialization'] = [
      '#type' => 'select',
      '#title' => $this->t('Data serialization'),
      '#default_value' => $this->configuration['data_serialization'],
      '#options' => [
        'raw' => $this->t('Raw'),
        'json' => $this->t('JSON'),
        'url_encode' => $this->t('URL encoded form (YAML input)'),
      ],
    ];
    $form['query_parameters'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Query parameters'),
      '#description' => $this->t('YAML, for example <code>key: "[node:title]"</code>.'),
      '#default_value' => $this->configuration['query_parameters'],
    ];
    $form['headers'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Headers'),
      '#description' => $this->t('YAML, for example <code>Accept: application/json</code>.'),
      '#default_value' => $this->configuration['headers'],
    ];
    $form['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout (seconds)'),
      '#min' => 1,
      '#max' => self::MAX_TIMEOUT,
      '#default_value' => $this->configuration['timeout'],
    ];
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of response token'),
      '#description' => $this->t('Holds [token_name:status], [token_name:client_error] (0 on success), [token_name:body], [token_name:json] when the body is JSON, and [token_name:headers].'),
      '#default_value' => $this->configuration['token_name'],
      '#required' => TRUE,
      '#eca_token_reference' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    foreach (array_keys($this->defaultConfiguration()) as $key) {
      if ($form_state->hasValue($key)) {
        $this->configuration[$key] = (string) $form_state->getValue($key);
      }
    }
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $method = strtolower((string) $this->configuration['method']);
    if (!in_array($method, self::METHODS, TRUE)) {
      $this->logger->error('HTTP request: unsupported method @method.', ['@method' => $method]);
      return;
    }

    $url = trim((string) $this->tokenService->replaceClear((string) $this->configuration['url']));
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], TRUE)) {
      $this->logger->error('HTTP request: not a valid http or https URL.');
      return;
    }

    $timeout = (int) $this->configuration['timeout'];
    $options = [
      'timeout' => min(max($timeout, 1), self::MAX_TIMEOUT),
      'connect_timeout' => min(max($timeout, 1), self::MAX_TIMEOUT),
      // A 4xx or 5xx answer throws, so it sets client_error like
      // eca_helper_http_request did; models branch on that.
      'http_errors' => TRUE,
    ];

    try {
      $query = $this->parseYaml((string) $this->configuration['query_parameters']);
      if ($query) {
        $options['query'] = $query;
      }
      $headers = $this->parseYaml((string) $this->configuration['headers']);
      if ($headers) {
        $options['headers'] = $headers;
      }
      $data = (string) $this->configuration['data'];
      if ($data !== '') {
        $data = (string) $this->tokenService->replaceClear($data);
        switch ($this->configuration['data_serialization']) {
          case 'json':
            $options['json'] = Json::decode($data) ?? $data;
            break;

          case 'url_encode':
            $options['form_params'] = (array) $this->yamlParser->parse($data);
            break;

          default:
            $options['body'] = $data;
        }
      }
    }
    catch (ParseException) {
      $this->logger->error('HTTP request: the query parameters, headers or data are not valid YAML.');
      return;
    }

    try {
      $response = $this->httpClient->request($method, $url, $options);
      $body = (string) $response->getBody();
      $result = [
        'body' => $body,
        'headers' => $response->getHeaders(),
        'status' => $response->getStatusCode(),
        'client_error' => 0,
      ];
      if ($body !== '' && json_validate($body)) {
        $result['json'] = Json::decode($body);
      }
    }
    catch (GuzzleException $e) {
      $this->logger->warning('HTTP request to @host failed: @message', [
        '@host' => parse_url($url, PHP_URL_HOST),
        '@message' => $e->getMessage(),
      ]);
      $response = $e instanceof RequestException ? $e->getResponse() : NULL;
      $result = [
        'body' => $response ? (string) $response->getBody() : '',
        'headers' => $response ? $response->getHeaders() : [],
        'status' => $response ? $response->getStatusCode() : $e->getCode(),
        'client_error' => 1,
      ];
    }

    $this->tokenService->addTokenData($this->configuration['token_name'], $result);
  }

  /**
   * Replaces tokens in a YAML string and parses it.
   *
   * @param string $yaml
   *   The YAML text, possibly empty.
   *
   * @return array
   *   The parsed values, or an empty array.
   *
   * @throws \Symfony\Component\Yaml\Exception\ParseException
   */
  protected function parseYaml(string $yaml): array {
    if (trim($yaml) === '') {
      return [];
    }
    $parsed = $this->yamlParser->parse((string) $this->tokenService->replaceClear($yaml));
    return is_array($parsed) ? $parsed : [];
  }

}
