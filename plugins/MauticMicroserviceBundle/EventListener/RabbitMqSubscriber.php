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

use MauticPlugin\MauticMicroserviceBundle\Event as Events;
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueProtocol;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RabbitMqSubscriber.
 */
class RabbitMqSubscriber extends AbstractQueueSubscriber
{
    /**
     * @var string
     */
    protected $protocol = QueueProtocol::RABBITMQ;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * RabbitMqSubscriber constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        // The container is needed due to non-required binding of the producer & consumer
        $this->container = $container;
    }

    /**
     * @param Events\QueueEvent $event
     */
    public function publishMessage(Events\QueueEvent $event)
    {
        $producer = $this->container->get('old_sound_rabbit_mq.microservice_producer');
        $producer->setQueue($event->getQueueName());
        $producer->publish($event->getPayload(), $event->getQueueName(), [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
    }

    /**
     * @param Events\QueueEvent $event
     */
    public function consumeMessage(Events\QueueEvent $event)
    {
        $consumer = $this->container->get('old_sound_rabbit_mq.microservice_consumer');
        $consumer->setQueueOptions([
            'name'        => $event->getQueueName(),
            'auto_delete' => false,
            'durable'     => true,
        ]);
        $consumer->setRoutingKey($event->getQueueName());
        $consumer->consume($event->getMessages());
    }

}
