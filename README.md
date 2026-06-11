# Uniform reCAPTCHA

A [Kirby 3, 4 & 5](https://getkirby.com/) plugin implementing a [Google reCAPTCHA v3](https://developers.google.com/recaptcha/docs/v3) guard for the [Uniform](https://github.com/mzur/kirby-uniform) plugin.

## Installation

### Download

- [Download](/archive/master.zip) the repository
- Extract the content to `site/plugins/uniform-recaptcha`

### Git Submodule

Add the plugin as Git submodule:

```
git submodule add https://github.com/eXpl0it3r/kirby-uniform-recaptcha.git site/plugins/uniform-recaptcha
```

### Composer

Add the plugin to your repository:

```
composer require expl0it3r/kirby-uniform-recaptcha
```

## Configuration

Set the configuration in your `config.php` file:

```php
return [
  'expl0it3r.uniform-recaptcha.siteKey' => 'my-site-key',
  'expl0it3r.uniform-recaptcha.secretKey' => 'my-secret-key',
  'expl0it3r.uniform-recaptcha.acceptableScore' => 0.5
];
```

- `siteKey` & `secretKey` can be found on the [reCAPTCHA admin page](https://www.google.com/recaptcha/admin/)
- `acceptableScore` is the minimum score in range `0.0` to `1.0` (default `0.5`) required to accept the form submission, see the [reCAPTCHA documentation](https://developers.google.com/recaptcha/docs/v3)
- `hostname` is optional (default empty / disabled). When set, the guard additionally checks that the `hostname` returned by Google matches this value.

## Usage

### Template

reCAPTCHA v3 runs invisibly and requires the form submission to happen through JavaScript.
Add the `recaptchaField()` helper inside your `<form>` and the `recaptchaScript()` helper somewhere on the page (e.g. before `</body>`):

```html+php
<?= recaptchaField() ?>
```

`recaptchaField()` outputs a hidden input and a small script that, on submit, obtains a reCAPTCHA token and resubmits the form. It binds to its own form via the `submit` event, so:

- It works no matter how the form is submitted (button click **or** the Enter key).
- You keep full control over your markup — use any `<button type="submit">` you like.
- No form `id` is required, and it can be used for multiple forms on the same page.
- The original submit button's `name`/`value` is preserved (via `requestSubmit()`), so server-side form routing keeps working.

In order for reCAPTCHA to work, you need to load the reCAPTCHA JavaScript file from Google.
Either include [the script](https://www.google.com/recaptcha/api.js?render=your-site-key) yourself (with the `render` parameter set to your site key) or use the helper function `recaptchaScript()`.

**Example**

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

> **Deprecated:** `recaptchaButton('Submit', 'btn', 'ContactForm')` is still available for backwards
> compatibility but no longer recommended. It relies on reCAPTCHA's `data-callback` flow, which does
> not trigger on Enter-key submits and requires knowing the form `id`. Use `recaptchaField()` instead.

### Controller

In your controller you can use the [magic method](https://kirby-uniform.readthedocs.io/en/latest/guards/guards/#magic-methods) `recaptchaGuard()` to enable the reCAPTCHA guard:

```php
$form = new Form(/* ... */);

if ($kirby->request()->is('POST'))
{
    $form->recaptchaGuard()
         ->emailAction(/* ... */);
}
```

## Credits

- Thanks to Johannes Pichler for the [Kirby 2 plugin](https://github.com/fetzi/kirby-uniform-recaptcha)!
- A million thanks to the whole Kirby Team! ❤