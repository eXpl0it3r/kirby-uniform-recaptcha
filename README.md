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

## Usage

### Template

reCAPTCHA v3 requires the form submission to happen through JavaScript.
You can use the provided helper function, which creates a JavaScript callback function and an HTML `<button`>:

```html+php
<?= recaptchaButton('Submit', 'btn', 'ContactForm') ?>
```

Where the parameters are in order:
- The text on the button itself
- Additional CSS classes
- The ID of the form

If you want full control, you can write something like the following:

```html+php
<script>
  function onRecaptchaFormSubmit(token) {
    document.getElementById("ContactForm").submit();
  }
</script>
<button class="g-recaptcha btn"
        data-sitekey="<?= option('expl0it3r.uniform-recaptcha.siteKey') ?>"
        data-callback="onRecaptchaFormSubmit"
        data-action="UniformAction">Submit</button>
```

**Note:** The `data-action` is critical as it will be checked in the backend as well.

In order for reCAPTCHA to work, you need to provide the reCAPTCHA JavaScript file from Google.

Either include [the script](https://www.google.com/recaptcha/api.js) yourself or use the helper function `recaptchaScript()` in your template.

**Example**

```html+php
<form action="<?= $page->url() ?>" method="post" id="ContactForm">
    <label for="name" class="required">Name</label>
    <input<?php if ($form->error('name')): ?> class="erroneous"<?php endif; ?> name="name" type="text" value="<?= $form->old('name') ?>">

    <!-- ... -->

    <?= csrf_field() ?>
    <?= recaptchaButton('Submit', 'btn', 'ContactForm') ?>
</form>
<?= recaptchaScript() ?>
```

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