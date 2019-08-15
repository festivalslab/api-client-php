<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi\Result;


class EventSearchResult
{
    /**
     * @var array
     */
    protected $events;

    /**
     * @var int
     */
    protected $total_results;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param array  $events
     * @param string $url
     * @param int    $total_results
     */
    public function __construct(array $events, $url, $total_results)
    {
        $this->events        = $events;
        $this->url           = $url;
        $this->total_results = $total_results;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @return int
     */
    public function getTotalResults(): int
    {
        return $this->total_results;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
