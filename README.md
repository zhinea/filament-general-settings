# Filament General Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/joaopaulolndev/filament-general-settings.svg?style=flat-square)](https://packagist.org/packages/joaopaulolndev/filament-general-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/joaopaulolndev/filament-general-settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/joaopaulolndev/filament-general-settings/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/joaopaulolndev/filament-general-settings/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/joaopaulolndev/filament-general-settings/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/joaopaulolndev/filament-general-settings.svg?style=flat-square)](https://packagist.org/packages/joaopaulolndev/filament-general-settings)



Create really fast and easily general settings for your Laravel Filament project.

## Features & Screenshots


## Installation

You can install the package via composer:

```bash
composer require joaopaulolndev/filament-general-settings
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-general-settings-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-general-settings-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-general-settings-views"
```

This is the contents of the published config file:

```php
return [
    'show_application_tab' => true,
    'show_analytics_tab' => true,
    'show_seo_tab' => true,
    'show_email_tab' => true,
    'show_social_networks_tab' => true,
    'expiration_cache_config_time' => 60,
];
```

## Usage
Add in AdminPanelProvider.php
```php
->plugins([
    FilamentGeneralSettingsPlugin::make()
])
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [João Paulo Leite Nascimento](https://github.com/joaopaulolndev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
