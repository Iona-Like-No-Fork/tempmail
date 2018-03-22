<?php

namespace leRisen\tempmail\Tests;

use PHPUnit\Framework\TestCase;

use leRisen\tempmail\TempMail;
use leRisen\tempmail\Exceptions\TempMailException;

class DomainBelongsTest extends TestCase
{
    const API_KEY = 'qwerty';
    
    private $api;
    
    protected function setUp()
    {
        $this->api = new TempMail(self::API_KEY);
    }
    
    protected function tearDown()
    {
        $this->api = NULL;
    }
    
    public function domainBelongsDataProvider()
    {
        return [
            array('test@example.com', ['@example.com'], true), // has a domain
            array('test@example.org', ['@example.com'], false), // not
        ];
    }
    
    /**
     * @param   string $email
     * @param   array $domains
     * @param   bool $expected
     * @dataProvider domainBelongsDataProvider
     */
    public function testWith($email, $domains, $expected)
    {
        $result = $this->api->domainBelongs($email, $domains);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testUnCorrect()
    {
        $this->api->domainBelongs('test@example', []);
        
        $this->expectException(TempMailException::class);
    }
    
    public function testEmpty()
    {
        $this->api->domainBelongs('', []);
    
        $this->expectException(TempMailException::class);
    }
}