<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMicroserviceBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\MauticMicroserviceBundle\Event\MicroserviceConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\MicroserviceEvents;
use MauticPlugin\MauticMicroserviceBundle\Queue\MicroserviceConsumerResults;

/**
 * Class EmailSubscriber.
 */
class DefaultSubscriber extends CommonSubscriber
{
    /**
     * DefaultSubscriber constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            MicroserviceEvents::ALL => ['onAll', 0],
        ];
    }

    /**
     * @param MicroserviceConsumerEvent $event
     *
     * @throws \Exception
     */
    public function onAll(MicroserviceConsumerEvent $event)
    {
        // Simply acknowledge all unsupported events.
        // This is ane exclusive queue, so it's okay to ack messages.
        $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);
    }
}
