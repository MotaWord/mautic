<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl3.0.html
 */



////// @todo @todo @todo
/// instead of this file, write a Configuration class in DependencyInjection
/// for a config scheme to be used in an application-level bundle or general application config.
$container->loadFromExtension(
    'old_sound_rabbit_mq',
    [
        'connections' => [
            'microservice' => [
                'host' => '%mautic.rabbitmq_host%',
                'port' => '%mautic.rabbitmq_port%',
                'user' => '%mautic.rabbitmq_user%',
                'password' => '%mautic.rabbitmq_password%',
                'vhost' => '%mautic.rabbitmq_vhost%',
                'lazy' => true,
                'connection_timeout' => 3,
                'heartbeat' => 2,
                'read_write_timeout' => 4,
            ],
        ],
        'consumers' => [
            'microservice' => [
                'exchange_options' => [
                    'type' => 'topic',
                    'name' => 'default-exchange',
                ],
                'callback' => 'mautic.microservice.helper.rabbitmq_consumer',
                'connection' => 'microservice',
                'queue_options' => [
                    'name' => 'mautic-microservice',
                    'routing_keys' => [
                        'emails.#'
                    ]
                ],
            ],
        ],
    ]
);
