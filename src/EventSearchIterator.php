<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi;


use FestivalsApi\Result\EventSearchResult;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @final since 2.5.0
 */
class EventSearchIterator extends AbstractSearchIterator
{

    /**
     * @deprecated
     */
    protected EventSearchResult $last_result;

    /**
     * Execute the query and return the events
     *
     * @throws GuzzleException
     * @throws FestivalsApiClientException
     */
    protected function makeApiCall(): array
    {
        $result = $this->client->searchEvents($this->query);
        $this->last_result = $result;

        return $result->getEvents();
    }

}
