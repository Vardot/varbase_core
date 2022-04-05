<?php

namespace Drupal\varbase_security\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Configure example settings for this site.
 */
class PasswordSuggestionsSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'varbase_security.password_suggestions.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_suggestions_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS)->get();

    $password_policy_link_html = Link::fromTextAndUrl($this->t('this configuration page.'), Url::fromRoute('entity.password_policy.collection'))->toString();

    $form['#prefix'] = '<div class="messages messages--warning"><strong> ' . $this->t("Note:") . ' </strong>' . $this->t("This form is used to update the password suggestions appearing to the end-user when creating or changing a password. Editing the text below will not affect the Password Policy constraints. If you want to edit your site's Password Policy constraints, you must edit your default Password Policy from") . " " . $password_policy_link_html . " " . $this->t("After you change your Password Policy settings, reflect your changes in this form to properly communicate your policy to your users.") . "</div>";

    $form['confirm_password_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Confirm password suggestions'),
      '#open' => TRUE,
      '#weight' => 0,
    ];

    $form['confirm_password_settings']['confirmTitle'] = [
      '#type' => 'textfield',
      '#title' => 'Confirm title',
      '#default_value' => $config['confirmTitle'] ?? $this->t('Password match:'),
    ];

    $form['confirm_password_settings']['confirmSuccess'] = [
      '#type' => 'textfield',
      '#title' => 'Confirm success',
      '#default_value' => $config['confirmSuccess'] ?? $this->t('yes'),
    ];

    $form['confirm_password_settings']['confirmFailure'] = [
      '#type' => 'textfield',
      '#title' => 'Confirm failure',
      '#default_value' => $config['confirmFailure'] ?? $this->t('no'),
    ];

    $form['confirm_password_settings']['strengthTitle'] = [
      '#type' => 'textfield',
      '#title' => 'Strength title',
      '#default_value' => $config['strengthTitle'] ?? $this->t('Password strength:'),
    ];

    $form['confirm_password_settings']['hasWeaknesses'] = [
      '#type' => 'textfield',
      '#title' => 'Has weaknesses',
      '#default_value' => $config['hasWeaknesses'] ?? $this->t('Recommendations to make your password stronger:'),
    ];

    $form['confirm_password_settings']['tooShort'] = [
      '#type' => 'textfield',
      '#title' => 'Too short',
      '#default_value' => $config['tooShort'] ?? $this->t('Make it at least 8 characters.'),
    ];

    $form['confirm_password_settings']['addLowerCase'] = [
      '#type' => 'textfield',
      '#title' => 'Add lowerCase',
      '#default_value' => $config['addLowerCase'] ?? $this->t('Add lowercase letters.'),
    ];

    $form['confirm_password_settings']['addUpperCase'] = [
      '#type' => 'textfield',
      '#title' => 'Add upperr case',
      '#default_value' => $config['addUpperCase'] ?? $this->t('Add uppercase letters'),
    ];

    $form['confirm_password_settings']['addNumbers'] = [
      '#type' => 'textfield',
      '#title' => 'Add numbers',
      '#default_value' => $config['addNumbers'] ?? $this->t('Add numbers'),
    ];

    $form['confirm_password_settings']['addPunctuation'] = [
      '#type' => 'textfield',
      '#title' => 'Add punctuation',
      '#default_value' => $config['addPunctuation'] ?? $this->t('Add punctuation'),
    ];

    $form['confirm_password_settings']['sameAsUsername'] = [
      '#type' => 'textfield',
      '#title' => 'Same as username',
      '#default_value' => $config['sameAsUsername'] ?? $this->t('Make it different from your username'),
    ];

    $form['confirm_password_settings']['weak'] = [
      '#type' => 'textfield',
      '#title' => 'Weak label',
      '#default_value' => $config['weak'] ?? $this->t('Weak'),
    ];

    $form['confirm_password_settings']['fair'] = [
      '#type' => 'textfield',
      '#title' => 'Fair label',
      '#default_value' => $config['fair'] ?? $this->t('Fair'),
    ];

    $form['confirm_password_settings']['good'] = [
      '#type' => 'textfield',
      '#title' => 'Good label',
      '#default_value' => $config['good'] ?? $this->t('Good'),
    ];

    $form['confirm_password_settings']['strong'] = [
      '#type' => 'textfield',
      '#title' => 'Strong label',
      '#default_value' => $config['strong'] ?? $this->t('Strong'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#attributes' => ['class' => ['button--primary']],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $submitted_config = $form_state->getValues();
    $new_config = [];

    $new_config["confirmTitle"] = htmlspecialchars($submitted_config['confirmTitle'], ENT_QUOTES);
    $new_config["confirmSuccess"] = htmlspecialchars($submitted_config['confirmSuccess'], ENT_QUOTES);
    $new_config["confirmFailure"] = htmlspecialchars($submitted_config['confirmFailure'], ENT_QUOTES);
    $new_config["strengthTitle"] = htmlspecialchars($submitted_config['strengthTitle'], ENT_QUOTES);
    $new_config["hasWeaknesses"] = htmlspecialchars($submitted_config['hasWeaknesses'], ENT_QUOTES);
    $new_config["tooShort"] = htmlspecialchars($submitted_config['tooShort'], ENT_QUOTES);
    $new_config["addLowerCase"] = htmlspecialchars($submitted_config['addLowerCase'], ENT_QUOTES);
    $new_config["addUpperCase"] = htmlspecialchars($submitted_config['addUpperCase'], ENT_QUOTES);
    $new_config["addNumbers"] = htmlspecialchars($submitted_config['addNumbers'], ENT_QUOTES);
    $new_config["addPunctuation"] = htmlspecialchars($submitted_config['addPunctuation'], ENT_QUOTES);
    $new_config["sameAsUsername"] = htmlspecialchars($submitted_config['sameAsUsername'], ENT_QUOTES);
    $new_config["weak"] = htmlspecialchars($submitted_config['weak'], ENT_QUOTES);
    $new_config["fair"] = htmlspecialchars($submitted_config['fair'], ENT_QUOTES);
    $new_config["good"] = htmlspecialchars($submitted_config['good'], ENT_QUOTES);
    $new_config["strong"] = htmlspecialchars($submitted_config['strong'], ENT_QUOTES);

    $config->set("confirmTitle", $new_config['confirmTitle'])->save(TRUE);
    $config->set("confirmSuccess", $new_config['confirmSuccess'])->save(TRUE);
    $config->set("confirmFailure", $new_config['confirmFailure'])->save(TRUE);
    $config->set("strengthTitle", $new_config['strengthTitle'])->save(TRUE);
    $config->set("hasWeaknesses", $new_config['hasWeaknesses'])->save(TRUE);
    $config->set("tooShort", $new_config['tooShort'])->save(TRUE);
    $config->set("addLowerCase", $new_config['addLowerCase'])->save(TRUE);
    $config->set("addUpperCase", $new_config['addUpperCase'])->save(TRUE);
    $config->set("addNumbers", $new_config['addNumbers'])->save(TRUE);
    $config->set("addPunctuation", $new_config['addPunctuation'])->save(TRUE);
    $config->set("sameAsUsername", $new_config['sameAsUsername'])->save(TRUE);
    $config->set("weak", $new_config['weak'])->save(TRUE);
    $config->set("fair", $new_config['fair'])->save(TRUE);
    $config->set("good", $new_config['good'])->save(TRUE);
    $config->set("strong", $new_config['strong'])->save(TRUE);

    parent::submitForm($form, $form_state);
  }

}
