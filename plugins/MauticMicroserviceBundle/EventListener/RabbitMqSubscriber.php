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
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        // The container is needed due to non-required binding of the producer & consumer
        $this->container = $container;
        $this->logger    = $logger;
    }

    /**
     * @param Events\MicroserviceEvent $event
     */
    public function consumeMessage(Events\MicroserviceEvent $event)
    {
        $consumer = $this->container->get('old_sound_rabbit_mq.microservice_consumer');
        $this->logger->info('Listening for topic '.$event->getQueueName());
        $consumer->consume($event->getMessages());
    }
}
