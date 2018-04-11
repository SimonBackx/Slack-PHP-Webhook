<?php
function exception_error_handler($errno, $errstr, $errfile, $errline) {
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Simple Syntax check
try {
	require 'slack.php';
	echo "OK\n";

} catch (ErrorException $ex) {
	echo $ex->getMessage();
	die("FAILED\n");
}

// Insert example script here to generate a preview URL
$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');

// Create a new message
$message = new SlackMessage($slack);

echo 'https://api.slack.com/docs/messages/builder?msg=' . rawurlencode(json_encode($message->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo PHP_EOL;