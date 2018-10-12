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

use GuzzleHttp\Client as HttpClient;
use leRisen\tempmail\Enums\TempMailAuxiliary;
use Psr\Http\Message\ResponseInterface;

class TempMailApiRequest
{
    protected const DISPATCH_METHOD = 'GET';
    protected const URL = 'https://privatix-temp-mail-v1.p.mashape.com/request/';
    protected const HEADER_ACCEPT = 'application/json';

    protected const CONNECTION_TIMEOUT = 15.0;
    protected const HTTP_ERRORS = false;

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var string
     */
    private $mashapeKey;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var bool
     */
    private $ignoreError;

    /**
     * @var callable
     */
    private $successHandler;

    /**
     * @var callable
     */
    private $errorHandler;

    /**
     * Constructor.
     *
     * @param string      $mashapeKey
     * @param string      $method
     * @param string|null $identifier
     */
    public function __construct($mashapeKey, $method, $identifier = null)
    {
        $this->client = new HttpClient([
            'timeout'     => static::CONNECTION_TIMEOUT,
            'http_errors' => static::HTTP_ERRORS, // disable 4xx and 5xx responses
        ]);

        $this->mashapeKey = $mashapeKey;
        $this->method = $method;
        $this->identifier = $identifier;
    }

    /**
     * Get data from response.
     *
     * @param ResponseInterface $response
     *
     * @return TempMailApiResult
     */
    private function getResponseData(ResponseInterface $response)
    {
        $result = new TempMailApiResult();

        $json = json_decode((string) $response->getBody(), true);

        $error = false;

        if (!$this->ignoreError) {
            $error = $this->hasError($json);
        }

        if ($error) {
            $result->error = true;
            $result->error_msg = $error;

            $handler = $this->errorHandler;

            if ($handler) {
                call_user_func($handler, $error);
            }
        } else {
            $result->success = true;
            $result->response = $json;

            $handler = $this->successHandler;

            if ($handler) {
                call_user_func($handler, $json);
            }
        }

        return $result;
    }

    /**
     * Checking the result for error.
     *
     * @param array|false $result
     *
     * @return string|false
     */
    private function hasError($result)
    {
        $error = false;

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = TempMailAuxiliary::MSG_ERROR_JSON.json_last_error_msg();
        } elseif (!is_array($result)) {
            $error = TempMailAuxiliary::MSG_NOT_ARRAY;
        } elseif (isset($result['message'])) {
            $error = $result['message'];
        }

        return $error;
    }

    /**
     * Send request by reference.
     */
    public function execute()
    {
        $builder = new TempMailApiBuilder(
            static::URL,
            $this->method,
            $this->identifier
        );

        $response = $this->client->request(
            static::DISPATCH_METHOD, $builder->build(),
            [
                'headers' => [
                    'X-Mashape-Key' => $this->mashapeKey,
                    'Accept'        => static::HEADER_ACCEPT,
                ],
            ]
        );

        return $this->getResponseData($response);
    }

    /**
     * Ignore error.
     *
     * @param bool $ignore
     *
     * @return TempMailApiRequest
     */
    public function ignoreError(bool $ignore): self
    {
        $this->ignoreError = $ignore;

        return $this;
    }

    /**
     * Set success handler.
     *
     * @param callable $func
     *
     * @return TempMailApiRequest
     */
    public function setSuccessHandler($func): self
    {
        $this->successHandler = $func;

        return $this;
    }

    /**
     * Set error handler.
     *
     * @param callable $func
     *
     * @return TempMailApiRequest
     */
    public function setErrorHandler($func): self
    {
        $this->errorHandler = $func;

        return $this;
    }
}
