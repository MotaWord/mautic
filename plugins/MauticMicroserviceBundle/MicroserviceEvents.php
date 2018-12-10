<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 * @author      MotaWord
 *
 * @link        http://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMicroserviceBundle;

/**
 * Class MicroserviceEvents
 * Events available for MauticMicroserviceBundle.
 */
final class MicroserviceEvents
{
    const CONSUME_MESSAGE = 'mautic.microservice_consume_message';

    const PUBLISH_MESSAGE = 'mautic.microservice_publish_message';

    const BUILD_CONFIG = 'mautic.microservice_build_config';

    const ALL = 'mautic.microservice.*';
}
