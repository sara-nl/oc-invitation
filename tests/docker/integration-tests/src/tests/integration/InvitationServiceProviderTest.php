<?php

namespace tests\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use tests\util\HttpClient;
use tests\util\Util;

class InvitationServiceProviderTest extends TestCase
{
    private const oc_1_Protected_Endpoint = "https://admin:admin@oc-1.nl/ocs/v1.php/apps/invitation";
    private const oc_1_Unprotected_Endpoint = "https://oc-1.nl/apps/invitation";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testInvitationServiceProviderProperties()
    {
        try {
            $endpoint = self::oc_1_Unprotected_Endpoint . "/registry/invitation-service-provider";
            print_r("\ntesting unprotected endpoint $endpoint\n");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($endpoint, true);

            $this->assertTrue(Util::is_true($response['success']), "GET $endpoint failed");
            $this->assertEquals('oc-1.nl', $response['data']['domain'], "Domain is not what is expected.");
            print_r("\nproperties: " . print_r($response, true) . "\n");
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testInvitationServiceProviderName()
    {
        // defined by the test data from Version20231130125301.php
        $invitationServiceProviderName = "OC 1 University";
        try {
            $endpoint = self::oc_1_Protected_Endpoint . "/name";
            print_r("\ntesting protected endpoint $endpoint\n");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($endpoint);
            print_r("\n" . print_r($response, true));
            $this->assertTrue(Util::is_true($response['success']), "GET $endpoint failed");
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
