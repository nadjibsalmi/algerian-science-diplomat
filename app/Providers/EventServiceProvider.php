<?php

namespace App\Providers;

use App\Modules\Authentication\Events\UserRegistered;
use App\Modules\Authentication\Listeners\SendVerificationEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegistered::class => [
            SendVerificationEmail::class,
        ],
    ];
}