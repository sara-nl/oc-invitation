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
    private const OC_1_ENDPOINT = "https://admin:admin@oc-1.nl/ocs/v1.php/apps/collaboration";
    private const OC_1_INVITATION_SERVICE_ENDPOINT = "https://oc-1.nl/apps/collaboration";
    private const OC_2_ENDPOINT = "https://admin:admin@oc-2.nl/ocs/v1.php/apps/collaboration";
    private const PARAM_NAME_EMAIL = "email";
    private const PARAM_NAME_NAME = "name";
    private const PARAM_NAME_SENDER_NAME = "senderName";
    private const PARAM_NAME_MESSAGE = "message";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGenerateInvite()
    {
        try {
            $endpoint = self::OC_1_ENDPOINT . "/generate-invite";
            print_r("\ntesting protected endpoint $endpoint\n");
            $httpClient = new HttpClient();

            // test no email specified
            print_r("\ntest no email specified\n");
            $response = $httpClient->curlPost(
                $endpoint,
                [
                    self::PARAM_NAME_EMAIL => "",
                    self::PARAM_NAME_NAME => "Me",
                    self::PARAM_NAME_SENDER_NAME => "You"

                ]
            );
            print_r("\n" . print_r($response, true));
            $this->assertFalse(Util::isTrue($response['success']), 'No email address provided should have returned error');
            $this->assertEquals(AppError::CREATE_INVITATION_NO_RECIPIENT_EMAIL, $response['error_message'], 'No email address check failed.');

            // test email invalid
            print_r("\ntest email invalid\n");
            $response = $httpClient->curlPost(
                $endpoint,
                [
                    self::PARAM_NAME_EMAIL => "invalid-email-address",
                    self::PARAM_NAME_NAME => "Me",
                    self::PARAM_NAME_SENDER_NAME => "You"
                ]
            );
            print_r("\n" . print_r($response, true));
            $this->assertFalse(Util::isTrue($response['success']), "Invalid email adress check should have failed");
            $this->assertEquals(AppError::CREATE_INVITATION_EMAIL_INVALID, $response['error_message'], 'Invalid email address response failure.');

            print_r("\ntest invitation\n");
            $message = urlencode('I want to invite you.');
            $response = $httpClient->curlPost(
                $endpoint,
                [
                    self::PARAM_NAME_EMAIL => "me@oc-1.nl",
                    self::PARAM_NAME_NAME => "Me",
                    self::PARAM_NAME_SENDER_NAME => "You",
                    self::PARAM_NAME_MESSAGE => $message
                ]
            );
            print_r("\n" . print_r($response, true));
            $this->assertTrue(Util::isTrue($response['success']), "POST $endpoint failed");
            $this->assertTrue(Uuid::isValid($response['data']['token']), 'POST $endpoint failed, invalid token returned.');
            return $response['data']['token'];
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
            $endpoint = self::OC_1_ENDPOINT . "/find-invitation-by-token";
            print_r("\ntesting protected endpoint: $endpoint for token: $token");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet("$endpoint?token=$token");
            print_r("\n" . print_r($response, true));
            $this->assertTrue(Util::isTrue($response['success']), "GET $endpoint failed");
            print_r("\nfound invitation with token: " . print_r($response['data'], true) . "\n");
            return $token;
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @depends testFindInvitation
     */
    public function testHandleInvitation(string $token)
    {
        try {
            $providerEndpoint = self::OC_1_INVITATION_SERVICE_ENDPOINT;
            $userName = "admin";
            $handleInviteUrl = self::OC_2_ENDPOINT . "/handle-invite?token=$token&providerEndpoint=$providerEndpoint&name=$userName";
            print_r("\ntesting protected endpoint: $handleInviteUrl");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($handleInviteUrl, false, true);
            $this->assertEquals(200, $response, "GET $handleInviteUrl failed");

            print_r("\n\nverifying the persisted invitation");
            $findInvitationEndpoint = self::OC_2_ENDPOINT . "/find-invitation-by-token";
            $response = $httpClient->curlGet("$findInvitationEndpoint?token=$token");
            print_r("\nresponse: " . print_r($response, true));
            $this->assertTrue(Util::isTrue($response['success']), "GET $findInvitationEndpoint failed");
            $this->assertEquals($token, $response['data']['token'], "GET $findInvitationEndpoint failed");
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
