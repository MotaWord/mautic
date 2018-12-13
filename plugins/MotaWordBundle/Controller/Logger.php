<?php

namespace MauticPlugin\MotaWordBundle\Controller;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Bugsnag\Handler;

class Logger
{
    /**
     * @var \Bugsnag\Client
     */
    protected static $client;

    /**
     * @return \Bugsnag\Client
     */
    public static function getClient()
    {
        if (!static::$client) {
            static::$client = Client::make('f0ffda7a1255fad11c38d038e1259604');
            Handler::register(static::$client);
        }

        return static::$client;
    }

    /**
     * Add new meta data to all errors from this point on.
     * Adds a new tab for each meta data in Bugsnag UI.
     *
     * @param string $category
     * @param array  $data
     */
    public static function attachData($category, $data)
    {
        static::getClient()->registerCallback(function ($report) use ($category, $data) {
            /* @var $report \Bugsnag\Report */
            $report->addMetaData([
                $category => $data,
            ]);
        });
    }

    /**
     * @param \Throwable|string $e       An Exception or error name
     * @param string            $message Error message, if the first parameter is not an Exception
     *
     * @return bool
     */
    public static function send($e, $message = null)
    {
        if ($e instanceof \Throwable) {
            $client = static::getClient();
            if (!is_array($message)) {
                $message = [$message];
            }

            $client->leaveBreadcrumb('Request', Breadcrumb::MANUAL_TYPE, $message);
            $client->notifyException($e);
        } else {
            static::getClient()->notifyError((string) $e, (string) $message);
        }

        return true;
    }
}
