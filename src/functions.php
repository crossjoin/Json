<?php
namespace Crossjoin\Json;

use Crossjoin\Json\Exception\InvalidArgumentException;
use Crossjoin\Json\Exception\JsonException;

/**
 * Returns the JSON representation of a value, encoded in UTF-8
 * (or false on failure)
 *
 * @param mixed $value
 * @param int $options
 * @param int $depth
 *
 * For details about the parameters and the return values see the native json_encode() function.
 * @link http://php.net/manual/en/function.json-encode.php
 *
 * @return string|false
 * @throws \Crossjoin\Json\Exception\InvalidArgumentException
 */
function json_encode($value, $options = 0, $depth = 512)
{
    $encoder = new Encoder();
    try {
        return $encoder->encode($value, $options, $depth);
    } catch (InvalidArgumentException $e) {
        throw $e;
    } catch (JsonException $e) {
        return false;
    }
}

/** @noinspection MoreThanThreeArgumentsInspection */
/**
 * Decodes a JSON string (encoded as UTF-8, UTF-16BE, UTF-16LE, UTF-32BE or UTF-32LE,
 * with or without byte order mark)
 *
 * @param mixed $json
 * @param bool $assoc
 * @param int $depth
 * @param int $options
 *
 * For details about the parameters and the return values see the native json_decode() function.
 * @link http://php.net/manual/en/function.json-decode.php
 *
 * @return mixed
 * @throws \Crossjoin\Json\Exception\InvalidArgumentException
 */
function json_decode($json, $assoc = false, $depth = 512, $options = 0)
{
    $decoder = new Decoder();
    try {
        return $decoder->decode($json, $assoc, $depth, $options);
    } catch (InvalidArgumentException $e) {
        throw $e;
    } catch (JsonException $e) {
        return null;
    }
}

/**
 * Returns the code of the last error occurred
 *
 * For details about the return values see the native json_last_error() function
 * @link http://php.net/manual/en/function.json-last-error.php
 *
 * @return int
 */
function json_last_error () {
    return \json_last_error();
}

/**
 * Returns the error string of the last json_encode() or json_decode() call
 *
 * For details about the return values see the native json_last_error_msg() function
 * @link http://php.net/manual/en/function.json-last-error-msg.php
 *
 * @return string|false
 */
function json_last_error_msg ()  {
    if (function_exists('\json_last_error_msg')) {
        return \json_last_error_msg();
    }
    return 'An error occurred while encoding or decoding JSON.';
}
