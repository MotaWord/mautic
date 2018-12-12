<?php

// i think there must be a better configuration scheme.
// we should be configuring MicroserviceBundle, not RabbitMQ bundle.
$container->loadFromExtension(
    'old_sound_rabbit_mq',
    [
        'consumers' => [
            'microservice' => [
                'queue_options' => [
                    'name'         => 'mautic-microservice',
                    'routing_keys' => [
                        'emails.#',
                        'users.#', // @todo listen to user updates to change name, locale, timezone etc.
                    ],
                ],
            ],
        ],
    ]
);

return [
    'name'        => 'MotaWord Email Microservice Bundle',
    'description' => 'This bundle integrates Mautic natively with MotaWord. '.
        'It adds functionality to make Mautic behave as a MotaWord '.
        'microservice for all email communication and related needs.',
    'version' => '1.0',
    'author'  => 'MotaWord',

    ////////

    'routes' => [
        'api' => [
            // This is identical to Mautic's send endpoint.
            // My plan here is to use a HTTP header in the incoming request for MW.
            // Then intercept the mwUserId and convert to lead ID and forward to normal Mautic flow.
            // Another way to achieve this is simply to use Event Listeners or Middlewares.
            // I think event or middlewares are MUCH BETTER methods.
            // Simply detect MotaWord header and convert user ID to lead ID.
            // If header not present, then the ID here is really the lead ID.
            // Leads are contacts in Mautic's dashboard.
            'plugin_motaword_api_sendcontactemail_mwuser' => [
                'path'         => '/emails/{id}/contact/{mwUserId}/send',
                'controller'   => 'MotaWordBundle:Api\EmailApi:sendToUser',
                'method'       => 'POST',
                'requirements' => [
                    'id' => '\d+', // Original Mautic endpoint should require \d+ only.
                ],
            ],
            // This is another way we can improve email functionality.
            // Expecting email template ID from external world won't work. So we need to support email names.
            // This is almost the same endpoint with Mautic's (also our own replication of Mautic's endpoint above)
            // The only difference is that {name} accepts string and, rather than only email ID.
            // Then we consider contact ID as MW User ID (just because this is a completely new endpoint
            // added by us, otherwise, we would need to preserve leadId support)
            // Example: /emails/hello_world/1/send
            'plugin_motaword_api_sendcontactemail_emailname' => [
                'path'         => '/emails/{name}/contact/{mwUserId}/send',
                'controller'   => 'MotaWordBundle:Api\EmailApi:sendToUser',
                'method'       => 'POST',
                'requirements' => [
                    'name' => '[a-zA-Z0-9]+', // Original Mautic endpoint should require \d+ only.
                ],
            ],
            // DO NOT IMPLEMENT FOR NOW.
            // This is an endpoint for generic email sending, no contacts, no email templates.
            // MW user ID and Mautic template name can be specified in the request, if necessary, in body, query or header.
            // We can also simply choose this method and drop usage of Mautic's send endpoints.
            // Example request can be like: {"template": "hello_world", "mw_user_id": 1, "data": {..custom variables..}}
            'plugin_motaword_api_send_email' => [
                'path'       => '/send',
                'controller' => 'MotaWordBundle:Api\EmailApi:send',
                'method'     => 'POST',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'mautic.motaword.emails.send_listener' => [
                'class'     => 'MauticPlugin\MotaWordBundle\EventListeners\SendEmailListener',
                'arguments' => [
                    'mautic.factory',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.email.model.email',
                    'monolog.logger.mautic',
                ],
            ],
            'mautic.motaword.emails.start_campaign_listener' => [
                'class'     => 'MauticPlugin\MotaWordBundle\EventListeners\StartCampaignListener',
                'arguments' => [
                    'mautic.factory',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.email.model.email',
                    'monolog.logger.mautic',
                ],
            ],
            'mautic.motaword.users.user_update_listener' => [
                'class'     => 'MauticPlugin\MotaWordBundle\EventListeners\UserUpdateListener',
                'arguments' => [
                    'mautic.factory',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.email.model.email',
                    'monolog.logger.mautic',
                ],
            ],
        ],
    ],
];
