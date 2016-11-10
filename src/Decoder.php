<?php
namespace Crossjoin\Json;

use Crossjoin\Json\Exception\EncodingNotSupportedException;
use Crossjoin\Json\Exception\InvalidArgumentException;
use Crossjoin\Json\Exception\JsonException;

/**
 * Class Decoder
 *
 * @package Crossjoin\Json
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 */
class Decoder extends Converter
{
    /**
     * @var bool
     */
    private $ignoreByteOrderMark = true;

    /**
     * Decoder constructor.
     *
     * @param bool $ignoreByteOrderMark
     *
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    public function __construct($ignoreByteOrderMark = true)
    {
        $this->setIgnoreByteOrderMark($ignoreByteOrderMark);
    }

    /**
     * @return boolean
     */
    public function getIgnoreByteOrderMark()
    {
        return $this->ignoreByteOrderMark;
    }

    /**
     * @param boolean $ignoreByteOrderMark
     *
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    public function setIgnoreByteOrderMark($ignoreByteOrderMark)
    {
        // Check arguments
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_BOOLEAN, 'ignoreByteOrderMark', $ignoreByteOrderMark, 1478195542);

        $this->ignoreByteOrderMark = $ignoreByteOrderMark;
    }

    /**
     * Gets the encoding of the JSON text.
     *
     * @param string $json
     *
     * @return string
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     * @throws \Crossjoin\Json\Exception\EncodingNotSupportedException
     */
    public function getEncoding($json)
    {
        // Check arguments
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_STRING, 'json', $json, 1478195652);

        // Get the first bytes
        $bytes = $this->getEncodingBytes($json);

        // Check encoding
        foreach ($this->getEncodingPatterns() as $encoding => $regExp) {
            if (preg_match($regExp, $bytes)) {
                // Additional check, because the UTF-8 pattern also matches UTF-8 ByteOrderMarks (if ByteOrderMarks
                //have not already been removed before).
                if ($encoding !== self::UTF8 || $this->ignoreByteOrderMark || !preg_match('/^\xEF\xBB\xBF/', $bytes)) {
                    return $encoding;
                }
                break;
            }
        }

        // No encoding found
        throw new EncodingNotSupportedException(
            'The JSON text is encoded with an unsupported encoding.',
            1478092834
        );
    }

    /**
     * @return array
     */
    private function getEncodingPatterns()
    {
        return array(
            // It's UTF-8 encoded JSON if you have...
            // - 1 byte and it's not NUL ("xx")
            // - 2 bytes and none of them are NUL ("xx xx")
            // - 3 bytes and they are not NUL ("xx xx xx")
            // - 4 or more bytes and the first 4 bytes are not NUL ("xx xx xx xx")
            self::UTF8    => '/^(?:[^\x00]{1,3}$|[^\x00]{4})/',
            // It's UTF-16BE encoded JSON if you have...
            // - 2 bytes and only the first is NUL ("00 xx")
            // - 4 or more bytes and only the first byte of the first 2 bytes is NUL ("00 xx")
            self::UTF16BE => '/^(?:\x00[^\x00]{1}$|\x00[^\x00]{1}.{2})/s',
            // It's UTF-16LE encoded JSON if you have...
            // - 2 bytes and only the second is NUL ("xx 00")
            // - 4 or more bytes and only the second of the first 3 bytes is NUL ("xx 00 xx")
            self::UTF16LE => '/^(?:[^\x00]{1}\x00$|[^\x00]{1}\x00[^\x00]{1}.{1})/s',
            // It's UTF-32BE encoded JSON if you have...
            // - 4 or more bytes and only the first to third byte of the first 4 bytes are NUL ("00 00 00 xx")
            self::UTF32BE => '/^[\x00]{3}[^\x00]{1}/',
            // It's UTF-32LE encoded JSON if you have...
            // - 4 or more bytes and only the second to fourth byte of the first 4 bytes are NUL ("xx 00 00 00")
            self::UTF32LE => '/^[^\x00]{1}[\x00]{3}/',
        );
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Parses a valid JSON text that is encoded as UTF-8, UTF-16BE, UTF-16LE, UTF-32BE or UTF-32LE
     * and returns the data as UTF-8.
     *
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     *
     * @return mixed
     * @throws \Crossjoin\Json\Exception\NativeJsonErrorException
     * @throws \Crossjoin\Json\Exception\ConversionFailedException
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     * @throws \Crossjoin\Json\Exception\EncodingNotSupportedException
     * @throws \Crossjoin\Json\Exception\ExtensionRequiredException
     */
    public function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        // Check arguments
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_STRING, 'json', $json, 1478418105);
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_BOOLEAN, 'assoc', $assoc, 1478418106);
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_INTEGER, 'depth', $depth, 1478418107);
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_INTEGER, 'options', $options, 1478418108);

        // Prepare JSON data (remove BOMs and convert encoding)
        $json = $this->prepareJson($json);

        // Try to decode the json text
        // @codeCoverageIgnoreStart
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $data = \json_decode($json, $assoc, $depth, $options);
        } else {
            $data = \json_decode($json, $assoc, $depth);
        }
        // @codeCoverageIgnoreEnd

        // Check if the native JSON decoder created an error
        $this->checkForNativeJsonError();

        return $data;
    }

    /**
     * @param string $json
     *
     * @return string
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    private function getEncodingBytes($json)
    {
        // Do not use str_* function here because of possible mb_str_* overloading
        preg_match('/^(.{0,8})/s', $json, $matches);
        $bytes = array_key_exists(1, $matches) ? $matches[1] : '';

        // Remove byte order marks
        if ($this->ignoreByteOrderMark && $bytes !== '') {
            $bytes = $this->removeByteOrderMark($bytes);
        }

        return $bytes;
    }

    /**
     * @param string $json
     *
     * @return string
     */
    private function prepareJson($json)
    {
        try {
            // Ignore empty string
            // (will cause a parsing error in the native json_decode function)
            if ($json !== '') {
                // Remove byte order marks
                if ($this->ignoreByteOrderMark) {
                    $json = $this->removeByteOrderMark($json);
                }

                // Convert encoding to UTF-8
                $json = $this->convertEncoding($json, $this->getEncoding($json), self::UTF8);
            }
        } catch (JsonException $e) {
            // Ignore exception here, so that the native json_decode function
            // is called by the decode() method and we get the native error message.
        }

        return $json;
    }

    /**
     * @throws \Crossjoin\Json\Exception\NativeJsonErrorException
     */
    private function checkForNativeJsonError()
    {
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw $this->getNativeJsonErrorException();
        }
    }
}
