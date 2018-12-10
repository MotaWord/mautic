<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl3.0.html
 */

$container->loadFromExtension(
    'old_sound_rabbit_mq',
    [
        'debug'       => true,
        'connections' => [
            'microservice' => [
                'host'               => '%mautic.rabbitmq_host%',
                'port'               => '%mautic.rabbitmq_port%',
                'user'               => '%mautic.rabbitmq_user%',
                'password'           => '%mautic.rabbitmq_password%',
                'vhost'              => '%mautic.rabbitmq_vhost%',
                'lazy'               => true,
                'connection_timeout' => 3,
                'heartbeat'          => 2,
                'read_write_timeout' => 4,
            ],
        ],
        'consumers' => [
            'microservice' => [
                'exchange_options' => [
                    'type' => 'topic',
                    'name' => '%mautic.rabbitmq_exchange%',
                ],
                'callback'      => 'mautic.microservice.helper.rabbitmq_consumer',
                'connection'    => 'microservice',
                'queue_options' => [
                    'name'         => 'mautic-microservice',
                    'routing_keys' => [
                        // This is the default routing key we are listening to.
                        // You would typically want to override specifically this config in your local configuration.
                        // See an example in config.php in MotaWordBundle
                        'mautic.#',
                    ],
                    'auto_delete' => false,
                    'durable'     => true,
                ],
            ],
        ],
    ]
);
