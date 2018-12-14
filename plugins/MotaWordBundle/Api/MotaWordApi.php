<?php

namespace MauticPlugin\MotaWordBundle\Api;

use GuzzleHttp\Client;
use Mautic\LeadBundle\Entity\Lead;
use Psr\Log\LoggerInterface;

class MotaWordApi
{
    private $baseURL;
    private $username;
    private $password;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MotaWordApi constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->baseURL  = getenv('MOTAWORD_API_URL');
        $this->username = getenv('MOTAWORD_API_USERNAME');
        $this->password = getenv('MOTAWORD_API_PASSWORD');
        $this->logger   = $logger;
    }

    private function getAuthToken()
    {
        $client = new Client([
            'base_uri' => $this->baseURL,
        ]);
        $credentials = base64_encode($this->username.':'.$this->password);

        $response = $client->post('token', [
            'headers'     => ['Authorization' => 'Basic '.$credentials],
            'form_params' => ['grant_type' => 'client_credentials'],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['access_token'];
    }

    /**
     * @param $mw_id
     *
     * @return Lead
     */
    public function getUserAsLead($mw_id)
    {
        //refactor this with singleton
        $token = $this->getAuthToken();

        $client = new Client(['base_uri' => $this->baseURL]);

        $response = $client->get('users/'.$mw_id.'?access_token='.$token);
        $body     = json_decode($response->getBody(), true);

        $this->logger->debug('MW user API call response: '.json_encode($body));

        return $this->buildLead(
            $body['firstname'],
            $body['lastname'],
            $body['email'],
            $mw_id,
            $body['locale'],
            $body['timezone']
        );
    }

    /**
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $mw_id
     * @param $locale
     * @param $timezone
     *
     * @return Lead
     */
    private function buildLead($firstName, $lastName, $email, $mw_id, $locale, $timezone)
    {
        $lead = new Lead();
        $lead->setFirstname($firstName);
        $lead->setLastname($lastName);
        $lead->setEmail($email);

        // @todo this timezone may not be compatible with Mautic's timezone
        $lead->setTimezone($timezone);

        $lead->addUpdatedField('preferred_locale', $locale);
        $lead->addUpdatedField('mw_id', $mw_id);

        return $lead;
    }
}
