<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi\Result;


class EventSearchResult extends AbstractSearchResult
{
    public function getEvents(): array
    {
        return $this->results;
    }

}
