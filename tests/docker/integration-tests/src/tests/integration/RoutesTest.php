<?php

use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testRouteEndpoint()
    {
        $this->assertTrue(true, "Test route /endpoint failed");
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
