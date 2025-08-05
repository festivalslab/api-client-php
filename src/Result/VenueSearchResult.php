<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi\Result;


class VenueSearchResult extends AbstractSearchResult
{
    public function getVenues(): array
    {
        return $this->results;
    }
}
