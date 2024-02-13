<?php

namespace tests\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use tests\util\HttpClient;
use tests\util\Util;

class InvitationServiceProviderTest extends TestCase
{
    private const OC_1_PROTECTED_ENDPOINT = "https://admin:admin@oc-1.nl/ocs/v1.php/apps/invitation";
    private const OC_1_UNPROTECTED_ENDPOINT = "https://oc-1.nl/apps/invitation";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testInvitationServiceProviderProperties()
    {
        try {
            $endpoint = self::OC_1_UNPROTECTED_ENDPOINT . "/registry/invitation-service-provider";
            print_r("\ntesting unprotected endpoint $endpoint\n");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($endpoint, true);

            $this->assertTrue(Util::isTrue($response['success']), "GET $endpoint failed");
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
            $endpoint = self::OC_1_PROTECTED_ENDPOINT . "/name";
            print_r("\ntesting protected endpoint $endpoint\n");
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($endpoint);
            print_r("\n" . print_r($response, true));
            $this->assertTrue(Util::isTrue($response['success']), "GET $endpoint failed");
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
