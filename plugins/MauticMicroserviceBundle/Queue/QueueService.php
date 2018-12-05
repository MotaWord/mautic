<?php

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
use MauticPlugin\MauticMicroserviceBundle\Event\QueueConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\Event\QueueEvent;
use MauticPlugin\MauticMicroserviceBundle\Helper\QueueRequestHelper;
use MauticPlugin\MauticMicroserviceBundle\QueueEvents;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

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
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->eventDispatcher      = $eventDispatcher;
        $this->logger               = $logger;
    }

    /**
     * @param string $queueName
     * @param array  $payload
     */
    public function publishToQueue($queueName, array $payload = [])
    {
        if (isset($payload['request']) && $payload['request'] instanceof Request) {
            $payload['request'] = QueueRequestHelper::flattenRequest($payload['request']);
        }

        $logPayload = $payload;
        unset($logPayload['request']);
        $this->logger->debug('MICROSERVICE: Queuing job for '.$queueName, $logPayload);

        $protocol                   = $this->coreParametersHelper->getParameter('queue_protocol');
        $event                      = new QueueEvent($protocol, $queueName, $payload);
        $this->eventDispatcher->dispatch(QueueEvents::PUBLISH_MESSAGE, $event);
    }

    /**
     * @param string   $queueName
     * @param int|null $messages
     */
    public function consumeFromQueue($queueName, $messages = null)
    {
        $protocol = $this->coreParametersHelper->getParameter('queue_protocol');
        $event    = new QueueEvent($protocol, $queueName, [], $messages);
        $this->eventDispatcher->dispatch(QueueEvents::CONSUME_MESSAGE, $event);
    }

    /**
     * @param AMQPMessage $msg
     * @return QueueConsumerEvent
     */
    public function dispatchConsumerEventFromPayload(AMQPMessage $msg)
    {
        $routingKey = $msg->delivery_info['routing_key'];
        $payload = $msg->body;
        $payload    = json_decode($payload, true);
        if(!$payload) {
            $payload = [];
        }
        $logPayload = $payload;
        unset($logPayload['request']);

        if (isset($payload['request'])) {
            $payload['request'] = QueueRequestHelper::buildRequest($payload['request']);
        }

        // This is needed since OldSound RabbitMqBundle consumers don't know what their queue is
        $eventName = str_replace('.', '_', $routingKey);
        $eventName = "mautic.microservice_{$eventName}";

        $this->logger->debug('MICROSERVICE: Consuming job for routing key '.$routingKey.': '.$eventName, $logPayload);

        $event = new QueueConsumerEvent($payload);
        if($this->eventDispatcher->hasListeners($eventName)) {
            $this->eventDispatcher->dispatch($eventName, $event);
        } else {
            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);
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
