<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * CSRFバリデーションから除外するURI
     *
     * @var array
     */
    protected $except = [
        'https://localhost/taionup/line/Webhook'
    ];
}