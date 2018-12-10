<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMicroserviceBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\MauticMicroserviceBundle\Event as Events;
use MauticPlugin\MauticMicroserviceBundle\MicroserviceEvents;

abstract class AbstractQueueSubscriber extends CommonSubscriber
{
    protected $protocol              = '';
    protected $protocolUiTranslation = '';

    abstract public function consumeMessage(Events\MicroserviceEvent $event);

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            MicroserviceEvents::CONSUME_MESSAGE => ['onConsumeMessage', 0],
            MicroserviceEvents::BUILD_CONFIG    => ['onBuildConfig', 0],
        ];
    }

    /**
     * @param Events\MicroserviceEvent $event
     */
    public function onConsumeMessage(Events\MicroserviceEvent $event)
    {
        if (!$event->checkContext($this->protocol)) {
            return;
        }

        $this->consumeMessage($event);
    }
}
