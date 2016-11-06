<?php
namespace Crossjoin\Json;

use Crossjoin\Json\Exception\ConversionFailedException;
use Crossjoin\Json\Exception\EncodingNotSupportedException;
use Crossjoin\Json\Exception\InvalidArgumentException;
use Crossjoin\Json\Exception\NativeJsonErrorException;

/**
 * Class Encoder
 *
 * @package Crossjoin\Json
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 */
class Encoder extends Converter
{
    const UTF16 = self::UTF16BE;
    const UTF32 = self::UTF32BE;

    /**
     * @var string
     */
    private $encoding = self::UTF8;

    /**
     * Encoder constructor.
     *
     * @param string $encoding
     * @throws \Crossjoin\Json\Exception\EncodingNotSupportedException
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    public function __construct($encoding = self::UTF8)
    {
        $this->setEncoding($encoding);
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     *
     * @throws \Crossjoin\Json\Exception\EncodingNotSupportedException
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    public function setEncoding($encoding)
    {
        if (is_string($encoding)) {
            if (in_array($encoding, array(self::UTF8, self::UTF16BE, self::UTF16LE, self::UTF32BE, self::UTF32LE), true)) {
                $this->encoding = $encoding;
            } else {
                throw new EncodingNotSupportedException(sprintf("Unsupported encoding '%s'.", $encoding), 1478101930);
            }
        } else {
            throw new InvalidArgumentException(
                sprintf("String expected for argument '%s'. Got '%s'.", 'encoding', gettype($encoding)),
                1478196374
            );
        }
    }

    /**
     * @param mixed $value
     * @param int $options
     * @param int $depth
     *
     * @return string
     * @throws \Crossjoin\Json\Exception\NativeJsonErrorException
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     * @throws \Crossjoin\Json\Exception\ExtensionRequiredException
     * @throws \Crossjoin\Json\Exception\ConversionFailedException
     */
    public function encode($value, $options = 0, $depth = 512)
    {
        // Check arguments
        if (!is_int($options)) {
            throw new InvalidArgumentException(
                sprintf("Integer expected for argument '%s'. Got '%s'.", 'options', gettype($options)),
                1478418109
            );
        }
        if (!is_int($depth)) {
            throw new InvalidArgumentException(
                sprintf("Integer expected for argument '%s'. Got '%s'.", 'depth', gettype($depth)),
                1478418110
            );
        }

        $toEncoding = $this->getEncoding();

        // Try to encode the data
        // @codeCoverageIgnoreStart
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $json = \json_encode($value, $options, $depth);
        } else {
            // Although the json_last_error() function exists, json_encode() in PHP < 5.5.0 sometimes
            // triggers an error, for example when an unsupported type is tried to be encoded. We
            // suppress these errors and throw an own exception instead.
            $json = @\json_encode($value, $options);
            if ($value !== null && $json === 'null') {
                throw new InvalidArgumentException('The type tried to encode is not supported.', 1478445896);
            }
        }
        // @codeCoverageIgnoreEnd

        if ($json === false) {
            if (function_exists('\json_last_error_msg')) {
                throw new NativeJsonErrorException(\json_last_error_msg(), \json_last_error());
            } else {
                throw new NativeJsonErrorException('An error occurred while encoding JSON.', \json_last_error());
            }
        }

        // Convert
        if ($toEncoding !== self::UTF8) {
            $json = $this->convertEncoding($json, self::UTF8, $toEncoding);
        }

        return $json;
    }
}
