<?php
ini_set('log_errors', 1);
ini_set("display_errors", 1);
error_reporting(E_ALL); // Report all errors

// Customize exception handler to out
set_exception_handler(function ($e) {
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
});

?>
