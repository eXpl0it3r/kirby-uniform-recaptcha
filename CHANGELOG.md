# Changelog

## Unreleased

Switches the plugin to reCAPTCHA v3, which needs a score-based key. Existing v2 keys don't work anymore, see the README for the new configuration.

### Added

- `recaptchaField()` helper, which requests the token on submit and works with the Enter key and multiple forms (#4)
- `acceptableScore` option, the minimum score to accept a form submission
- `hostname` option to check where the token was created (#4)
- `sendRemoteIp` option, the IP of the visitor is no longer sent by default
- `scriptHost` option to load the script from `www.recaptcha.net`
- Support for Kirby 4 and 5 (#4)

### Changed

- Use reCAPTCHA v3 with score checks instead of v2
- Require PHP 8.1 and Uniform 5.6.2
- Send the verification as POST request, so the secret key doesn't end up in URLs and logs (#4)
- Load the reCAPTCHA script with `async` and `defer`
- Deprecate `recaptchaButton()` in favor of `recaptchaField()` (#4)

### Fixed

- Reject the "Over free quota." answer, which Google sends with success and a score of 0.9
- Reject the form when Google can't be reached, instead of showing a PHP warning (#4)
- Send the form without token when the reCAPTCHA script isn't loaded, instead of doing nothing on submit
- Escape the parameters of `recaptchaButton()`
- Load the translations with `require`, so they don't turn into `true` when loaded twice
- Install the plugin into `site/plugins` with Composer (#4)

## [1.0.2](https://github.com/eXpl0it3r/kirby-uniform-recaptcha/compare/1.0.1...1.0.2) - 2021-05-10

### Fixed

- Composer package name in the README

## [1.0.1](https://github.com/eXpl0it3r/kirby-uniform-recaptcha/compare/1.0.0...1.0.1) - 2021-01-22

### Fixed

- Error with the helper function

## [1.0.0](https://github.com/eXpl0it3r/kirby-uniform-recaptcha/releases/tag/1.0.0) - 2021-01-22

First release for Kirby 3 and reCAPTCHA v2.

### Added

- reCAPTCHA guard for Uniform
- `recaptchaButton()` and `recaptchaScript()` helpers
- English, German and Dutch translations
