<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'services' => [
        'events' => [
            'mautic.microservice.rabbitmq.subscriber' => [
                'public' => true,
                'class'     => 'MauticPlugin\MauticMicroserviceBundle\EventListener\RabbitMqSubscriber',
                'arguments' => 'service_container',
            ],
            'mautic.microservice.beanstalkd.subscriber' => [
                'public' => true,
                'class'     => 'MauticPlugin\MauticMicroserviceBundle\EventListener\BeanstalkdSubscriber',
                'arguments' => [
                    'service_container',
                    'mautic.microservice.service',
                ],
            ],
            'mautic.microservice.emails.subscriber' => [
                'class'     => 'MauticPlugin\MauticMicroserviceBundle\EventListener\EmailSubscriber',
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.email.model.email',
                    'monolog.logger.mautic',
                ],
            ],
        ],
        'other' => [
            'mautic.microservice.service' => [
                'class'     => 'MauticPlugin\MauticMicroserviceBundle\Queue\QueueService',
                'public' => true,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'event_dispatcher',
                    'monolog.logger.mautic',
                ],
            ],
            'mautic.microservice.helper.rabbitmq_consumer' => [
                'public' => true,
                'class'     => 'MauticPlugin\MauticMicroserviceBundle\Helper\RabbitMqConsumer',
                'arguments' => 'mautic.microservice.service',
            ],
        ],
    ],
];
