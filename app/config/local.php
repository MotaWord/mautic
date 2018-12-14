<?php

$parameters = [
    'db_driver'         => 'pdo_mysql',
    'install_source'    => 'Docker',
    'db_host'           => getenv('MAUTIC_DB_HOST'), // also check docker/makeconfig.php
    'db_name'           => getenv('MAUTIC_DB_NAME'), // also check docker/makeconfig.php
    'db_user'           => getenv('MAUTIC_DB_USER'), // also check docker/makeconfig.php
    'db_password'       => getenv('MAUTIC_DB_PASSWORD'), // also check docker/makeconfig.php
    'db_table_prefix'   => null,
    'db_port'           => '3306',
    'db_backup_tables'  => 1,
    'db_backup_prefix'  => 'bak_',
    'mailer_from_name'  => 'MotaWord',
    'mailer_from_email' => 'info@motaword.com',
    'mailer_transport'  => 'mautic.transport.amazon',
    'mailer_host'       => null,
    'mailer_port'       => null,
    'mailer_user'       => 'AKIAJOKWUZK42YTBAY3Q', // "mautic-smtp-mailer" IAM user
    'mailer_password'   => 'AvJxoI1MrEEIaRfvg1v+b5x/B9rtTQGlFuRTEyUpX5Ra',
    'mailer_encryption' => null,
    'mailer_auth_mode'  => null,
    'mailer_spool_type' => 'memory',
    'mailer_spool_path' => '%kernel.root_dir%/spool',
    'secret_key'        => '72d50fb4c3402a2ebcbabf9fe2f5cc86776d49001cb1b4aeb17814096a5b7161',
    'dev_hosts'         => [
        '0' => '127.0.0.1',
        '1' => '127.0.0.1:8085',
        '2' => 'localhost',
        '3' => '172.18.0.1',
    ],
    'webroot'       => null,
    //'cache_path'    => '/tmp/app/cache',
    // Problematic when run outside the docker container.
    //'log_path'      => '/var/log/mautic',
    'image_path'    => 'media/images',
    //'tmp_path'      => '/tmp/app/cache',
    'theme'         => 'Mauve',
    'locale'        => 'en_US',
    'trusted_hosts' => [
    ],
    'trusted_proxies' => [
    ],
    'rememberme_key'       => '8b0a6f487692fae19cb33afa084d4cd158946683',
    'rememberme_lifetime'  => '31536000',
    'rememberme_path'      => '/',
    'default_pagelimit'    => 30,
    'default_timezone'     => 'UTC',
    'date_format_full'     => 'F j, Y g:i a T',
    'date_format_short'    => 'D, M d',
    'date_format_dateonly' => 'F j, Y',
    'date_format_timeonly' => 'g:i a',
    'ip_lookup_service'    => 'maxmind_download',
    'ip_lookup_auth'       => null,
    'ip_lookup_config'     => [
    ],
    'transifex_username' => null,
    'update_stability'   => 'stable',
    'cookie_path'        => '/',
    'cookie_secure'      => null,
    'do_not_track_ips'   => [
    ],
    'do_not_track_bots' => [
        '0'  => 'MSNBOT',
        '1'  => 'msnbot-media',
        '2'  => 'bingbot',
        '3'  => 'Googlebot',
        '4'  => 'Google Web Preview',
        '5'  => 'Mediapartners-Google',
        '6'  => 'Baiduspider',
        '7'  => 'Ezooms',
        '8'  => 'YahooSeeker',
        '9'  => 'Slurp',
        '10' => 'AltaVista',
        '11' => 'AVSearch',
        '12' => 'Mercator',
        '13' => 'Scooter',
        '14' => 'InfoSeek',
        '15' => 'Ultraseek',
        '16' => 'Lycos',
        '17' => 'Wget',
        '18' => 'YandexBot',
        '19' => 'Java/1.4.1_04',
        '20' => 'SiteBot',
        '21' => 'Exabot',
        '22' => 'AhrefsBot',
        '23' => 'MJ12bot',
        '24' => 'NetSeer crawler',
        '25' => 'TurnitinBot',
        '26' => 'magpie-crawler',
        '27' => 'Nutch Crawler',
        '28' => 'CMS Crawler',
        '29' => 'rogerbot',
        '30' => 'Domnutch',
        '31' => 'ssearch_bot',
        '32' => 'XoviBot',
        '33' => 'digincore',
        '34' => 'fr-crawler',
        '35' => 'SeznamBot',
        '36' => 'Seznam screenshot-generator',
        '37' => 'Facebot',
        '38' => 'facebookexternalhit',
    ],
    'do_not_track_internal_ips' => [
    ],
    'link_shortener_url'    => null,
    'cached_data_timeout'   => '10',
    'batch_sleep_time'      => '1',
    'cors_restrict_domains' => 0,
    'cors_valid_domains'    => [
    ],
    'rss_notification_url'              => 'https://mautic.com/?feed=rss2&tag=notification',
    'max_entity_lock_time'              => 0,
    'api_enabled'                       => 1,
    'api_enable_basic_auth'             => true,
    'api_oauth2_access_token_lifetime'  => 60,
    'api_oauth2_refresh_token_lifetime' => 14,
    'api_batch_max_limit'               => '200',
    'upload_dir'                        => '/var/www/html/app/../media/files',
    'max_size'                          => '6',
    'allowed_extensions'                => [
        '0'  => 'csv',
        '1'  => 'doc',
        '2'  => 'docx',
        '3'  => 'epub',
        '4'  => 'gif',
        '5'  => 'jpg',
        '6'  => 'jpeg',
        '7'  => 'mpg',
        '8'  => 'mpeg',
        '9'  => 'mp3',
        '10' => 'odt',
        '11' => 'odp',
        '12' => 'ods',
        '13' => 'pdf',
        '14' => 'png',
        '15' => 'ppt',
        '16' => 'pptx',
        '17' => 'tif',
        '18' => 'tiff',
        '19' => 'txt',
        '20' => 'xls',
        '21' => 'xlsx',
        '22' => 'wav',
    ],
    'campaign_time_wait_on_event_false' => 'PT1H',
    'mailer_return_path'                => null,
    'mailer_append_tracking_pixel'      => 1,
    'mailer_convert_embed_images'       => 0,
    'mailer_amazon_region'              => 'email-smtp.us-east-1.amazonaws.com',
    'mailer_custom_headers'             => [
    ],
    'mailer_spool_msg_limit'       => null,
    'mailer_spool_time_limit'      => null,
    'mailer_spool_recover_timeout' => '900',
    'mailer_spool_clear_timeout'   => '1800',
    'unsubscribe_text'             => '<a href="|URL|">Unsubscribe</a> to no longer receive emails from us.',
    'webview_text'                 => '<a href="|URL|">Having trouble reading this email? Click here.</a>',
    'unsubscribe_message'          => 'We are sorry to see you go! |EMAIL| will no longer receive emails from us. If this was by mistake, <a href="|URL|">click here to re-subscribe</a>.',
    'resubscribe_message'          => '|EMAIL| has been re-subscribed. If this was by mistake, <a href="|URL|">click here to unsubscribe</a>.',
    'monitored_email'              => [
        'general' => [
            'address'    => '',
            'host'       => '',
            'port'       => '993',
            'encryption' => '/ssl',
            'user'       => '',
            'password'   => '',
        ],
        'EmailBundle_bounces' => [
            'address'           => '',
            'host'              => '',
            'port'              => '993',
            'encryption'        => '/ssl',
            'user'              => '',
            'password'          => '',
            'override_settings' => '0',
            'folder'            => '',
        ],
        'EmailBundle_unsubscribes' => [
            'address'           => '',
            'host'              => '',
            'port'              => '993',
            'encryption'        => '/ssl',
            'user'              => '',
            'password'          => '',
            'override_settings' => '0',
            'folder'            => '',
        ],
        'EmailBundle_replies' => [
            'address'           => '',
            'host'              => '',
            'port'              => '993',
            'encryption'        => '/ssl',
            'user'              => '',
            'password'          => '',
            'override_settings' => '0',
            'folder'            => '',
        ],
    ],
    'mailer_is_owner'                       => 0,
    'default_signature_text'                => 'Best regards, |FROM_NAME|',
    'email_frequency_number'                => null,
    'email_frequency_time'                  => null,
    'show_contact_preferences'              => 0,
    'show_contact_frequency'                => 0,
    'show_contact_pause_dates'              => 0,
    'show_contact_preferred_channels'       => 0,
    'show_contact_categories'               => 0,
    'show_contact_segments'                 => 0,
    'mailer_mailjet_sandbox'                => 0,
    'mailer_mailjet_sandbox_default_mail'   => null,
    'disable_trackable_urls'                => 0,
    'parallel_import_limit'                 => '1',
    'background_import_if_more_rows_than'   => 0,
    'cat_in_page_url'                       => 0,
    'google_analytics'                      => null,
    'track_contact_by_ip'                   => 0,
    'track_by_tracking_url'                 => 0,
    'track_by_fingerprint'                  => 0,
    'facebook_pixel_id'                     => null,
    'facebook_pixel_trackingpage_enabled'   => 0,
    'facebook_pixel_landingpage_enabled'    => 0,
    'google_analytics_id'                   => null,
    'google_analytics_trackingpage_enabled' => 0,
    'google_analytics_landingpage_enabled'  => 0,
    'google_analytics_anonymize_ip'         => 0,
    'queue_protocol'                        => 'rabbitmq',
    'rabbitmq_host'                         => getenv('RABBITMQ_HOST'), // also check docker/makeconfig.php
    'rabbitmq_port'                         => '5672',
    'rabbitmq_vhost'                        => '/',
    'rabbitmq_user'                         => getenv('RABBITMQ_USER'), // also check docker/makeconfig.php
    'rabbitmq_password'                     => getenv('RABBITMQ_PASSWORD'), // also check docker/makeconfig.php
    'rabbitmq_exchange'                     => 'default-exchange',
    'beanstalkd_host'                       => 'localhost',
    'beanstalkd_port'                       => '11300',
    'beanstalkd_timeout'                    => '60',
    'sms_username'                          => null,
    'sms_password'                          => null,
    'sms_sending_phone_number'              => null,
    'sms_frequency_number'                  => null,
    'sms_frequency_time'                    => null,
    'sms_transport'                         => null,
    'saml_idp_entity_id'                    => 'http://localhost',
    'saml_idp_own_password'                 => null,
    'saml_idp_email_attribute'              => 'EmailAddress',
    'saml_idp_username_attribute'           => null,
    'saml_idp_firstname_attribute'          => 'FirstName',
    'saml_idp_lastname_attribute'           => 'LastName',
    'saml_idp_default_role'                 => null,
    'webhook_start'                         => '0',
    'webhook_limit'                         => '10',
    'webhook_log_max'                       => '1000',
    'webhook_disable_limit'                 => '100',
    'webhook_timeout'                       => '15',
    'queue_mode'                            => 'immediate_process',
    'events_orderby_dir'                    => 'ASC',
    'twitter_handle_field'                  => 'twitter',
];
