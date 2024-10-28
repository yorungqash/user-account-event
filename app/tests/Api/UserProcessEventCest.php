<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Tests\Support\ApiTester;

class UserProcessEventCest
{
    public function testDefaultResponse(ApiTester $i): void
    {
        $rawBody = '
            {
                "userId": 30409,
                "content": "logout"
            }
        ';

        $i->haveHttpHeader('Accept', 'application/json');
        $i->haveHttpHeader('Content-Type', 'application/json');

        $i->send('POST', '/api/v1/deal-offer', $rawBody);

        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseJsonMatchesJsonPath('$.data.event[*]');
    }
}
