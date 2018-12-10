<?php

/** @noinspection PhpUndefinedClassInspection */

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 * @author      MotaWord
 *
 * @link        http://mautic.org
 * @link        https://www.motaword.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMicroserviceBundle\Queue;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use MauticPlugin\MauticMicroserviceBundle\Event\MicroserviceConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\Event\MicroserviceEvent;
use MauticPlugin\MauticMicroserviceBundle\Helper\QueueRequestHelper;
use MauticPlugin\MauticMicroserviceBundle\MicroserviceEvents;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class QueueService.
 */
class QueueService
{
    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * QueueService constructor.
     *
     * @param CoreParametersHelper     $coreParametersHelper
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->eventDispatcher      = $eventDispatcher;
        $this->logger               = $logger;
    }

    /**
     * @param string   $queueName
     * @param int|null $messages
     */
    public function consumeFromQueue($queueName, $messages = null)
    {
        $protocol = $this->coreParametersHelper->getParameter('queue_protocol');
        $event    = new MicroserviceEvent($protocol, $queueName, [], $messages);
        $this->eventDispatcher->dispatch(MicroserviceEvents::CONSUME_MESSAGE, $event);
    }

    /**
     * @param AMQPMessage $msg
     *
     * @return MicroserviceConsumerEvent
     */
    public function dispatchConsumerEventFromPayload(AMQPMessage $msg)
    {
        $routingKey = $msg->delivery_info['routing_key'];
        $payload    = $msg->body;
        $payload    = json_decode($payload, true);
        if (!$payload) {
            $payload = [];
        }
        $logPayload = $payload;
        unset($logPayload['request']);

        if (isset($payload['request'])) {
            $payload['request'] = QueueRequestHelper::buildRequest($payload['request']);
        }

        // This is the event name released to the system.
        // microservice bundle users should their own events and bind them
        // via config to microservice configuration.
        // Microservice bundle will simply get your configuration,
        // and release those events when you receive a message to the topic $routingKey
        // We also have a way to listen to all microservice events, for instance for logging.
        // see below, after the release of this $eventName.
        // @warning Listeners should acknowledge the message.
        $eventName = "mautic.microservice.{$routingKey}";

        $this->logger->debug('MICROSERVICE: Consuming job for routing key '.$routingKey.': '.$eventName, $logPayload);

        $event = new MicroserviceConsumerEvent($payload);
        if ($this->eventDispatcher->hasListeners($eventName)) {
            $this->eventDispatcher->dispatch($eventName, $event);
        } else {
            $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);
        }

        // If there is a listener for mautic.microservice.*, trigger them
        $allEventName = 'mautic.microservice.*';
        $allEvent     = new MicroserviceConsumerEvent($payload);
        if ($this->eventDispatcher->hasListeners($allEventName)) {
            $this->eventDispatcher->dispatch($allEventName, $allEvent);
        }

        return $event;
    }

    /**
     * @return bool
     */
    public function isQueueEnabled()
    {
        return $this->coreParametersHelper->getParameter('queue_protocol') != '';
    }
}
