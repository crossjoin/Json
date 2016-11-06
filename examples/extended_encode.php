<?php
require __DIR__ . '/../vendor/autoload.php';

use Crossjoin\Json\Encoder;
use Crossjoin\Json\Exception\JsonException;
use Crossjoin\Json\Exception\NativeJsonErrorException;

// Get encoder instance with default options
$encoder = new Encoder();

// Get encoder instance that uses UTF-8 encoding for the output (which is the default)
//$encoder = new Encoder(Encoder::UTF8);

// Get encoder instance that uses UTF-16BE encoding for the output
//$encoder = new Encoder(Encoder::UTF16BE);

// Get encoder instance that uses UTF-16LE encoding for the output
//$encoder = new Encoder(Encoder::UTF16LE);

// Get encoder instance that uses UTF-16BE encoding for the output
//$encoder = new Encoder(Encoder::UTF32BE);

// Get encoder instance that uses UTF-16LE encoding for the output
//$encoder = new Encoder(Encoder::UTF32LE);

// The encoding may also be changed after initialization
//$encoder = new Encoder();
//$encoder->setEncoding(Encoder::UTF16BE);

// Encode the data
try {
    // Encode example (same arguments as the native PHP function).
    $json = $encoder->encode('my data', \JSON_NUMERIC_CHECK, 512);

    // Do something with the JSON string
    // ...
} catch (NativeJsonErrorException $e) {
    // Catch native JSON errors, wrapped in an exception. The exception's
    // message and code are the same as returned by the native json_last_error*
    // functions (which can still be used)

    // Get native JSON error code, for example \JSON_ERROR_UNSUPPORTED_TYPE,
    // same as returned by json_last_error()
    $jsonError = $e->getCode();

    // Get native JSON error message, same as returned by json_last_error_msg()
    $jsonErrorMessage = $e->getMessage();

    // Error handling
    // ...
} catch (JsonException $e) {
    // Catch misc errors, for example if invalid data was used as input.

    // Error handling
    // ...
}