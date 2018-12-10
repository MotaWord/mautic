<?php

namespace MauticPlugin\MauticMicroserviceBundle\Helper;

use MauticPlugin\MauticMicroserviceBundle\Queue\MicroserviceConsumerResults;
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqConsumer implements ConsumerInterface
{
    /**
     * @var QueueService
     */
    private $queueService;

    /**
     * RabbitMqConsumer constructor.
     *
     * @param QueueService $queueService
     */
    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $msg)
    {
        $event = $this->queueService->dispatchConsumerEventFromPayload($msg);

        if ($event->getResult() === MicroserviceConsumerResults::TEMPORARY_REJECT) {
            return static::MSG_REJECT_REQUEUE;
        } elseif ($event->getResult() === MicroserviceConsumerResults::ACKNOWLEDGE) {
            return static::MSG_ACK;
        } elseif ($event->getResult() === MicroserviceConsumerResults::REJECT) {
            return static::MSG_REJECT;
        } else {
            return static::MSG_SINGLE_NACK_REQUEUE;
        }
    }
}
