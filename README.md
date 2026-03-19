# MyAdmin Piwik Analytics Plugin

Piwik/Matomo analytics integration plugin for the [MyAdmin](https://github.com/detain/myadmin) control-panel framework. Provides event-driven hooks for menu registration, requirement loading, and settings management within the MyAdmin plugin architecture.

[![Build Status](https://github.com/detain/myadmin-piwik-analytics/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-piwik-analytics/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-piwik-analytics/version)](https://packagist.org/packages/detain/myadmin-piwik-analytics)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-piwik-analytics/downloads)](https://packagist.org/packages/detain/myadmin-piwik-analytics)
[![License](https://poser.pugx.org/detain/myadmin-piwik-analytics/license)](https://packagist.org/packages/detain/myadmin-piwik-analytics)

## Requirements

- PHP 8.2 or later
- ext-soap
- Symfony EventDispatcher 5.x, 6.x, or 7.x

## Installation

```sh
composer require detain/myadmin-piwik-analytics
```

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

This package is licensed under the [LGPL-2.1](LICENSE) license.
