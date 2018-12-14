<?php

namespace MauticPlugin\MotaWordBundle\EventListeners;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticMicroserviceBundle\Event\MicroserviceConsumerEvent;
use MauticPlugin\MauticMicroserviceBundle\Queue\MicroserviceConsumerResults;
use MauticPlugin\MotaWordBundle\Api\MotaWordApi;
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
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @var MotaWordApi
     */
    protected $api;

    /**
     * EmailSubscriber constructor.
     *
     * @param MauticFactory   $factory
     * @param IpLookupHelper  $ipLookupHelper
     * @param AuditLogModel   $auditLogModel
     * @param EmailModel      $emailModel
     * @param LeadModel       $leadModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        MauticFactory $factory,
        IpLookupHelper $ipLookupHelper,
        AuditLogModel $auditLogModel,
        EmailModel $emailModel,
        LeadModel $leadModel,
        LoggerInterface $logger
    ) {
        $this->factory        = $factory;
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel  = $auditLogModel;
        $this->emailModel     = $emailModel;
        $this->leadModel      = $leadModel;
        $this->logger         = $logger;
        // @todo MotaWordApi should be injected via DI
        $this->api = new MotaWordApi($logger);
    }

    public static function getSubscribedEvents()
    {
        return [
            MicroserviceEvents::SEND_EMAIL => ['runEvent', 0],
        ];
    }

    public function runEvent(MicroserviceConsumerEvent $event): MicroserviceConsumerEvent
    {
        $payload    = $event->getPayload();
        $validation = $this->validateAndPreparePayload($payload);
        if ($validation['isValid'] === false) {
            $this->logger->error($validation['message']);
            $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);

            return $event;
        }

        if (isset($validation['payload'])) {
            $payload = $validation['payload'];
        }

        $this->logger->debug(print_r($payload, true));
        $emailName = $payload['email'];
        $userIds   = $payload['user_ids'];
        $email     = null;
        $sendTo    = [];

        // Prepare email
        // @todo $email variable logic will change once we integrate preferred locales per user.
        // There will be multiple email entities to use for each user.
        $email = $this->getEmailByName($emailName);
        if (!$email) {
            $this->logger->error('Email requested does not exist: '.$emailName);

            $event->setResult(MicroserviceConsumerResults::REJECT);

            return $event;
        }
        $this->logger->debug('Found the email named '.$emailName);

        // Prepare recipient user list
        $sendTo = $this->convertUserIdsToLeadIds($userIds);
        $this->logger->debug('I will send this email to '.count($sendTo).' recipients.');

        //TODO check
        $options['tokens'] = $payload;

        if (!$sendTo) {
            $this->logger->error('Could not generate a recipient list.');
            $event->setResult(MicroserviceConsumerResults::REJECT);

            return $event;
        }

        // Send the email
        // @todo $email variable logic will change once we integrate preferred locales per user.
        // There will be multiple email entities to use for each user.
        $sendStatus = $this->emailModel->sendEmail($email, $sendTo, $options);

        if (!$sendStatus) {
            $this->logger->error('Failed to start email delivery.');
            $event->setResult(MicroserviceConsumerResults::REJECT);

            return $event;
        }

        $this->logger->info('Triggered email delivery.');
        $event->setResult(MicroserviceConsumerResults::ACKNOWLEDGE);

        return $event;
    }

    protected function convertUserIdsToLeadIds(array $userIds): array
    {
        $sendTo = [];
        foreach ($userIds as $mwId) {
            $leadResult = $this->leadModel->getEntities([
                'filter' => [
                    'force' => [
                        [
                            'column' => 'l.mw_id',
                            'expr'   => 'eq',
                            'value'  => $mwId,
                        ],
                    ],
                ],
            ]);

            // Create a new Lead for this MW user if non-existing
            if (count($leadResult) < 1) {
                $this->logger->warning('Could not find Lead for MotaWord user #'.$mwId.'. Will create one now.');
                $newLead = $this->api->getUserAsLead($mwId);
                $this->leadModel->saveEntity($newLead);

                $lead = $newLead;
            } else {
                $this->logger->info('Found Lead for MotaWord user #'.$mwId.'.');
                /* @var \Mautic\LeadBundle\Entity\Lead $lead */
                $this->logger->debug(print_r(array_keys($leadResult), true));
                $lead = $leadResult[array_keys($leadResult)[0]];
            }

            $sendTo[] = $lead->getId();
        }

        return $sendTo;
    }

    /**
     * @param string $emailName
     *
     * @return mixed|null
     */
    protected function getEmailByName(string $emailName): ?Email
    {
        // Prepare email
        $emailResult = $this->emailModel->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => 'e.name',
                        'expr'   => 'eq',
                        'value'  => $emailName,
                    ],
                    // @todo detect user's language and choose the proper email.
                    [
                        'column' => 'e.language',
                        'expr'   => 'eq',
                        'value'  => 'en',
                    ],
                ],
            ],
        ]);

        if (count($emailResult) < 1) {
            return null;
        }

        return $emailResult->getIterator()->current();
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    protected function validateAndPreparePayload(array $payload): array
    {
        $message = null;
        $isValid = true;

        if (!isset($payload['email'])) {
            $message = 'Payload missing email name. Skipping.';
            $isValid = false;
        }

        // If user_id is set but not user_ids, set user_ids as an array from user_id
        // user_ids is what we want to handle outside in normal flow
        if (isset($payload['user_id']) && $payload['user_id']) {
            if (!isset($payload['user_ids']) || !$payload['user_ids']) {
                $payload['user_ids'] = [$payload['user_id']];
            }
        }

        if (!isset($payload['user_ids']) || !$payload['user_ids']) {
            $message = 'Payload missing recipient list. Skipping.';
            $isValid = false;
        }

        return [
            'message' => $message,
            'isValid' => $isValid,
            'payload' => $payload,
        ];
    }
}
