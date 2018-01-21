<?php

/**
 * The PHP class for temp-mail.org
 * @author Miroslav Lepichev <lemmas.online@gmail.com>
 * @link https://github.com/leRisen/tempmail
 * @version 1.0
 * @license MIT
 */

namespace leRisen\tempmail;

use GuzzleHttp\Client;

use leRisen\tempmail\Exceptions\TempMailException;

class TempMail
{
    /**
     * API Settings
     */
    const API_URL = 'https://privatix-temp-mail-v1.p.mashape.com/request/';
    const API_TIMEOUT = 15.0;
    
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
     * Instance curl
     *
     * @var resource
     */
    private $handle;
    
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
    public function __construct(string $mashapeKey, string $login = null, string $domain = null)
    {
        /*
         * Checking for load cURL to avoid conflicts
         */
        if (extension_loaded('curl')) {
            $this->handle = new Client([
                'base_uri' => self::API_URL,
                'timeout' => self::API_TIMEOUT
            ]);
            
            $this->mashapeKey = $mashapeKey;
            
            /*
             *  Receives a list of domains, then decodes
             *  and writes the result to a variable of available domains.
             */
            $this->domains = json_decode($this->domainsList(), true);
            
            $this->setEmail($login, $domain);
        } else {
            throw new TempMailException('The curl PHP extension is required');
        }
    }
    
    /**
     * Executes request on link
     *
     * @param   string $url
     * @return  string
     * @throws  TempMailException
     */
    private function sendRequest(string $url): string
    {
        $response = $this->handle->request(
            'GET', $url,
            [
                'headers' => [
                    'X-Mashape-Key' => $this->getMashapeKey(),
                    'Accept' => 'application/json'
                ]
            ]
        );
        
        $result = json_decode((string)$response->getBody(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TempMailException('Error during decoding JSON: ' . json_last_error_msg());
        } elseif (!is_array($result)) {
            throw new TempMailException('It was expected that the output would be an array');
        }
        
        return $result;
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
        return sprintf('%s%s', $method, (is_null($id) ? '' : '/id/' . $id));
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
        if (strlen($login) >= 4) {
            if (preg_match('/^[a-zA-Z0-9]+$/', $login)) {
                return $login;
            } else {
                throw new TempMailException('Login must contain cyrillic (without symbols)');
            }
        } else {
            throw new TempMailException('Invalid login length (minimal - 4)');
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
        if (in_array($domain, $this->getDomains())) {
            return $domain;
        } else {
            throw new TempMailException('Domain not found in domain lists');
        }
    }
    
    /**
     * Generate random login
     *
     * @param   int $length (max 32)
     * @return  string|null
     */
    public function generateRandomLogin(int $length): ?string
    {
        return ($length <= 32 ? substr(md5(mt_rand()), 0, $length) : null);
    }
    
    /**
     * Get random domain from the list domains
     *
     * @return  string
     * @throws  TempMailException
     */
    public function getRandomDomain(): string
    {
        $domains = $this->getDomains();
        
        if (!empty($domains)) {
            return $domains[$rand = array_rand($domains)];
        } else {
            throw new TempMailException('Failed to get domain');
        }
    }
    
    /**
     * Set new login and domain
     *
     * @param   string|null $login
     * @param   string|null $domain
     * @return  self
     * @throws  TempMailException
     */
    public function setEmail(string $login = null, string $domain = null): self
    {
        if (empty($login)) {
            $this->login = $this->generateRandomLogin(22);
        } else {
            $this->login = $this->validateLogin($login);
        }
        
        if (empty($domain)) {
            $this->domain = $this->getRandomDomain();
        } else {
            $this->domain = $this->validateDomain($domain);
        }
        
        $this->email = $this->login . '@' . $this->domain;
        
        return $this;
    }
    
    /**
     * Set mashape key
     *
     * @param   string $key
     * @return  self
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
     * @return  string
     */
    public function getEmail(bool $md5): string
    {
        return ($md5 ? md5($this->email) : $this->email);
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
        return (!empty($this->domains) ? $this->domains : []);
    }
    
    /**
     * Returns messages list
     *
     * @return  string
     * @throws  TempMailException
     */
    public function messagesList(): string
    {
        $email = $this->getEmail(true);
        
        $address = $this->getApiUrl('mail', $email);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns message
     *
     * @param   string $messageID
     * @return  string
     * @throws  TempMailException
     */
    public function message(string $messageID): string
    {
        $address = $this->getApiUrl('one_mail', $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns message source
     *
     * @param   string $messageID
     * @return  string
     * @throws  TempMailException
     */
    public function messageSource(string $messageID): string
    {
        $address = $this->getApiUrl('source', $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns message attachments
     *
     * @param   string $messageID
     * @return  string
     * @throws  TempMailException
     */
    public function messageAttachments(string $messageID): string
    {
        $address = $this->getApiUrl('attachments', $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Delete message
     *
     * @param   string $messageID
     * @return  string
     * @throws  TempMailException
     */
    public function deleteMessage(string $messageID): string
    {
        $address = $this->getApiUrl('delete', $messageID);
        
        return $this->sendRequest($address);
    }
    
    /**
     * Returns domains list
     *
     * @return  string
     * @throws  TempMailException
     */
    public function domainsList(): string
    {
        $address = $this->getApiUrl('domains');
        
        return $this->sendRequest($address);
    }
}