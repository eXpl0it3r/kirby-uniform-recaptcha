# Uniform reCAPTCHA

A [Kirby 3](https://getkirby.com/) plugin implementing a Google reCAPTCHA guard for the [Uniform](https://github.com/mzur/kirby-uniform) plugin.

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

Set the configration in your `config.php` file:

```php
return [
  'expl0it3r.uniform-recaptcha.siteKey' => 'my-site-key',
  'expl0it3r.uniform-recaptcha.secretKey' => 'my-secret-key'
];
```

## Usage

### Template

You can use the provided helper function to embed the reCAPTCHA into your template:

```html+php
<?= recaptchaField() ?>
```

In order for reCAPTCHA to work, you need to provide the reCAPTCHA JavaScript file from Google.

Either include [the script](https://www.google.com/recaptcha/api.js) yourself or use the helper function `recaptchaScript()` in your template.

**Example**

```html+php
<form action="<?= $page->url() ?>" method="post">
    <label for="name" class="required">Name</label>
    <input<?php if ($form->error('name')): ?> class="erroneous"<?php endif; ?> name="name" type="text" value="<?= $form->old('name') ?>">

    <!-- ... -->

    <?php echo csrf_field() ?>
    <?php echo recaptchaField() ?>
    <input type="submit" value="Submit">
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