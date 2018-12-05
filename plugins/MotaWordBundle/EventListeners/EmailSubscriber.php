<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MotaWordBundle\EventListeners;

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
            QueueEvents::SEND_EMAIL => ['onSend', 0],
        ];
    }

    public function onSend(QueueConsumerEvent $event): QueueConsumerEvent
    {
        $payload = $event->getPayload();
        if(!isset($payload['name'])) {
            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);
            return $event;
        }

        // @todo get the email name from payload and find the Email entity
        $email = $this->emailModel->getEntity($payload['name']);

        //@todo also support regular email addresses apart from user_ids.
        if(!isset($payload['user_id_list'])) {
            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);
            return $event;
        }

        $mwUserIds = $payload['user_id_list'];
        $sendTo = [];

        foreach($mwUserIds as $mwUserId) {
            // @todo find the lead and add its ID to the lead ID list.
            $sendTo[] = ['id' => $mwUserId];
        }

        if($this->emailModel->sendEmail($email, $sendTo)) {
            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);
            return $event;
        }

        $event->setResult(QueueConsumerResults::REJECT);
        return $event;
    }
}
