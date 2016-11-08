# JSON decoder and encoder
[![Author](http://img.shields.io/badge/author-@cziegenberg-blue.svg?style=flat-square)](https://twitter.com/cziegenberg)
[![Quality Score](https://img.shields.io/scrutinizer/g/crossjoin/Json/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/crossjoin/Json)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/crossjoin/Json.svg?style=flat-square)](https://packagist.org/packages/crossjoin/Json)
[![Total Downloads](https://img.shields.io/packagist/dt/crossjoin/Json.svg?style=flat-square)](https://packagist.org/packages/crossjoin/Json)
[![Build](https://img.shields.io/travis/crossjoin/Browscap/master.svg)](https://travis-ci.org/crossjoin/Browscap)

## Introduction
This library provides a JSON decoder and encoder for PHP, based on the native PHP `json_*` functions, but **with full Unicode support**, following [RFC 7159](https://tools.ietf.org/html/rfc7159). 

The native PHP functions only support JSON strings that are encoded in UTF-8 and that do not contain a byte order mark (BOM).

This library wraps the PHP functions and enables you to decode and encode JSON files with the following additional encodings that are allowed by the RFC:
- UTF-16BE
- UTF-16LE
- UTF-32BE
- UTF-32LE

By default (can be disabled) it also ignores byte order marks (BOMs) when parsing, as suggested by the RFC in "[the interests of interoperability](https://tools.ietf.org/html/rfc7159#section-8.1)".

## Installation
You can install the library with Composer. Either via command-line interface...
```
$ composer install crossjoin/json
```

or by adding the following requirement to your `composer.json` file:
```
{
    "require": {
        "crossjoin/json": "~1.0.0"
    }
}
```

### Requirements
The library supports the following PHP versions (the newer, the better the JSON support):
- PHP >= 5.3.3
- PHP >= 7.0.0
- HHVM (tested with version 3.6.6)

One of the following extensions is required to support different encodings than UTF-8:
  - iconv
  - mbstring
  - intl (can only be used together with PHP >= 5.5.0)


## Usage
### Decoding
#### How does it work?
First the JSON string is checked for a byte order mark and the used encoding. If a byte order mark is present, it's removed from the JSON string. If another encoding than UTF-8 is used, the encoding of the JSON string is changed to UTF-8.

Finally the native `json_decode()` function is called to decode the modified JSON string, so the result **relies on the native PHP function** (and may differ between different PHP versions).

All returned data are encoded as UTF-8, so you can work with it in PHP.

#### Basic example
A basic example using the provided functions in the `Crossjoin\Json` namespace:
```php
<?php
// From PHP 5.6.0 you can also use namespaced functions to avoid the namespace
// prefixes below.
//use function \Crossjoin\Json\json_decode, \Crossjoin\Json\json_encode;
//use function \Crossjoin\Json\json_last_error, \Crossjoin\Json\json_last_error_msg;

// Decode example (same arguments as the native PHP function).
//
// Note: If an invalid type is used for one of the arguments, a
// \Crossjoin\Json\Exception\InvalidArgumentException exception is thrown,
// while the native PHP function may accept some of them.
$data = \Crossjoin\Json\json_decode('{"key":"value"}');
//$data = \Crossjoin\Json\json_decode('{"key":"value"}', false,);
//$data = \Crossjoin\Json\json_decode('{"key":"value"}', false, 512);
//$data = \Crossjoin\Json\json_decode('{"key":"value"}', false, 512, \JSON_BIGINT_AS_STRING);

// Also the functions json_last_error() and json_last_error_msg() can be used
if (\Crossjoin\Json\json_last_error() !== \JSON_ERROR_NONE) {
    $errorMessage = \Crossjoin\Json\json_last_error_msg();

    // Error handling
    // ...
} else {
    // Do something with the data
    // ...
}
```

#### Extended example
The function in the previous example exists for an easy replacement of the native function. Internally the function uses the class `\Crossjoin\Json\Decoder`, catches all exceptions (except the ones for invalid arguments) and returns the same result as expected by the native function.

If you use the decoder class directly, you can define how to handle byte order marks, and work with exceptions instead with the `json_last_error*` functions (which nonetheless can still be used).

```php
<?php
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
```

### Encoding
#### How does it work?
The data is encoded using the native `json_encode()` function, so the result **relies on the native PHP function** (and may differ between different PHP versions). 

The input must be encoded as UTF-8 (or the compatible ASCII encoding). The output encoding defaults to UTF-8, but can be set to a different Unicode encoding. The encoding conversion is applied after the encoding of the data as JSON. 

Note: The output does not contain a byte order mark, because this is not allowed by the RFC. It's only allowed to be accepted when decoding JSON.

#### Basic example
A basic example using the provided functions in the `Crossjoin\Json` namespace:
```php
<?php
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
```

#### Extended example
The function in the previous example exists for an easy replacement of the native function. Internally the function uses the class `\Crossjoin\Json\Encoder`, catches all exceptions (except the ones for invalid arguments) and returns the same result as expected by the native function.

If you use the encoder class directly, you can define the encoding for the output, and work with exceptions instead with the `json_last_error*` functions (which can still be used).

```php
<?php
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
```

## Things to know
### Handling of invalid arguments
This library checks the types of the given argument types more strictly.

For example the native json_decode() function also accepts an integer or a boolean as JSON string, which can lead to unexpected results. This library will throw a `\Crossjoin\Json\Exception\InvalidArgumentException` exception in this case.

### Arguments added in later PHP versions
The arguments of the native PHP functions `json_decode` and `json_encode` differ between PHP versions. Older PHP versions do not support all arguments. When using the replacement functions `\Crossjoin\Json\json_decode` and `\Crossjoin\Json\json_encode`, these arguments may be set, but they are only used if the native PHP version supports them.

### Error messages in PHP < 5.5.0
The function `\Crossjoin\Json\json_last_error_msg()` returns the original error message, as returned by the native PHP function `json_last_error_msg()` - except for PHP < 5.5.0, because this function does not exist in these versions. In this case a default error message is returned instead.

### Different results for PHP < 5.5.0
When you try to encode an invalid type in PHP < 5.5.0, for example a resource, the native PHP function `json_encode()` returns an encoded null value and triggers an error.

This behavior has been adjusted to the behavior of PHP 5.5.0, so that this case is handled as an error, resulting in an `\Crossjoin\Json\Exception\InvalidArgumentException` exception. This error cannot be handled with `json_last_error()`, as the error code \JSON_ERROR_UNSUPPORTED_TYPE does not exist before PHP 5.5.0.

