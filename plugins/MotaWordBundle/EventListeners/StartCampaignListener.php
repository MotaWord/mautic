<?php

namespace MauticPlugin\MotaWordBundle\EventListeners;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\EmailBundle\Model\EmailModel;
use MauticPlugin\MauticMicroserviceBundle\Event\MicroserviceConsumerEvent;
use MauticPlugin\MotaWordBundle\MicroserviceEvents;
use Psr\Log\LoggerInterface;

class StartCampaignListener extends CommonSubscriber
{
    /**
     * @var AuditLogModel
     */
    protected $auditLogModel;

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * EmailSubscriber constructor.
     *
     * @param MauticFactory   $factory
     * @param IpLookupHelper  $ipLookupHelper
     * @param AuditLogModel   $auditLogModel
     * @param EmailModel      $emailModel
     * @param LoggerInterface $logger
     */
    public function __construct(MauticFactory $factory, IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel, EmailModel $emailModel, LoggerInterface $logger)
    {
        $this->factory        = $factory;
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel  = $auditLogModel;
        $this->emailModel     = $emailModel;
        $this->logger         = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            MicroserviceEvents::START_CAMPAIGN => ['runEvent', 0],
        ];
    }

    public function runEvent(MicroserviceConsumerEvent $event): MicroserviceConsumerEvent
    {
    }
}
