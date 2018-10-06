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

use leRisen\tempmail\Enums\{
    TempMailAuxiliary,
    TempMailMethods
};

use leRisen\tempmail\Exceptions\TempMailClientException;

class TempMailApiClient
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
     * Mashape application key
     *
     * @var string
     */
    private $mashapeKey;
    
    /**
     * Constructor
     *
     * @param   string $mashapeKey
     * @throws  TempMailClientException
     */
    public function __construct($mashapeKey)
    {
        /*
         * Checking for load cURL to avoid conflicts
         */
        if (extension_loaded(TempMailAuxiliary::NEEDLE_EXTENSION)) {
            $this->setMashapeKey($mashapeKey);
        } else {
            throw new TempMailClientException(TempMailAuxiliary::MSG_EXTENSION_REQUIRED);
        }
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
        return in_array($domain, empty($domains) ? self::getDomains() : $domains);
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
     * @param   array $domains
     * @return  string
     * @throws  TempMailClientException
     */
    private function getRandomDomain(array $domains): string
    {
        if (!empty($domains)) {
            return $domains[$rand = array_rand($domains)];
        } else {
            throw new TempMailClientException(TempMailAuxiliary::MSG_NOT_GET_DOMAIN);
        }
    }
    
    /**
     * Validate login
     *
     * @param   string $login
     * @return  string
     * @throws  TempMailClientException
     */
    private function validateLogin(string $login): string
    {
        if (preg_match(TempMailAuxiliary::REGEX_LOGIN, $login)) {
            return $login;
        } else {
            throw new TempMailClientException(TempMailAuxiliary::MSG_NOT_VALID_LOGIN);
        }
    }
    
    /**
     * Validate domain
     *
     * @param   string $domain
     * @return  string
     * @throws  TempMailClientException
     */
    private function validateDomain(string $domain): string
    {
        if ($isFound = $this->searchDomain($domain, self::getDomains())) {
            return $domain;
        } else {
            throw new TempMailClientException(TempMailAuxiliary::MSG_NOT_FOUND_DOMAIN);
        }
    }

    /**
     * Create request
     * 
     * @param   string $method
     * @param   string|null $identifier
     * @return  TempMailApiRequest
     */
    public function createRequest(string $method, string $identifier = null): TempMailApiRequest
    {
        return new TempMailApiRequest($this->getMashapeKey(), $method, $identifier);
    }
    
    /**
     * Checks if the domain belongs to the mail
     *
     * @param   string $email
     * @param   array $domains
     * @return  bool
     * @throws  TempMailClientException
     */
    public function domainBelongs(string $email, array $domains = []): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $domain = substr($email, strpos($email, '@'));
            
            return $result = $this->searchDomain($domain, $domains);
        } else {
            throw new TempMailClientException(TempMailAuxiliary::MSG_ERROR_FORMAT_EMAIL);
        }
    }
    
    /**
     * Set new login and domain
     *
     * @param   string|null $login
     * @param   string|null $domain
     * @return  TempMailApiClient
     * @throws  TempMailClientException
     */
    public function setEmail(string $login = null, string $domain = null): self
    {
        $this->login = empty($login) ? $this->generateRandomLogin(rand(5, 32)) : $this->validateLogin($login);
        
        $this->domain = empty($domain) ? $this->getRandomDomain(self::getDomains()) : $this->validateDomain($domain);
        
        $this->email = $this->login . '' . $this->domain;
        
        return $this;
    }

    /**
     * Set mashape key
     *
     * @param   string $key
     * @return  TempMailApiClient
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
        $request = $this->domainsList();

        $result = $request->execute();

        $domains = $result->success ? $result->response : [];
        
        return empty($this->domains) ? $domains : $this->domains;
    }

    /**
     * Returns messages list
     *
     * @return  TempMailApiRequest
     */
    public function messagesList(): TempMailApiRequest
    {
        return $this->createRequest(TempMailMethods::MESSAGES, $this->getEmail(true));
    }
    
    /**
     * Returns message
     *
     * @param   string $messageID
     * @return  TempMailApiRequest
     */
    public function message(string $messageID): TempMailApiRequest
    {
        return $this->createRequest(TempMailMethods::MESSAGE, $messageID);
    }
    
    /**
     * Returns message source
     *
     * @param   string $messageID
     * @return  TempMailApiRequest
     */
    public function messageSource(string $messageID): TempMailApiRequest
    {
        return $this->createRequest(TempMailMethods::SOURCE, $messageID);
    }
    
    /**
     * Returns message attachments
     *
     * @param   string $messageID
     * @return  TempMailApiRequest
     */
    public function messageAttachments(string $messageID): TempMailApiRequest
    {
        return $this->createRequest(TempMailMethods::ATTACHMENTS, $messageID);
    }
    
    /**
     * Delete message
     *
     * @param   string $messageID
     * @return  TempMailApiRequest
     */
    public function deleteMessage(string $messageID): TempMailApiRequest
    {
        return $this->createRequest(TempMailMethods::DELETE, $messageID);
    }

    /**
     * Returns domains list
     * 
     * @return  TempMailApiRequest
     */
    public function domainsList(): TempMailApiRequest
    {
        return $this->createRequest(TempMailMethods::DOMAINS);
    }
}