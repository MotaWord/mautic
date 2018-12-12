<?php

namespace MauticPlugin\MotaWordBundle\Controller;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadRepository;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MotaWordBundle\Api\MotaWordApi;

class ContactController
{
    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * EmailSubscriber constructor.
     *
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * This function retrieve contact with motaword id.
     * If the contact is not exist in Mautic, this function retrieve user from MotawordAPI.
     * In exception situation, function returns 0;.
     *
     * @param int $mwId
     *
     * @return int
     */
    public function getContactId($mwId)
    {
        try {
            /** @var LeadModel $leadModel */
            $leadModel = $this->factory->getModel('lead');

            /** @var LeadRepository $repository */
            $leadRepository = $leadModel->getRepository();
            $motaword       = new MotaWordApi();

            /** @var Lead $lead */
            $lead = $leadRepository->findOneBy(['mw_id' => $mwId]);

            if ($lead === null) {
                $leadModel->saveEntity($motaword->getUser($mwId));
                /** @var Lead $createdLead */
                $createdLead = $leadRepository->findOneBy(['mw_id' => $mwId]);

                return $createdLead->getId();
            } else {
                return $sendTo[] = $lead->getId();
            }
        } catch (\Exception $ex) {
            //TODO Bugsnag error('getContactId has an exception with mw_id :  ' . $mwId . ' error message is : ' . $ex->getMessage());
            return 0;
        }
    }
}
