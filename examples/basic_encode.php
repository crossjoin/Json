<?php
require __DIR__ . '/../vendor/autoload.php';

// From PHP 5.6.0 you can also use namespaced functions to avoid the namespace
// prefixes below.
//use function \Crossjoin\Json\json_decode, \Crossjoin\Json\json_encode;
//use function \Crossjoin\Json\json_last_error, \Crossjoin\Json\json_last_error_msg;

// Encode example (same arguments as the native PHP function)
//
// Note: If an invalid type is used for one of the arguments, a
// \Crossjoin\Json\Exception\InvalidArgumentException exception is thrown,
// while the native PHP function may accept some of them.
$json = \Crossjoin\Json\json_encode(123);
//$json = \Crossjoin\Json\json_encode(123, \JSON_NUMERIC_CHECK);
//$json = \Crossjoin\Json\json_encode(123, \JSON_NUMERIC_CHECK, 512);

// Also the functions json_last_error() and json_last_error_msg() can be used
if (\Crossjoin\Json\json_last_error() !== \JSON_ERROR_NONE) {
    $errorMessage = \Crossjoin\Json\json_last_error_msg();

    // Error handling
    // ...
} else {
    // Do something with the JSON string
    // ...
}
