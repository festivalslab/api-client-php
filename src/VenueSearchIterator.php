<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */

namespace FestivalsApi;

use GuzzleHttp\Exception\GuzzleException;

final class VenueSearchIterator extends AbstractSearchIterator
{
    /**
     * Execute the query and return the venues
     *
     * @throws GuzzleException
     * @throws FestivalsApiClientException
     */
    protected function makeApiCall(): array
    {
        return $this->client->searchVenues($this->query)->getVenues();
    }

}
