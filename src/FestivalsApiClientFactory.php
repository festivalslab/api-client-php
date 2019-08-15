<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi;


use GuzzleHttp\Client;

class FestivalsApiClientFactory
{
    public static function create(): FestivalsApiClient
    {
        $guzzle = new Client(['headers' => ['User-Agent' => 'Festivals API Client (PHP)']]);

        return new FestivalsApiClient($guzzle);
    }

    public static function createWithCredentials(string $key, string $secret): FestivalsApiClient
    {
        $client = FestivalsApiClientFactory::create();
        $client->setCredentials($key, $secret);

        return $client;
    }
}
