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
use MauticPlugin\MauticMicroserviceBundle\Event\MicroserviceConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\Queue\MicroserviceConsumerResults;
use MauticPlugin\MotaWordBundle\Api\MotaWordApi;
use MauticPlugin\MotaWordBundle\MicroserviceEvents;
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

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            MicroserviceEvents::SEND_EMAIL => ['onSend', 0],
        ];
    }

    public function onSend(MicroserviceConsumerEvent $event): MicroserviceConsumerEvent
    {
        $payload = $event->getPayload();
        $this->logger->info('@onSend, payload: '.json_encode($payload));
        if (!isset($payload['name'])) {
            $this->logger->warning('Payload missing email name. Skipping.');

            $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);

            return $event;
        }

        // @todo get the email name from payload and find the Email entity
        $email = $this->emailModel->getEntity($payload['name']);

        //@todo also support regular email addresses apart from user_ids.
        if (!isset($payload['user_id_list']) || !$payload['user_id_list']) {
            $this->logger->warning('Payload missing recipient list. Skipping.');

            $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);

            return $event;
        }

        $mwUserIds = $payload['user_id_list'];
        $sendTo    = [];

        /** @var LeadModel $leadModel */
        $leadModel = $this->factory->getModel('lead');

        /** @var LeadRepository $repository */
        $leadRepository = $leadModel->getRepository();
        $motaword       = new MotaWordApi();

        foreach ($mwUserIds as $mwUserId) {
            /** @var Lead $lead */
            $lead = $leadRepository->findOneBy(['mw_id' => $mwUserId]);

            if ($lead === null) {
                $this->logger->info('Could not find MW user as Mautic lead. Creating one.');
                $leadModel->saveEntity($motaword->getUser($mwUserId));

                /** @var Lead $createdLead */
                $createdLead = $leadRepository->findOneBy(['mw_id' => $mwUserId]);

                $sendTo[] = $createdLead->getId();
            } else {
                $sendTo[] = $lead->getId();
            }
        }

        if ($sendTo && $this->emailModel->sendEmail($email, $sendTo)) {
            $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);

            return $event;
        }

        $event->setResult(MicroserviceConsumerResults::REJECT);

        return $event;
    }
}
