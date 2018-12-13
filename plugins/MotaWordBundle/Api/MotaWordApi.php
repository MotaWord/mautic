<?php

namespace MauticPlugin\MotaWordBundle\Api;

use GuzzleHttp\Client;
use Mautic\LeadBundle\Entity\Lead;

class MotaWordApi
{
    private $baseURL;
    private $username;
    private $password;

    /**
     * MotaWordApi constructor.
     */
    public function __construct()
    {
        $this->baseURL  = getenv('MOTAWORD_API_URL');
        $this->username = getenv('MOTAWORD_API_USERNAME');
        $this->password = getenv('MOTAWORD_API_PASSWORD');
    }

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

        return $this->buildLead($body['firstname'], $body['lastname'], $body['email'], $mw_id, $body['locale'],
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
