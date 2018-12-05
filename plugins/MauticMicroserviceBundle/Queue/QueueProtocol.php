<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 * @author      MotaWord
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMicroserviceBundle\Queue;

/**
 * Class QueueProtocol.
 */
class QueueProtocol
{
    const BEANSTALKD = 'beanstalkd';
    const RABBITMQ   = 'rabbitmq';
}
