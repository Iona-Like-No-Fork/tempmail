# tempmail [![forthebadge](http://forthebadge.com/images/badges/built-with-love.svg)](http://forthebadge.com)

<p align="center">
<img src="https://img.shields.io/packagist/php-v/le-risen/tempmail.svg?style=flat-square" alt="PHP"></a>
<img src="https://poser.pugx.org/le-risen/tempmail/v/stable.svg" alt="Version"></a>
<img src="https://poser.pugx.org/le-risen/tempmail/license.svg" alt="License"></a>
<img src="https://img.shields.io/github/last-commit/leRisen/tempmail/master.svg?style=flat-square" alt="Last commit"></a>
</p>

a class for work with [api temp-mail.org](https://market.mashape.com/Privatix/temp-mail)

## Table of Contents
- [Requirements](#requirements)
- [Install](#install)
- [Example](#example)
- [Functions](#functions)
- [Helpers](#helpers)
- [License](#license)

## Requirements
- PHP 7.1+ (with enabled cURL)
- [Composer](https://getcomposer.org)

## Install

Run this command in console:
```
composer require leRisen/tempmail
```

## Example

```php
use leRisen\tempmail\TempMail;

/*
    'qwerty' - mashape application key
    'gebi' - login
    '@endrix.org' - domain
*/
$api = new TempMail('qwerty', 'gebi', '@endrix.org');

$domains = $api->listDomains();

foreach ($domains as $domain) {
	echo $domain;
}
```

## Functions

### `setEmail($login, $domain)`
Set new login and domain

### `setMashapeKey($key)`
Set mashape key

### `getEmail(false)`
Get full email (with md5 - true)

### `getLogin()`
Get current login for mail

### `getDomain()`
Get current domain for mail

### `getDomains()`
Get available domains

### `getMashapeKey()`
Get mashape key

### `messagesList()`
Returns messages list

### `message($messageID)`
Returns message

### `messageSource($messageID)`
Returns message source

### `messageAttachments($messageID)`
Returns message attachments

### `deleteMessage($messageID)`
Delete message

### `domainsList()`
Returns domain list

## Helpers

### `generateRandomLogin($length)`
Generate random login with $length (**without md5 hash**)

### `getRandomDomain()`
Get random domain from the domains list

### `temporaryMail($email)`
Checks whether the mail is temporary

## License

[MIT](https://tldrlegal.com/license/mit-license)