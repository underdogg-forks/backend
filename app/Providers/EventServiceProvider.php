<?php

namespace plunner\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'plunner\Events\Caldav\ErrorEvent' => [
            'plunner\Listeners\Caldav\ErrorListener',
        ],
        'plunner\Events\Caldav\OkEvent' => [
            'plunner\Listeners\Caldav\OkListener',
        ],
        'plunner\Events\Optimise\ErrorEvent' => [
            'plunner\Listeners\Optimise\ErrorListener',
        ],
        'plunner\Events\Optimise\OkEvent' => [
            'plunner\Listeners\Optimise\OkListener',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
