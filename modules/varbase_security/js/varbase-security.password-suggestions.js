(function passwordSuggestionsIIFE($, Drupal) {
  Drupal.evaluatePasswordStrength = function evaluatePasswordStrength(
    password,
    passwordSettings,
  ) {
    password = password.trim();
    let indicatorText;
    let indicatorClass;
    let weaknesses = 0;
    let strength = 100;
    let msg = [];
    const hasLowercase = /[a-z]/.test(password);
    const hasUppercase = /[A-Z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const hasPunctuation = /[^a-zA-Z0-9]/.test(password);
    const $usernameBox = $('input.username');
    const username =
      $usernameBox.length > 0 ? $usernameBox.val() : passwordSettings.username;

    if (password.length < passwordSettings.minimal_length) {
      msg.push(passwordSettings.tooShort);
      strength -= (passwordSettings.minimal_length - password.length) * 5 + 30;
    }

    if (!hasLowercase) {
      msg.push(passwordSettings.addLowerCase);
      weaknesses += 1;
    }

    if (!hasUppercase) {
      msg.push(passwordSettings.addUpperCase);
      weaknesses += 1;
    }

    if (!hasNumbers) {
      msg.push(passwordSettings.addNumbers);
      weaknesses += 1;
    }

    if (!hasPunctuation) {
      msg.push(passwordSettings.addPunctuation);
      weaknesses += 1;
    }

    switch (weaknesses) {
      case 1:
        strength -= 12.5;
        break;

      case 2:
        strength -= 25;
        break;

      case 3:
      case 4:
        strength -= 40;
        break;

      default:
        break;
    }

    if (password !== '' && password.toLowerCase() === username.toLowerCase()) {
      msg.push(passwordSettings.sameAsUsername);
      strength = 5;
    }

    const cssClasses = Drupal.user.password.css;

    if (strength < 60) {
      indicatorText = passwordSettings.weak;
      indicatorClass = cssClasses.passwordWeak;
    } else if (strength < 70) {
      indicatorText = passwordSettings.fair;
      indicatorClass = cssClasses.passwordFair;
    } else if (strength < 80) {
      indicatorText = passwordSettings.good;
      indicatorClass = cssClasses.passwordGood;
    } else if (strength <= 100) {
      indicatorText = passwordSettings.strong;
      indicatorClass = cssClasses.passwordStrong;
    }

    const messageTips = msg;
    msg = ''
      .concat(passwordSettings.hasWeaknesses, '<ul><li>')
      .concat(msg.join('</li><li>'), '</li></ul>');
    return Drupal.deprecatedProperty({
      target: {
        strength,
        message: msg,
        indicatorText,
        indicatorClass,
        messageTips,
      },
      deprecatedProperty: 'message',
      message:
        'The message property is deprecated in drupal:9.1.0 and is removed from drupal:10.0.0. The markup should be constructed using messageTips property and Drupal.theme.passwordSuggestions. See https://www.drupal.org/node/3130352',
    });
  };
})(jQuery, Drupal);
