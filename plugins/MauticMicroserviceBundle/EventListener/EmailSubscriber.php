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
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\EmailBundle\Model\EmailModel;
use MauticPlugin\MauticMicroserviceBundle\Event\QueueConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueConsumerResults;
use MauticPlugin\MauticMicroserviceBundle\QueueEvents;
use Psr\Log\LoggerInterface;

/**
 * Class EmailSubscriber.
 */
class EmailSubscriber extends CommonSubscriber
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
     * EmailSubscriber constructor.
     *
     * @param IpLookupHelper $ipLookupHelper
     * @param AuditLogModel  $auditLogModel
     * @param EmailModel     $emailModel
     */
    public function __construct(IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel, EmailModel $emailModel, LoggerInterface $logger)
    {
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel  = $auditLogModel;
        $this->emailModel     = $emailModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            QueueEvents::ALL => ['onAll', 0],
        ];
    }

    /**
     * @param QueueConsumerEvent $event
     * @throws \Exception
     */
    public function onAll(QueueConsumerEvent $event)
    {
        // Simply acknowledge all unsupported events.
        // This is ane exclusive queue, so it's okay to ack messages.
        $event->setResult(QueueConsumerResults::ACKNOWLEDGE);
    }
}
