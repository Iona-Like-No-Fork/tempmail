<?php

/**
 * This file is part of package le-risen/tempmail.
 *
 * @author Miroslav Lepichev <lemmas.online@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace leRisen\tempmail\Enums;

class TempMailAuxiliary
{
    const NEEDLE_EXTENSION = 'curl';
    const MSG_EXTENSION_REQUIRED = 'The ' . self::NEEDLE_EXTENSION . ' PHP extension is required';
    
    const MSG_ERROR_JSON = 'Error during decoding JSON: ';
    const MSG_NOT_ARRAY = 'It was expected that the output would be an array';
    
    const REGEX_LOGIN = '/^[a-zA-Z0-9]+$/';
    
    const MSG_NOT_VALID_LOGIN = 'Login must contain cyrillic (without symbols)';
    const MSG_NOT_GET_DOMAIN = 'Failed to get domain';
    
    const MSG_NOT_FOUND_DOMAIN = 'Domain not found in domain lists';
    const MSG_ERROR_FORMAT_EMAIL = 'The transmitted mail address does not match the format';
}