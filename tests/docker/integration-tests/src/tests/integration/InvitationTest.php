<?php

namespace tests\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use tests\util\AppError;
use tests\util\HttpClient;

class InvitationTest extends TestCase
{
    private const oc_1_Endpoint = "https://oc-1.nl/apps/invitation";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGenerateInvite()
    {
        try {
            $endpoint = self::oc_1_Endpoint . "/generate-invite";
            print_r("\ntesting endpoint $endpoint\n");
            $httpClient = new HttpClient();

            // test no email specified
            $response = $httpClient->curlGet("$endpoint", 'admin');
            $this->assertFalse(boolval($response['success']), "Test with no email failed");
            $this->assertEquals(AppError::CREATE_INVITATION_NO_RECIPIENT_EMAIL, $response['error_message'], 'Error check failed.');

            // test email invalid
            $response = $httpClient->curlGet("$endpoint?email=invalid", 'admin');
            $this->assertFalse(boolval($response['success']), "Test with no email failed");
            $this->assertEquals(AppError::CREATE_INVITATION_EMAIL_INVALID, $response['error_message'], 'Error check failed.');

            $message = urlencode('I want to invite you.');
            $response = $httpClient->curlGet("$endpoint?email=someone@example.com&message=$message", 'admin');
            print_r("\nresponse: " . print_r($response, true) . "\n");

            $this->assertTrue(boolval($response['success']), "POST $endpoint failed");

            $this->assertTrue(Uuid::isValid($response['data']), 'POST $endpoint failed, invalid token returned.');
            return $response['data'];
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @depends testGenerateInvite
     */
    public function testFindInvitation(string $token)
    {
        try {
            $endpoint = self::oc_1_Endpoint . "/find-invitation-by-token";
            print_r("\ntesting endpoint: $endpoint");
            print_r("\n      with token: $token\n");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet("$endpoint?token=$token", 'admin');
            $this->assertTrue(boolval($response['success']), "GET $endpoint failed");
            print_r("\nfound invitation: " . print_r($response['data'], true) . "\n");
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
