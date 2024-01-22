<?php

namespace tests\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use tests\util\AppError;
use tests\util\HttpClient;

class InvitationServiceProviderTest extends TestCase
{
    private const oc_1_Endpoint = "https://oc-1.nl/apps/invitation";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testInvitationServiceProviderProperties()
    {
        try {
            $endpoint = self::oc_1_Endpoint . "/registry/invitation-service-provider";
            print_r("\ntesting endpoint $endpoint\n");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($endpoint);
            $this->assertTrue(boolval($response['success']), "GET $endpoint failed");
            print_r("\nproperties: " . print_r($response['data'], true) . "\n");
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testInvitationServiceProviderName()
    {
        // defined by the test data from Version20231130125301.php
        $invitationServiceProviderName = "OC 1 University";
        try {
            $endpoint = self::oc_1_Endpoint . "/name";
            print_r("\ntesting endpoint $endpoint\n");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($endpoint, 'admin');
            $this->assertTrue(boolval($response['success']), "GET $endpoint failed");
            print_r("\nname: " . $response['data'] . "\n");
            $this->assertEquals($invitationServiceProviderName, $response['data'], "GET $endpoint failed");
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
