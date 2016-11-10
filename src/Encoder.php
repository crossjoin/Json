<?php
namespace Crossjoin\Json;

use Crossjoin\Json\Exception\EncodingNotSupportedException;
use Crossjoin\Json\Exception\InvalidArgumentException;

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
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_STRING, 'encoding', $encoding, 1478196374);

        if (in_array($encoding, array(self::UTF8, self::UTF16BE, self::UTF16LE, self::UTF32BE, self::UTF32LE), true)) {
            $this->encoding = $encoding;
        } else {
            throw new EncodingNotSupportedException(sprintf("Unsupported encoding '%s'.", $encoding), 1478101930);
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
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_INTEGER, 'options', $options, 1478418109);
        InvalidArgumentException::validateArgument(InvalidArgumentException::TYPE_INTEGER, 'depth', $depth, 1478418110);

        // Try to encode the data
        // @codeCoverageIgnoreStart
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $json = $this->encodePhpGte55($value, $options, $depth);
        } else {
            $json = $this->encodePhpLt55($value, $options);
        }
        // @codeCoverageIgnoreEnd

        // Convert
        return $this->convertEncoding($json, self::UTF8, $this->getEncoding());
    }

    /**
     * @param mixed $value
     * @param int $options
     * @param int $depth
     *
     * @return string
     * @throws \Crossjoin\Json\Exception\NativeJsonErrorException
     */
    private function encodePhpGte55($value, $options, $depth)
    {
        // @codeCoverageIgnoreStart
        $json = \json_encode($value, $options, $depth);

        if (!is_string($json)) {
            throw $this->getNativeJsonErrorException();
        }

        return $json;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param mixed $value
     * @param int $options
     *
     * @return string
     * @throws \Crossjoin\Json\Exception\NativeJsonErrorException
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    private function encodePhpLt55($value, $options)
    {
        // Although the json_last_error() function exists, json_encode() in PHP < 5.5.0 sometimes
        // triggers an error, for example when an unsupported type is tried to be encoded. We
        // suppress these errors and throw an own exception instead.

        // @codeCoverageIgnoreStart
        $json = @\json_encode($value, $options);
        if ($value !== null && $json === 'null') {
            throw new InvalidArgumentException('The type tried to encode is not supported.', 1478445896);
        }

        if (!is_string($json)) {
            throw $this->getNativeJsonErrorException();
        }

        return $json;
        // @codeCoverageIgnoreEnd
    }
}
