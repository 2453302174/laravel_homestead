<?php

namespace App\Services;

use App\Contracts\TestContract;

class TestService implements TestContract
{
    public function callMe($controller)
    {
        var_dump('Call Me From TestServiceProvider In '.$controller);
    }
}