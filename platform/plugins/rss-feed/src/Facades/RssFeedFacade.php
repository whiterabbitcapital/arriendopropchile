<?php

namespace Botble\RssFeed\Facades;

use Botble\RssFeed\Supports\RssFeed;
use Illuminate\Support\Facades\Facade;

class RssFeedFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RssFeed::class;
    }
}
