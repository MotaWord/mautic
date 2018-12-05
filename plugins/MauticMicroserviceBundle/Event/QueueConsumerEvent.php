<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMicroserviceBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\MauticMicroserviceBundle\Queue\QueueConsumerResults;

/**
 * Class QueueConsumerEvent.
 */
class QueueConsumerEvent extends CommonEvent
{
    /**
     * @var array
     */
    private $payload;

    /**
     * @var string
     */
    private $result;

    public function __construct($payload = [])
    {
        $this->payload = $payload;
        $this->result  = QueueConsumerResults::DO_NOT_ACKNOWLEDGE;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
