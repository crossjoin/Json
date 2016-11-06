<?php
require __DIR__ . '/../vendor/autoload.php';

use Crossjoin\Json\Decoder;
use Crossjoin\Json\Exception\JsonException;
use Crossjoin\Json\Exception\NativeJsonErrorException;

// Get decoder instance with default options
$decoder = new Decoder();

// Get decoder instance that accepts input with byte order mark (which is the default)
//$decoder = new Decoder(true);

// Get decoder instance that does not accept input with byte order mark
//$decoder = new Decoder(false);

// Byte order mark support may also be enabled/disabled after initialization
//$decoder = new Decoder();
//$decoder->setIgnoreByteOrderMark(false);

// Decode the data
try {
    // Decode example (same arguments as the native PHP function).
    $data = $decoder->decode('{"key":"value"}', false, 512, \JSON_BIGINT_AS_STRING);

    // Do something with the data
    // ...
} catch (NativeJsonErrorException $e) {
    // Catch native JSON errors, wrapped in an exception. The exception's
    // message and code are the same as returned by the native json_last_error*
    // functions (which can still be used)

    // Get native JSON error code, for example \JSON_ERROR_SYNTAX,
    // same as returned by json_last_error()
    $jsonError = $e->getCode();

    // Get native JSON error message, same as returned by json_last_error_msg()
    $jsonErrorMessage = $e->getMessage();

    // Error handling
    // ...
} catch (JsonException $e) {
    // Catch misc errors, for example if JSON with an unsupported encoding was
    // used as input.

    // Error handling
    // ...
}
