<?php

/**
 * This file is part of package le-risen/tempmail.
 *
 * @author Miroslav Lepichev <lemmas.online@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace leRisen\tempmail;

class TempMailApiBuilder
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $identifier;

    /**
     * Constructor.
     *
     * @param string      $url
     * @param string      $method
     * @param string|null $identifier
     */
    public function __construct($url, $method, $identifier = null)
    {
        $this->url = $url;
        $this->method = $method;
        $this->identifier = $identifier;
    }

    public function build()
    {
        $url = $this->url;
        $method = $this->method;
        $identifier = $this->identifier;

        return sprintf(
            '%s%s%s', $url, $method, is_null($identifier) ? '' : "/id/$identifier"
        );
    }
}
