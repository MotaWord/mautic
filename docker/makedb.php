<?php
// Args: 0 => makedb.php, 1 => "$MAUTIC_DB_HOST", 2 => "$MAUTIC_DB_USER", 3 => "$MAUTIC_DB_PASSWORD", 4 => "$MAUTIC_DB_NAME"
$stderr = fopen('php://stderr', 'w');
fwrite($stderr, "\nEnsuring Mautic database is present\n");

if (strpos($argv[1], ':') !== false)
{
	list($host, $port) = explode(':', $argv[1], 2);
}
else
{
	$host = $argv[1];
	$port = 3306;
}

$maxTries = 10;

do
{
	$mysql = new mysqli($host, $argv[2], $argv[3], '', (int) $port);

	if ($mysql->connect_error)
	{
		fwrite($stderr, "\nMySQL Connection Error: ({$mysql->connect_errno}) {$mysql->connect_error}\n");
		--$maxTries;

		if ($maxTries <= 0)
		{
			exit(1);
		}

		sleep(3);
	}
}
while ($mysql->connect_error);

$initSql = null;

if(!$mysql->select_db($mysql->real_escape_string($argv[4]))){
    $initSql = file_get_contents("/init.sql");
}

if (!$mysql->query('CREATE DATABASE IF NOT EXISTS `' . $mysql->real_escape_string($argv[4]) . '`'))
{
	fwrite($stderr, "\nMySQL 'CREATE DATABASE' Error: " . $mysql->error . "\n");
	$mysql->close();
	exit(1);
}

fwrite($stderr, "\nMySQL Database Created\n");

if($initSql != null){
    $dbName = $mysql->real_escape_string($argv[4]);
    if (!$mysql->multi_query('use `'.$dbName.'`;'.$initSql))
    {
        fwrite($stderr, "\nMySQL : ".'use `'.$dbName.'`;'.$initSql."' \n CREATE UPDATE' Error: " . $mysql->error . "\n");
        $mysql->close();
        exit(1);
    }
}

fwrite($stderr, "\nInitialized database with current config\n");

$mysql->close();
