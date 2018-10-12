# tempmail [![forthebadge](http://forthebadge.com/images/badges/built-with-love.svg)](http://forthebadge.com)

<p align="center">
    <img src="https://travis-ci.org/leRisen/tempmail.svg?branch=master" alt="Build Status">
    <img src="https://poser.pugx.org/le-risen/tempmail/v/stable.svg" alt="Version">
    <img src="https://poser.pugx.org/le-risen/tempmail/license.svg" alt="License">
    <img src="https://img.shields.io/github/last-commit/leRisen/tempmail/master.svg" alt="Last commit">
    <img src="https://poser.pugx.org/le-risen/tempmail/downloads.svg" alt="Downloads">
</p>

a package for work with [api temp-mail.org](https://market.mashape.com/Privatix/temp-mail)

## Table of Contents
- [Requirements](#requirements)
- [Install](#install)
- [Sample](#sample)
- [Functions](#functions)

## Requirements
- PHP 7.1+ (with enabled cURL)
- [Composer](https://getcomposer.org)

## Install

Run this command in console:
```
composer require leRisen/tempmail
```

## Sample

```php
/*
    'qwerty' - mashape application key
*/
$api = new \leRisen\tempmail\TempMailApiClient('qwerty');

$api->setEmail('gebi', '@endrix.org');

$request = $api->domainsList();

$request->setErrorHandler(function ($error) {
    var_dump($error);
});

$request->setSuccessHandler(function ($result) {
    var_dump($result);
});

$request->execute();
```

## Functions

### Checks if the domain belongs to the mail

```php
domainBelongs($email, $domains)
```

 - `$email` (string) - mail for verification
 - `$domains` (array) - list of domains
 - return `bool`
 
Example:
```php
$domains = [
    '@example.com'
];

$api->domainBelongs(
    'test@example.com',
    $domains
); // true
```

*If the domains are not transferred, then are used the received - `domainsList()`*

### Set new login and domain

```php
setEmail($login, $domain)
```
 - `$login` (string)
 - `$domain` (string)
 - return `self`
 
Example:
```php
$api->setEmail(
    'test',
    '@example.com'
);
```
 
*If one of the arguments was not passed, then it will be `automatically generated`.*
 
### Set mashape key

```php
setMashapeKey($key)
```
 - `$key` (string) - mashape api key
 - return `self`
 
Example:
```php
$api->setMashapeKey('qwerty');
```
  
### Get full email

```php
getEmail($md5)
```
 - `$md5` (bool) - with(out) md5 hash
 - return `string`
 
Example:
```php
$api->getEmail(false); // test@example.com
```

### Get current login for mail

```php
getLogin()
```
 - return `string`
 
Example:
```php
$api->getLogin(); // test
```

### Get current domain for mail

```php
getDomain()
```
 - return `string`
 
Example:
```php
$api->getDomain(); // @example.com
```

### Get mashape key

```php
getMashapeKey()
```
 - return `string`

Example:
```php
$api->getMashapeKey(); // qwerty
```

### Get available domains

```php
getDomains()
```
 - return `array`

Example:
```php
$api->getDomains(); // ['@endrix.org']
```

### Returns messages list

```php
$api->messagesList();
```
 - return `TempMailApiRequest`

### Returns domain list

```php
$api->domainsList();
```
 - return `TempMailApiRequest`

### Message

```php
// Returns message
$api->message($messageID);
```

```php
// Returns message source
$api->messageSource($messageID);
```

```php
// Return message attachments
$api->messageAttachments($messageID);
```

```php
// Delete message
$api->deleteMessage($messageID);
```
 - `$messageID` (string) - md5 unique identifier
 - return `TempMailApiRequest`
 