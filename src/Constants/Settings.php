<?php

/**
 * This file is part of package le-risen/tempmail.
 *
 * @author Miroslav Lepichev <lemmas.online@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace leRisen\tempmail\Constants;

/**
 * Constants Settings
 * @package leRisen\tempmail
 */
interface Settings
{
    const API_DISPATCH_METHOD = 'GET';
    const API_URL = 'https://privatix-temp-mail-v1.p.mashape.com/request/';
    const API_HEADER_ACCEPT = 'application/json';
    const API_TIMEOUT = 15.0;
    const API_HTTP_ERRORS = false;
}