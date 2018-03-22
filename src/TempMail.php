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

use leRisen\tempmail\Constants\{
    Settings,
    Methods,
    Auxiliary
};

use leRisen\tempmail\Exceptions\TempMailException;

/**
 * Class TempMail
 * @package leRisen\tempmail
 * @version 1.2
 */
class TempMail implements
    Settings,
    Methods,
    Auxiliary
{
    /**
     * Login for mail
     *
     * @var string
     */
    private $login;
    
    /**
     * Domain for mail
     *
     * @var string
     */
    private $domain;
    
    /**
     * Available domains
     *
     * @var array
     */
    private $domains;
    
    /**
     * Full email with login and domain
     *
     * @var string
     */
    private $email;
    
    /**
     * Client for working with https requests
     *
     * @var HttpClient
     */
    private $client;
    
    /**
     * Mashape application key
     *
     * @var string
     */
    private $mashapeKey;
    
    /**
     * Constructor
     *
     * @param   string $mashapeKey
     * @param   string|null $login
     * @param   string|null $domain
     * @throws  TempMailException
     */
    public function __construct($mashapeKey, $login = null, $domain = null)
    {
        /*
         * Checking for load cURL to avoid conflicts
         */
        if (extension_loaded(Auxiliary::NEEDLE_EXTENSION)) {
            $this->client = new HttpClient([
                'base_uri' => Settings::API_URL,
                'timeout' => Settings::API_TIMEOUT,
                'http_errors' => Settings::API_HTTP_ERRORS, // disable 4xx and 5xx responses
            ]);
            
            $this->setMashapeKey($mashapeKey);
            
            $this->domainsList(); // receives a list of domains
            
            $this->setEmail($login, $domain);
        } else {
            throw new TempMailException(Auxiliary::MSG_EXTENSION_REQUIRED);
        }
    }
    
    /**
     * Send inquiry by reference
     *
     * @param   string $url
     * @return  array
     * @throws  TempMailException
     */
    private function sendRequest(string $url): array
    {
        $response = $this->client->request(
            Settings::API_DISPATCH_METHOD, $url,
            [
                'headers' => [
                    'X-Mashape-Key' => $this->getMashapeKey(),
                    'Accept' => Settings::API_HEADER_ACCEPT,
                ]
            ]
        );
        
        $answer = json_decode((string)$response->getBody(), true);
        
        $this->checkErrors($answer);
        
        return $answer;
    }
    
    /**
     * Checking the result for errors
     *
     * @param   array|false $result
     * @throws  TempMailException
     */
    private function checkErrors($result)
    {
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TempMailException(Auxiliary::MSG_ERROR_JSON . json_last_error_msg());
        } elseif (!is_array($result)) {
            throw new TempMailException(Auxiliary::MSG_NOT_ARRAY);
        } elseif (isset($result['message'])) {
            throw new TempMailException($result['message']);
        }
    }
    
    /**
     * Returns base API url
     *
     * @param   string $method
     * @param   string|null $id
     * @return  string
     */
    private function getApiUrl(string $method, string $id = null): string
    {
        return sprintf('%s%s', $method, is_null($id) ? '' : '/id/' . $id);
    }
    
    /**
     * Search domain in domains list
     *
     * @param   string $domain
     * @param   array $domains
     * @return  bool
     */
    private function searchDomain(string $domain, array $domains = []): bool
    {
        return in_array($domain, empty($domains) ? $this->getDomains() : $domains);
    }
    
    /**
     * Generate random login
     *
     * @param   int $length (max 32)
     * @return  string
     */
    private function generateRandomLogin(int $length): string
    {
        return substr(md5(mt_rand()), 0, $length);
    }
    
    /**
     * Get random domain from the domains list
     *
     * @return  string
     * @throws  TempMailException
     */
    private function getRandomDomain(): string
    {
        $domains = $this->getDomains();
        
        if (!empty($domains)) {
            return $domains[$rand = array_rand($domains)];
        } else {
            throw new TempMailException(Auxiliary::MSG_NOT_GET_DOMAIN);
        }
    }
    
    /**
     * Validate login
     *
     * @param   string $login
     * @return  string
     * @throws  TempMailException
     */
    private function validateLogin(string $login): string
    {
        if (preg_match(Auxiliary::REGEX_LOGIN, $login)) {
            return $login;
        } else {
            throw new TempMailException(Auxiliary::MSG_NOT_VALID_LOGIN);
        }
    }
    
    /**
     * Validate domain
     *
     * @param   string $domain
     * @return  string
     * @throws  TempMailException
     */
    private function validateDomain(string $domain): string
    {
        if ($isFound = $this->searchDomain($domain, $this->getDomains())) {
            return $domain;
        } else {
            throw new TempMailException(Auxiliary::MSG_NOT_FOUND_DOMAIN);
        }
    }
    
    /**
     * Checks if the domain belongs to the mail
     *
     * @param   string $email
     * @param   array $domains
     * @return  bool
     * @throws  TempMailException
     */
    public function domainBelongs(string $email, array $domains = []): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $domain = substr($email, strpos($email, '@'));
            
            return $result = $this->searchDomain($domain, $domains);
        } else {
            throw new TempMailException(Auxiliary::MSG_ERROR_FORMAT_EMAIL);
        }
    }
    
    /**
     * Set new login and domain
     *
     * @param   string|null $login
     * @param   string|null $domain
     * @return  TempMail
     * @throws  TempMailException
     */
    public function setEmail(string $login = null, string $domain = null): self
    {
        $this->login = empty($login) ? $this->generateRandomLogin(rand(5, 32)) : $this->validateLogin($login);
        
        $this->domain = empty($domain) ? $this->getRandomDomain() : $this->validateDomain($domain);
        
        $this->email = $this->login . '' . $this->domain;
        
        return $this;
    }
    
    /**
     * Set mashape key
     *
     * @param   string $key
     * @return  TempMail
     */
    public function setMashapeKey(string $key): self
    {
        $this->mashapeKey = $key;
        
        return $this;
    }
    
    /**
     * Get mashape key
     *
     * @return  string|null
     */
    public function getMashapeKey(): ?string
    {
        return $this->mashapeKey;
    }
    
    /**
     * Get full email
     *
     * @param   bool $md5
     * @return  string|null
     */
    public function getEmail(bool $md5): ?string
    {
        return $md5 ? md5($this->email) : $this->email;
    }
    
    /**
     * Get current login for mail
     *
     * @return  string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }
    
    /**
     * Get current domain for mail
     *
     * @return  string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }
    
    /**
     * Get available domains
     *
     * @return  array
     */
    public function getDomains(): array
    {
        return !empty($this->domains) ? $this->domains : [];
    }
    
    /**
     * Returns messages list
     *
     * @return  array
     * @throws  TempMailException
     */
    public function messagesList(): array
    {
        $email = $this->getEmail(true);
        
        $address = $this->getApiUrl(Methods::MESSAGES, $email);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns message
     *
     * @param   string $messageID
     * @return  array
     * @throws  TempMailException
     */
    public function message(string $messageID): array
    {
        $address = $this->getApiUrl(Methods::MESSAGE, $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns message source
     *
     * @param   string $messageID
     * @return  array
     * @throws  TempMailException
     */
    public function messageSource(string $messageID): array
    {
        $address = $this->getApiUrl(Methods::SOURCE, $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns message attachments
     *
     * @param   string $messageID
     * @return  array
     * @throws  TempMailException
     */
    public function messageAttachments(string $messageID): array
    {
        $address = $this->getApiUrl(Methods::ATTACHMENTS, $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Delete message
     *
     * @param   string $messageID
     * @return  array
     * @throws  TempMailException
     */
    public function deleteMessage(string $messageID): array
    {
        $address = $this->getApiUrl(Methods::DELETE, $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns domains list
     *
     * @return  array
     * @throws  TempMailException
     */
    public function domainsList(): array
    {
        $address = $this->getApiUrl(Methods::DOMAINS);
        
        return $this->domains = $this->sendRequest($address);
    }
}