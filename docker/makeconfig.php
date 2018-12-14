<?php

$path     = '/var/www/html/app/config/local.php';
$stderr   = fopen('php://stderr', 'w');

fwrite($stderr, "\nUpdating Mautic config with environment variables\n");

$parameters = [];
// $path contains $parameters
if (is_readable($path)) {
    require $path;
}

// Database env vars
if (array_key_exists('MAUTIC_DB_HOST', $_ENV)) {
    // Figure out if we have a port in the database host string
    if (strpos($_ENV['MAUTIC_DB_HOST'], ':') !== false) {
        list($host, $port)     = explode(':', $_ENV['MAUTIC_DB_HOST'], 2);
        $parameters['db_port'] = $port;
    } else {
        $host = $_ENV['MAUTIC_DB_HOST'];
    }
    $parameters['db_host'] = $host;
}
if (array_key_exists('MAUTIC_DB_NAME', $_ENV)) {
    $parameters['db_name'] = $_ENV['MAUTIC_DB_NAME'];
}
if (array_key_exists('MAUTIC_DB_USER', $_ENV)) {
    $parameters['db_user'] = $_ENV['MAUTIC_DB_USER'];
}
if (array_key_exists('MAUTIC_DB_PASSWORD', $_ENV)) {
    $parameters['db_password'] = $_ENV['MAUTIC_DB_PASSWORD'];
}
if (array_key_exists('MAUTIC_TRUSTED_PROXIES', $_ENV)) {
    $proxies                       = explode(',', $_ENV['MAUTIC_TRUSTED_PROXIES']);
    $parameters['trusted_proxies'] = $proxies;
}

// RabbitMQ env vars
if (array_key_exists('RABBITMQ_HOST', $_ENV)) {
    $parameters['rabbitmq_host'] = $_ENV['RABBITMQ_HOST'];
}
if (array_key_exists('RABBITMQ_USER', $_ENV)) {
    $parameters['rabbitmq_user'] = $_ENV['RABBITMQ_USER'];
}
if (array_key_exists('RABBITMQ_PASSWORD', $_ENV)) {
    $parameters['rabbitmq_password'] = $_ENV['RABBITMQ_PASSWORD'];
}

$rendered = "<?php\n\$parameters = ".var_export($parameters, true).";\n";

$status = file_put_contents($path, $rendered);

if ($status === false) {
    fwrite($stderr, "\nCould not write configuration file to $path, you can create this file with the following contents:\n\n$rendered\n");
}

fwrite($stderr, "\nNew local.php: ".$rendered);
