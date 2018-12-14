<?php

namespace MauticPlugin\MotaWordBundle\EventListeners;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\EmailBundle\Model\EmailModel;
use MauticPlugin\MauticMicroserviceBundle\Event\MicroserviceConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\Queue\MicroserviceConsumerResults;
use MauticPlugin\MotaWordBundle\Controller\ContactController;
use MauticPlugin\MotaWordBundle\Controller\Logger;
use MauticPlugin\MotaWordBundle\MicroserviceEvents;
use Psr\Log\LoggerInterface;

class SendEmailListener extends CommonSubscriber
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
    public function __construct(
        MauticFactory $factory,
        IpLookupHelper $ipLookupHelper,
        AuditLogModel $auditLogModel,
        EmailModel $emailModel,
        LoggerInterface $logger
    ) {
        $this->factory        = $factory;
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel  = $auditLogModel;
        $this->emailModel     = $emailModel;
        $this->logger         = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            MicroserviceEvents::SEND_EMAIL => ['runEvent', 0],
        ];
    }

    public function runEvent(MicroserviceConsumerEvent $event): MicroserviceConsumerEvent
    {
        try {
            $payload    = $event->getPayload();
            $validation = $this->validatePayload($payload);
            if ($validation['isValid'] === false) {
                $this->logger->error($validation['message']);
                $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);

                return $event;
            }

            $contactService = new ContactController($this->factory);
            $sendTo         = [];
            foreach ($payload['user_ids'] as $mwId) {
                $contactId = $contactService->getContactId($mwId);
                if ($contactId != 0) {
                    $sendTo[] = $contactId;
                }
            }

            //TODO check
            $options['tokens'] = $payload;

            if ($sendTo && $this->emailModel->sendEmail($this->emailModel->getEntity($payload['email']), $sendTo, $options)) {
                $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);
            } else {
                $event->setResult(MicroserviceConsumerResults::REJECT);
            }

            return $event;
        } catch (\Exception $ex) {
            Logger::send($ex, MicroserviceEvents::SEND_EMAIL.' event has an exception. Error message : '.$ex->getMessage());
        }
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    public function validatePayload($payload): array
    {
        $message = null;
        $isValid = true;

        if (!isset($payload['email'])) {
            $message = 'Payload missing email name. Skipping.';
            $isValid = false;
        } else {
            if ($this->emailModel->getEntity($payload['email']) === null) {
                $message = 'The email template is not exist in mautic.';
                $isValid = false;
            } else {
                // If user_id is set but not user_ids, set user_ids as an array from user_id
                // user_ids is what we want to handle outside in normal flow
                if (isset($payload['user_id']) && $payload['user_id']) {
                    if (!isset($payload['user_ids']) || !$payload['user_ids']) {
                        $payload['user_ids'] = [$payload['user_id']];
                    }
                }

                if (!isset($payload['user_id']) || !$payload['user_id']) {
                    $message = 'Payload missing recipient list. Skipping.';
                    $isValid = false;
                }
            }
        }

        return [
            'message' => $message,
            'isValid' => $isValid,
        ];
    }
}
