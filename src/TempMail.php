<?php

/**
 * The PHP class for temp-mail.org
 * @author Miroslav Lepichev <lemmas.online@gmail.com>
 * @link https://github.com/leRisen/tempmail
 * @version 1.1
 * @license MIT
 */

namespace leRisen\tempmail;

use GuzzleHttp\Client as HttpClient;

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
    public function __construct(string $mashapeKey, string $login = null, string $domain = null)
    {
        /*
         * Checking for load cURL to avoid conflicts
         */
        if (extension_loaded('curl')) {
            $this->client = new HttpClient([
                'base_uri' => self::API_URL,
                'timeout' => self::API_TIMEOUT,
                'http_errors' => false, // disable 4xx and 5xx responses
            ]);
            
            $this->mashapeKey = $mashapeKey;
            
            $this->domainsList(); // receives a list of domains
            
            $this->setEmail($login, $domain);
        } else {
            throw new TempMailException('The curl PHP extension is required');
        }
    }
    
    /**
     * Executes request on link
     *
     * @param   string $url
     * @return  array
     * @throws  TempMailException
     */
    private function sendRequest(string $url): array
    {
        $response = $this->client->request(
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
        } elseif (isset($result['message'])) {
            throw new TempMailException($result['message']);
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
        return sprintf('%s%s', $method, is_null($id) ? '' : '/id/' . $id);
    }
    
    /**
     * Search domain in domains list
     *
     * @param   string $domain
     * @return  bool
     */
    private function searchDomain(string $domain): bool
    {
        return in_array($domain, $this->getDomains());
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
        if (preg_match('/^[a-zA-Z0-9]+$/', $login)) {
            return $login;
        } else {
            throw new TempMailException('Login must contain cyrillic (without symbols)');
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
        if ($this->searchDomain($domain)) {
            return $domain;
        } else {
            throw new TempMailException('Domain not found in domain lists');
        }
    }
    
    /**
     * Checks whether the mail is temporary
     *
     * @param   string $email
     * @return  bool
     * @throws  TempMailException
     */
    public function temporaryMail(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $domain = substr($email, strpos($email, '@'), -1);
            
            return $this->searchDomain($domain);
        } else {
            throw new TempMailException('The transmitted mail address does not match the format');
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
        return $length <= 32 ? substr(md5(mt_rand()), 0, $length) : null;
    }
    
    /**
     * Get random domain from the domains list
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
     * @return  TempMail
     * @throws  TempMailException
     */
    public function setEmail(string $login = null, string $domain = null): self
    {
        $this->login = empty($login) ? $this->generateRandomLogin(22) : $this->validateLogin($login);
        
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
     * @return  string
     */
    public function getEmail(bool $md5): string
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
        
        $address = $this->getApiUrl('mail', $email);
        
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
        $address = $this->getApiUrl('one_mail', $messageID);
        
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
        $address = $this->getApiUrl('source', $messageID);
        
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
        $address = $this->getApiUrl('attachments', $messageID);
        
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
        $address = $this->getApiUrl('delete', $messageID);
        
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
        $address = $this->getApiUrl('domains');
        
        return $this->domains = $this->sendRequest($address);
    }
}