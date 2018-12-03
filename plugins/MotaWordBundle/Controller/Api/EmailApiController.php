<?php

/*
 * @copyright   2018 MotaWord. All rights reserved
 * @author      MotaWord
 *
 * @link        https://www.motaword.com
 *
 * @license     Proprietary
 */

namespace MauticPlugin\MotaWordBundle\Controller\Api;

use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class EmailApiController.
 */
class EmailApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('email');
        $this->entityClass      = 'Mautic\EmailBundle\Entity\Email';
        $this->entityNameOne    = 'email';
        $this->entityNameMulti  = 'emails';
        $this->serializerGroups = ['emailDetails', 'categoryList', 'publishDetails', 'assetList', 'formList', 'leadListList'];
        $this->dataInputMasks   = [
            'customHtml'     => 'html',
            'dynamicContent' => [
                'content' => 'html',
                'filters' => [
                    'content' => 'html',
                ],
            ],
        ];

        parent::initialize($event);
    }

    /**
     * Sends the email to a specific lead.
     *
     * @param int|string $idOrName Email ID or name
     * @param int        $mwUserId MotaWord User ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendToUserAction($idOrName, $mwUserId)
    {
        $leadId  = null;
        $emailId = null;

        // @todo implementation
        // find the Lead with this $mwUserId
        // if not, create Lead for this MW User.
        // Forward the request to the regular Mautic controller, but this time with the correct Mautic lead ID.
        // additions: security checks like in sendLeadAction
        // see sendLeadAction for entity examples.

        // this method is also used by route plugin_motaword_api_sendcontactemail_emailname
        // if $id is a string, then find this email by name and forward its $id to sendLeadAction.

        $response = $this->forward('Mautic\EmailBundle\Controller\Api\EmailApiController::sendLeadAction', [
            'id'     => $emailId,
            'leadId' => $leadId,
        ]);

        return $response;
    }
}
