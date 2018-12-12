<?php

namespace MauticPlugin\MotaWordBundle\Api;

use GuzzleHttp\Client;
use Mautic\LeadBundle\Entity\Lead;

class MotaWordApi
{
    //TODO : following parameters should be env. parameters.

    private $baseURL  = 'http://api';
    private $username = '518725b5fcda3573';
    private $password = '27e571d75a56b8053ffc69caf58213ba';

    private function getAuthToken()
    {
        $client = new Client([
            'base_uri' => $this->baseURL,
        ]);
        $credentials = base64_encode($this->username.':'.$this->password);

        $response = $client->post('token', [
            'headers'     => ['Authorization' => 'Basic '.$credentials],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $body = json_decode($response->getBody(), true);

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

        $response = $client->get('users/'.$mw_id.'?access_token='.$token);

        $body = json_decode($response->getBody(), true);

        return $this->buildLead($body['firstname'], $body['lastname'], $body['email_address'], $mw_id, $body['locale'],
            $body['timezone']);
    }

    /**
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param $mw_id
     * @param $locale
     * @param $timezone
     *
     * @return Lead
     */
    private function buildLead($firstname, $lastname, $email, $mw_id, $locale, $timezone)
    {
        $lead = new Lead();
        $lead->setFirstname($firstname);
        $lead->setLastname($lastname);
        $lead->setEmail($email);
        $lead->setTimezone($timezone); //todo name surname
        $lead->setFields(
            [
                'core' => [
                    'mw_id' => [
                        'label' => 'Motaword ID',
                        'alias' => 'mw_id',
                        'type'  => 'number',
                        'group' => 'core',
                        'value' => $mw_id,
                    ],
                    'preferred_locale' => $locale,
                ],
            ]
        );

        return $lead;
    }
}
