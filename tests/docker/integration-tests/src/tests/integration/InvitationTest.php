<?php

namespace tests\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use tests\util\AppError;
use tests\util\HttpClient;
use tests\util\Util;

class InvitationTest extends TestCase
{
    private const oc_1_Endpoint = "https://admin:admin@oc-1.nl/ocs/v1.php/apps/invitation";
    private const PARAM_NAME_EMAIL = "email";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGenerateInvite()
    {
        try {
            $endpoint = self::oc_1_Endpoint . "/generate-invite";
            print_r("\ntesting protected endpoint $endpoint\n");
            $httpClient = new HttpClient();

            // test no email specified
            $response = $httpClient->curlPost($endpoint, []);
            $this->assertFalse(Util::is_true($response['success']), 'No email address provided should have returned error');
            $this->assertEquals(AppError::CREATE_INVITATION_NO_RECIPIENT_EMAIL, $response['error_message'], 'No email address check failed.');

            // test email invalid
            print_r("\ntest email valid\n");
            $response = $httpClient->curlPost(
                $endpoint,
                [
                    self::PARAM_NAME_EMAIL => 'invalid-email-address',
                    'message' => ''
                ]
            );
            print_r("\ntesting response for error_message:");
            $this->assertFalse(Util::is_true($response['success']), "Invalid email adress check should have failed");
            $this->assertEquals(AppError::CREATE_INVITATION_EMAIL_INVALID, $response['error_message'], 'Invalid email address response failure.');

            $message = urlencode('I want to invite you.');
            $response = $httpClient->curlPost(
                $endpoint,
                [
                    'email' => 'someone@example.com',
                    'message' => $message
                ]
            );
            $this->assertTrue(Util::is_true($response['success']), "POST $endpoint failed");
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
            print_r("\ntesting protected endpoint: $endpoint for token: $token");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet("$endpoint?token=$token");
            $this->assertTrue(Util::is_true($response['success']), "GET $endpoint failed");
            print_r("\nfound invitation with token: " . print_r($response['data'], true) . "\n");
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
