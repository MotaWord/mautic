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
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueConsumerResults;
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueProtocol;
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueService;
use Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class BeanstalkdSubscriber.
 */
class BeanstalkdSubscriber extends AbstractQueueSubscriber
{
    const DELAY_DURATION = 60;

    /**
     * @var string
     */
    protected $protocol = QueueProtocol::BEANSTALKD;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var QueueService
     */
    private $queueService;

    /**
     * BeanstalkdSubscriber constructor.
     *
     * @param ContainerInterface $container
     * @param QueueService       $queueService
     */
    public function __construct(ContainerInterface $container, QueueService $queueService)
    {
        // The container is needed due to non-required binding of pheanstalk
        $this->container    = $container;
        $this->queueService = $queueService;
    }

    /**
     * @param Events\QueueEvent $event
     */
    public function publishMessage(Events\QueueEvent $event)
    {
        $this->container->get('leezy.pheanstalk')
            ->useTube($event->getQueueName())
            ->put($event->getPayload());
    }

    /**
     * @param Events\QueueEvent $event
     *
     * @throws Pheanstalk\Exception\ServerException
     */
    public function consumeMessage(Events\QueueEvent $event)
    {
        $messagesConsumed = 0;

        while ($event->getMessages() === null || $event->getMessages() > $messagesConsumed) {
            $pheanstalk = $this->container->get('leezy.pheanstalk');
            $job        = $pheanstalk
                ->watch($event->getQueueName())
                ->ignore('default')
                ->reserve(3600);

            if (empty($job)) {
                continue;
            }

            $consumerEvent = $this->queueService->dispatchConsumerEventFromPayload($job->getData());

            if ($consumerEvent->getResult() === QueueConsumerResults::TEMPORARY_REJECT) {
                $pheanstalk->release($job, PheanstalkInterface::DEFAULT_PRIORITY, static::DELAY_DURATION);
            } elseif ($consumerEvent->getResult() === QueueConsumerResults::REJECT) {
                $pheanstalk->bury($job);
            } else {
                try {
                    $pheanstalk->delete($job);
                } catch (Pheanstalk\Exception\ServerException $e) {
                    if (strpos($e->getMessage(), 'Cannot delete job') === false
                        && strpos($e->getMessage(), 'NOT_FOUND') === false
                    ) {
                        throw $e;
                    }
                }
            }

            ++$messagesConsumed;
        }
    }

}
