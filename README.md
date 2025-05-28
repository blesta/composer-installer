# Composer Installer

[![Tests](https://github.com/blesta/composer-installer/actions/workflows/tests.yml/badge.svg)](https://github.com/blesta/composer-installer/actions/workflows/tests.yml)
[![Coverage Status](https://coveralls.io/repos/blesta/composer-installer/badge.svg?branch=master&service=github)](https://coveralls.io/github/blesta/composer-installer?branch=master)

A library for installing Blesta extensions using [composer](http://getcomposer.org).

## Requirements

- PHP 8.1 or later
- Composer 2.0 or later

## Usage

To use simply add `blesta/composer-installer` as a requirement to your extension's `composer.json` file,
and tell composer which type of extension you've created.

```json
    "type": "blesta-plugin",
    "require": {
        "blesta/composer-installer": "~2.0"
    }
```

In the above example, we've set `blesta-plugin` as the type of composer package.
See below for a complete list of supported types, and choose the appropriate one for your extension.

**Supported Types**

- **blesta-plugin**
    - Use for [Plugins](https://docs.blesta.com/display/dev/Plugins)
- **blesta-module**
    - Use for [Modules](https://docs.blesta.com/display/dev/Modules)
- **blesta-gateway-merchant**
    - Use for [Merchant Gateways](https://docs.blesta.com/display/dev/Merchant+Gateways)
- **blesta-gateway-nonmerchant**
    - Use for [Non-merchant Gateways](https://docs.blesta.com/display/dev/Non-merchant+Gateways)
- **blesta-invoice-template**
    - Use for [Invoice Templates](https://docs.blesta.com/display/dev/Invoice+Templates)
- **blesta-report**
    - Use for Reports
- **blesta-messenger**
    - Use for [Messengers](https://docs.blesta.com/display/dev/Messengers)

Now list your extension with [packagist](http://packagist.org) (the default composer repository) and anyone can install your extension with composer!
