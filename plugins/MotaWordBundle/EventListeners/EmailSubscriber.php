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
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadRepository;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticMicroserviceBundle\Event\QueueConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueConsumerResults;
use MauticPlugin\MauticMicroserviceBundle\QueueEvents;
use MauticPlugin\MotaWordBundle\Integration\MotawordApi;
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
     * @var MauticFactory
     */
    protected $factory;

    /**
     * EmailSubscriber constructor.
     *
     * @param IpLookupHelper $ipLookupHelper
     * @param AuditLogModel  $auditLogModel
     * @param EmailModel     $emailModel
     */
    public function __construct(MauticFactory $factory, IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel, EmailModel $emailModel, LoggerInterface $logger)
    {
        $this->factory        = $factory;
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
        if (!isset($payload['name'])) {
            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);

            return $event;
        }

        // @todo get the email name from payload and find the Email entity
        $email = $this->emailModel->getEntity($payload['name']);

        //@todo also support regular email addresses apart from user_ids.
        if (!isset($payload['user_id_list'])) {
            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);

            return $event;
        }

        $mwUserIds = $payload['user_id_list'];
        $sendTo    = [];

        /** @var LeadModel $leadModel */
        $leadModel = $this->factory->getModel('lead');

        /** @var LeadRepository $repository */
        $leadRepository = $leadModel->getRepository();
        $motaword       = new MotawordApi();

        foreach ($mwUserIds as $mwUserId) {
            /** @var Lead $lead */
            $lead = $leadRepository->findOneBy(['mw_id' => $mwUserId]);

            if ($lead === null) {
                $leadModel->saveEntity($motaword->getUser($mwUserId));

                /** @var Lead $createdLead */
                $createdLead = $leadRepository->findOneBy(['mw_id' => $mwUserId]);

                $sendTo[] = $createdLead->getId();
            } else {
                $sendTo[] = $lead->getId();
            }
        }

        if ($this->emailModel->sendEmail($email, $sendTo)) {
            $event->setResult(QueueConsumerResults::ACKNOWLEDGE);

            return $event;
        }

        $event->setResult(QueueConsumerResults::REJECT);

        return $event;
    }
}
