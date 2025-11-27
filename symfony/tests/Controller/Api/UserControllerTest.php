<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testRegisterUserSuccess(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register-user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'fullName' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('User registered successfully', $response['message']);
        $this->assertEquals('John Doe', $response['data']['fullName']);
        $this->assertEquals('john.doe@example.com', $response['data']['email']);
        $this->assertEquals('+1234567890', $response['data']['phone']);
        $this->assertArrayHasKey('id', $response['data']);
    }

    public function testRegisterUserMissingFullName(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register-user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'john@example.com',
                'phone' => '+1234567890',
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterUserInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register-user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'fullName' => 'John Doe',
                'email' => 'invalid-email',
                'phone' => '+1234567890',
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterUserInvalidPhone(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register-user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'fullName' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => 'abc',
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterUserFullNameTooShort(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register-user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'fullName' => 'J',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterUserEmptyPayload(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register-user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(422);
    }
}
