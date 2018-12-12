<?php

/*
 * @copyright   2018 MotaWord. All rights reserved
 * @author      MotaWord
 *
 * @link        https://www.motaword.com
 *
 * @license     Proprietary
 */

namespace MauticPlugin\MotaWordBundle;

/**
 * Class MicroserviceEvents
 * Events available for MauticMicroserviceBundle.
 */
final class MicroserviceEvents
{
    const SEND_EMAIL = 'mautic.microservice.emails.send';

    const START_CAMPAIGN = 'mautic.microservice.emails.start_campaign';

    const USER_UPDATE = 'mautic.microservice.users.update';
}
