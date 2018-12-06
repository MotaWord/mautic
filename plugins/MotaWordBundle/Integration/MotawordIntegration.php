<?php

namespace MauticPlugin\MotaWordBundle\Integration;

use GuzzleHttp\Client;
use Mautic\LeadBundle\Entity\Lead;

class MotawordIntegration
{
    //TODO : following parameters should be env. parameters.

    private $baseURL  = 'api';
    private $username = '518725b5fcda3573';
    private $password = '27e571d75a56b8053ffc69caf58213ba';

    private function getAuthToken()
    {
        $client = new Client([
            'base_uri' => $this->baseURL,
        ]);
        $credentials = base64_encode($this->username.':'.$this->password);

        $response = $client->post('token', [
            'headers'   => ['Authorization' => 'Basic '.$credentials],
            'multipart' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $body = json_decode($response->getBody());

        return $body['access_token'];
    }

    /**
     * @param $mw_id
     *
     * @return Lead
     */
    public function getUser($mw_id)
    {
        //refactor this with singleton
        $token = $this->getAuthToken();

        $client = new Client([
            'base_uri' => $this->baseURL,
        ]);

        $response = $client->get('users/'.$mw_id.'/?access_token'.$token);

        $body = json_decode($response->getBody());

        return $this->buildLead($body['email_address'], $mw_id);
    }

    /**
     * @param $email
     * @param $mw_id
     *
     * @return Lead
     */
    private function buildLead($email, $mw_id)
    {
        $lead = new Lead();
        $lead->setEmail($email);
        $lead->setFields(
            [
                'core' => [
                    'mw_id' => [//'id' = '', //TODO check is it necessary or not?
                        'label' => 'Motaword ID',
                        'alias' => 'mw_id',
                        'type'  => 'number',
                        'group' => 'core',
                        'value' => $mw_id,
                    ],
                ],
            ]
        );

        return $lead;
    }
}
