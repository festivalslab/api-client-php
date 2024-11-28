<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi\Result;


class VenueSearchResult
{
    public function __construct(
        protected array  $venues,
        protected string $url,
        protected int    $total_results
    ) {
    }

    public function getVenues(): array
    {
        return $this->venues;
    }

    public function getTotalResults(): int
    {
        return $this->total_results;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
