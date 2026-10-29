<?php

namespace App\Services;

use Illuminate\Support\Str;

class GatewayReference
{
    public static function make(): string { return 'txn_'.Str::uuid()->toString(); }
}
