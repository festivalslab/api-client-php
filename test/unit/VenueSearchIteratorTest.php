<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */

namespace test\unit\FestivalsApi;

use FestivalsApi\AbstractSearchIterator;
use FestivalsApi\VenueSearchIterator;

class VenueSearchIteratorTest extends AbstractSearchIteratorCases
{

    public function test_it_is_initialisable(): void
    {
        $this->assertInstanceOf(VenueSearchIterator::class, $this->newSubject());
        $this->assertInstanceOf(AbstractSearchIterator::class, $this->newSubject());
    }

    protected function newSubject(): VenueSearchIterator
    {
        return new VenueSearchIterator($this->client);
    }

}
