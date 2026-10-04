# Uniform reCAPTCHA

A [Kirby](https://getkirby.com/) plugin implementing a [Google reCAPTCHA](https://docs.cloud.google.com/recaptcha/docs/overview) v3 guard for the [Uniform](https://github.com/mzur/kirby-uniform) plugin.

reCAPTCHA v3 runs invisibly in the background and rates each form submission with a score, there's no challenge for your visitors to solve.

## Requirements

- Kirby 3.10, 4 or 5
- PHP 8.1 or newer, note that Kirby 5 needs PHP 8.2
- [Uniform](https://github.com/mzur/kirby-uniform) 5.6.2 or newer, as it's the first version that has been tested with Kirby 5

## Installation

### Composer

```
composer require expl0it3r/kirby-uniform-recaptcha
```

Composer pulls in Uniform as well, if it isn't installed yet.

### Git Submodule

```
git submodule add https://github.com/eXpl0it3r/kirby-uniform-recaptcha.git site/plugins/uniform-recaptcha
```

### Download

[Download](https://github.com/eXpl0it3r/kirby-uniform-recaptcha/archive/master.zip) the repository and extract its content to `site/plugins/uniform-recaptcha`.

Keep in mind, that for the submodule and the download, you also have to install Uniform yourself.

## Keys

reCAPTCHA has become part of Google Cloud, which Google now calls "Fraud Defense". Classic keys have been moved to Google Cloud projects and keep on working, as such you don't need to change anything, if you've used the plugin before.

New keys can still be created on the [reCAPTCHA admin page](https://www.google.com/recaptcha/admin/create), which creates a Google Cloud project for you, or directly in the [Google Cloud console](https://console.cloud.google.com/security/recaptcha). Choose the "score-based" key type, which used to be called reCAPTCHA v3.

Google renamed a few things along the way:

- The site key is now called "Key ID"
- The secret key is now called "legacy secret key", you find it on the key under "Integration" and then "Use Legacy Key"

The plugin uses the `siteverify` API, which Google calls legacy, but still supports for plugins like this one.

## Configuration

Set the configuration in your `config.php` file:

```php
return [
    'expl0it3r.uniform-recaptcha.siteKey' => 'my-site-key',
    'expl0it3r.uniform-recaptcha.secretKey' => 'my-secret-key',
    'expl0it3r.uniform-recaptcha.acceptableScore' => 0.5,
];
```

- `siteKey` and `secretKey` are the keys from above
- `acceptableScore` is the minimum score between `0.0` and `1.0` that's required to accept the form submission, default `0.5`
- `hostname` makes the guard check that the token was created on this hostname, default empty, i.e. not checked
- `sendRemoteIp` sends the IP of the visitor along to Google, default `false`, as Google lists it as optional
- `scriptHost` is the host the reCAPTCHA script is loaded from, default `www.google.com`. Set it to `www.recaptcha.net` where `www.google.com` isn't reachable.

### Scores and Quota

Google's free tier covers 10'000 checks a month, shared across all the sites of your Google Cloud organization. Once a project without billing goes over that, Google still answers with "success", but adds an "Over free quota." error. The guard rejects these, as such going over the quota means that forms will be rejected until the next month, not that bots get through. If your forms see more traffic, you'll have to enable billing for the project.

Without billing, Google only returns the scores `0.1`, `0.3`, `0.7` and `0.9`. As such any `acceptableScore` between `0.31` and `0.7` behaves the same as the default `0.5`.

## Usage

### Template

reCAPTCHA v3 needs JavaScript to get a token right before the form is sent. Add the `recaptchaField()` helper inside your `<form>` and the `recaptchaScript()` helper somewhere on the page, e.g. before `</body>`:

```html+php
<form action="<?= $page->url() ?>" method="post">
    <label for="name" class="required">Name</label>
    <input<?php if ($form->error('name')): ?> class="erroneous"<?php endif; ?> name="name" type="text" value="<?= $form->old('name') ?>">

    <!-- ... -->

    <?= csrf_field() ?>
    <?= recaptchaField() ?>
    <button type="submit">Submit</button>
</form>
<?= recaptchaScript() ?>
```

`recaptchaField()` outputs a hidden input and a small script that requests the token when the form is submitted. Since it hooks into the form's `submit` event, the button and the Enter key both work, you can use any submit button you like, the form doesn't need an `id` and multiple forms on the same page are fine. The name and value of the used submit button are kept, in case your controller relies on them.

If the reCAPTCHA script couldn't be loaded, e.g. because it's blocked or the visitor hasn't given consent yet, the form is sent without a token and the guard rejects it with a message.

`recaptchaScript()` loads the reCAPTCHA script from Google with your site key. You can also include `https://www.google.com/recaptcha/api.js?render=my-site-key` yourself, e.g. to only load it after the visitor gave consent.

`recaptchaButton('Submit', 'btn', 'ContactForm')` is still around for existing forms, but it's deprecated. Its callback doesn't trigger, when the form is sent with the Enter key and it needs the `id` of the form. Use `recaptchaField()` instead.

### Controller

In your controller you can use the [magic method](https://kirby-uniform.readthedocs.io/en/latest/guards/guards/#magic-methods) `recaptchaGuard()` to enable the reCAPTCHA guard:

```php
$form = new Form(/* ... */);

if ($kirby->request()->is('POST')) {
    $form->recaptchaGuard()
         ->emailAction(/* ... */);
}
```

## Privacy

Since April 2nd, 2026 Google acts as data processor for reCAPTCHA, as such you're responsible for the data and should mention reCAPTCHA in your privacy policy. The old "Google Privacy Policy and Terms of Service apply" text is no longer needed and has been removed from the badge.

You may hide the reCAPTCHA badge, as long as the page shows "This site is protected by reCAPTCHA." somewhere near the form:

```css
.grecaptcha-badge {
    visibility: hidden;
}
```

Note that reCAPTCHA sets a cookie and depending on your visitors, e.g. from the EU, you may need their consent before loading the script. This isn't legal advice, check what applies to your site.

## Credits

- Thanks to Johannes Pichler for the [Kirby 2 plugin](https://github.com/fetzi/kirby-uniform-recaptcha)!
- Thanks to Moritz Profitlich for the Kirby 5 upgrade and the `recaptchaField()` helper!
- A million thanks to the whole Kirby Team! ❤
