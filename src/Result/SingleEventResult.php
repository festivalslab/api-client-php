<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi\Result;


class SingleEventResult
{
    /**
     * @var array
     */
    protected $event;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param array  $event
     * @param string $url
     */
    public function __construct(array $event, $url)
    {
        $this->event = $event;
        $this->url   = $url;
    }

    /**
     * @return array
     */
    public function getEvent(): array
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
