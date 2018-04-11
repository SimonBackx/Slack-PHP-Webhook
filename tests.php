<?php
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Simple Syntax check
try {
    require('slack.php');
    echo "OK\n";

} catch (ErrorException $ex) {
    echo $ex->getMessage();
    die("FAILED\n");
}