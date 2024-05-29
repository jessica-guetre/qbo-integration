<?php

$options = getopt("", ["client_id:", "client_secret:"]);

// Check if client_id and client_secret are provided
if (!isset($options['client_id']) || !isset($options['client_secret'])) {
    die("Error: client_id and client_secret must be provided.\nUsage: php main.php --client_id=YOUR_CLIENT_ID --client_secret=YOUR_CLIENT_SECRET\n");
}

// Load the config and set client_id and client_secret
$config = include('config.php');
$config['client_id'] = $options['client_id'];
$config['client_secret'] = $options['client_secret'];

// Start local server
$command = "php -S localhost:8000 -t .";
echo "Starting server with client_id: {$config['client_id']}\n";
echo "Starting server with client_secret: {$config['client_secret']}\n";
exec($command);
?>
