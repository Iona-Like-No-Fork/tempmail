<?php

namespace leRisen\tempmail\Tests;

use leRisen\tempmail\TempMailApiClient;
use leRisen\tempmail\TempMailApiResult;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    const API_KEY = 'qwerty';

    private $api;

    protected function setUp()
    {
        $this->api = new TempMailApiClient(self::API_KEY);
    }

    protected function tearDown()
    {
        $this->api = null;
    }

    public function testCallWithInvalidKey()
    {
        $request = $this->api->domainsList();
        $result = $request->execute();

        $this->assertInstanceOf(TempMailApiResult::class, $result);
        $this->assertTrue($result->error);
        $this->assertEquals($result->error_msg, 'Invalid Mashape Key');
        $this->assertFalse($result->success);
        $this->assertNull($result->response);
    }
}
