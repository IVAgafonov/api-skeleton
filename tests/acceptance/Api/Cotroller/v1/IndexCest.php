<?php namespace Acceptance\Cotroller\v1;


class IndexCest
{
    public function _before(\AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(\AcceptanceTester $I)
    {
        $I->amOnPage('/api');
        $I->see('Invalid route');
    }
}
