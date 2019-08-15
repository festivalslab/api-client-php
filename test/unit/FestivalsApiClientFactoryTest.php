<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */

namespace test\unit\FestivalsApi;

use FestivalsApi\FestivalsApiClient;
use FestivalsApi\FestivalsApiClientFactory;
use PHPUnit\Framework\TestCase;

class FestivalsApiClientFactoryTest extends TestCase
{

    public function test_it_creates_client_without_credentials()
    {
        $this->assertInstanceOf(
            FestivalsApiClient::class,
            FestivalsApiClientFactory::create()
        );
    }

    public function test_it_creates_client_with_credentials_set()
    {
        $this->assertInstanceOf(
            FestivalsApiClient::class,
            FestivalsApiClientFactory::createWithCredentials('test-key', 'test-secret')
        );
    }
}
