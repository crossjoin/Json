# Json Encoder/Decoder
JSON decoder and encoder for PHP, based on the native PHP json_* functions, but with improved Unicode support, following [RFC 7159](https://tools.ietf.org/html/rfc7159). 


The native PHP functions only support JSON string that are encoded in UTF-8 and do not contain a byte order mark (BOM).

This library wraps the PHP functions and enables you to decode and encode JSON files with the following additional encodings that are allowed by the RFC:
- UTF-16BE
- UTF-16LE
- UTF-32BE
- UTF-32LE

By default (can be disabled) it also ignores byte order marks (BOMs) when parsing, as suggested by the RFC in "[the interests of interoperability](https://tools.ietf.org/html/rfc7159#section-8.1)".

## Installation
Coming soon...

## Usage
Coming soon...
