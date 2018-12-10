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
 * Class MicroserviceConsumerResults.
 */
final class MicroserviceConsumerResults
{
    const ACKNOWLEDGE        = 'delete';
    const DO_NOT_ACKNOWLEDGE = 'do_not_acknowledge';
    const REJECT             = 'do_not_retry';
    const TEMPORARY_REJECT   = 'temporary_reject';
}
