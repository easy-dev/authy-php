<?php

require 'vendor/resty/resty/Resty.php';

class ApiTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->client = new AuthyApi('bf12974d70818a08199d17d5e2bae630', 'http://sandbox-api.authy.com');
        $this->invalid_token = '1234567';
        $this->valid_token = '0000000';
    }

    public function testCreateUserWithValidData() {
        $user = $this->client->register_user('user@example.com', '305-456-2345', 1);

        $this->assertEquals("integer", gettype($user->id()));
        $this->assertEmpty((array) $user->errors());
    }

    public function testCreateUserWithInvalidData() {
        $user = $this->client->register_user('user@example.com', '', 1);

        $this->assertEquals("NULL", gettype($user->id()));
        $this->assertNotEmpty((array) $user->errors());
    }

    public function testVerifyTokenWithValidUser() {
        $user = $this->client->register_user('user@example.com', '305-456-2345', 1);
        $token = $this->client->verify_token($user->id(), $this->invalid_token);

        $this->assertEquals(false, $token->ok());
    }

    public function testVerifyTokenWithInvalidUser() {
        $token = $this->client->verify_token(0, $this->invalid_token);

        $this->assertEquals(false, $token->ok());
        $this->assertNotEmpty((array) $token->errors());
        $this->assertEquals("user has not configured this application", $token->errors()->error);
    }

    public function testVerifyTokenWithInvalidToken() {
        $user = $this->client->register_user('user@example.com', '305-456-2345', 1);
        $token = $this->client->verify_token($user->id(), $this->valid_token);
        $this->assertEquals(true, $token->ok());
    }

    public function testRequestSmsWithInvalidUser() {
        $sms = $this->client->request_sms(0, array("force" => true));

        $this->assertEquals(false, $sms->ok());
    }

    public function testRequestSmsWithValidUser() {
        $user = $this->client->register_user('user@example.com', '305-456-2345', 1);
        $sms = $this->client->request_sms($user->id(), array("force" => true));

        $this->assertEquals(false, $sms->ok());
        $this->assertEquals("is not activated for this account", $sms->errors()->enable_sms);
    }
}

