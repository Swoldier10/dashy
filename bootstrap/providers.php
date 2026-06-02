<?php

use App\Providers\AiServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\GoogleCalendarServiceProvider;
use App\Providers\NotificationsServiceProvider;
use App\Providers\SearchServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AiServiceProvider::class,
    SearchServiceProvider::class,
    NotificationsServiceProvider::class,
    GoogleCalendarServiceProvider::class,
];
