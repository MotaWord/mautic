<?php

return [
    'name'        => 'MotaWord Localization Bundle',
    'description' => 'This bundle integrates MotaWord natively to localize emails. ',
    'version'     => '1.0',
    'author'      => 'MotaWord',

    ////////
    ///
    'services' => [
        'other' => [
            'mautic.motaword.syncservice' => [
                'class'     => 'MauticPlugin\MauticMotaWordBundle\SyncService',
                'public'    => true,
                'arguments' => [
                    'mautic.email.model.email',
                    'monolog.logger.mautic',
                    'mautic.helper.core_parameters',
                ],
            ],
        ],
    ],
];
