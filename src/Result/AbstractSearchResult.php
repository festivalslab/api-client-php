<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi\Result;


abstract class AbstractSearchResult
{
    public function __construct(
        protected array  $results,
        protected string $url,
        protected int    $total_results
    ) {
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
