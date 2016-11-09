<?php
namespace Crossjoin\Json;

use Crossjoin\Json\Exception\ConversionFailedException;
use Crossjoin\Json\Exception\ExtensionRequiredException;
use Crossjoin\Json\Exception\InvalidArgumentException;
use Crossjoin\Json\Exception\NativeJsonErrorException;

/**
 * Class Converter
 *
 * @package Crossjoin\Json
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 */
abstract class Converter
{
    const UTF8    = 'UTF-8';
    const UTF16BE = 'UTF-16BE';
    const UTF16LE = 'UTF-16LE';
    const UTF32BE = 'UTF-32BE';
    const UTF32LE = 'UTF-32LE';

    /**
     * @param string $string
     * @param string $fromEncoding
     * @param string $toEncoding
     *
     * @return string
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     * @throws \Crossjoin\Json\Exception\ConversionFailedException
     * @throws \Crossjoin\Json\Exception\ExtensionRequiredException
     */
    public function convertEncoding($string, $fromEncoding, $toEncoding)
    {
        // Check arguments
        if (!is_string($string)) {
            throw InvalidArgumentException::getInstance('string', 'json', $string, 1478195990);
        }
        if (!is_string($fromEncoding)) {
            throw InvalidArgumentException::getInstance('string', 'fromEncoding', $fromEncoding, 1478195991);
        }
        if (!is_string($toEncoding)) {
            throw InvalidArgumentException::getInstance('string', 'toEncoding', $toEncoding, 1478195992);
        }

        // Try different conversion functions, ordered by speed
        if (
            ($convertedString = $this->convertWithIconv($string, $fromEncoding, $toEncoding)) === null &&
            ($convertedString = $this->convertWithUConverter($string, $fromEncoding, $toEncoding)) === null &&
            ($convertedString = $this->convertWithMultiByteString($string, $fromEncoding, $toEncoding)) === null
        ) {
            throw new ExtensionRequiredException(
                "The 'iconv', 'intl' or the 'mbstring' extension is required to convert the JSON encoding.",
                1478095252
            );
        }

        return $convertedString;
    }

    /**
     * Removes the byte order mark (BOM) from the JSON text. This is not allowed in JSON,
     * but may be ignored when parsing it.
     *
     * @param string $string
     *
     * @return string
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    public function removeByteOrderMark($string)
    {
        // Check arguments
        if (!is_string($string)) {
            throw InvalidArgumentException::getInstance('string', 'string', $string, 1478195910);
        }

        return (string)preg_replace(
            '/^(?:' .
            '\xEF\xBB\xBF|' .     // UTF-8 BOM
            '\x00\x00\xFE\xFF|' . // UTF-32BE BOM
            '\xFF\xFE\x00\x00|' . // UTF-32LE BOM (before UTF-16LE check!)
            '\xFE\xFF|' .         // UTF-16BE BOM
            '\xFF\xFE' .          // UTF-16LE BOM
            ')/',
            '',
            $string
        );
    }

    /**
     * @return NativeJsonErrorException
     */
    protected function getNativeJsonErrorException()
    {
        if (function_exists('\json_last_error_msg')) {
            return new NativeJsonErrorException(\json_last_error_msg(), \json_last_error());
        } else {
            return new NativeJsonErrorException('An error occurred while encoding/decoding JSON.', \json_last_error());
        }
    }

    /**
     * @param $string
     * @param $fromEncoding
     * @param $toEncoding
     *
     * @return string|null
     * @throws \Crossjoin\Json\Exception\ConversionFailedException
     */
    private function convertWithIconv($string, $fromEncoding, $toEncoding)
    {
        // @codeCoverageIgnoreStart
        if (function_exists('iconv')) {
            $string = iconv($fromEncoding, $toEncoding . '//IGNORE', $string);
            if ($string === false) {
                throw new ConversionFailedException('Error while converting the encoding.', 1478193725);
            }
            return $string;
        }
        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param $string
     * @param $fromEncoding
     * @param $toEncoding
     *
     * @return string|null
     */
    private function convertWithUConverter($string, $fromEncoding, $toEncoding)
    {
        // @codeCoverageIgnoreStart
        if (class_exists('\\UConverter')) {
            /** @noinspection PhpUndefinedClassInspection */
            $uConverter = new \UConverter($toEncoding, $fromEncoding);
            /** @noinspection PhpUndefinedMethodInspection */
            return $uConverter->convert($string);
        }
        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param $string
     * @param $fromEncoding
     * @param $toEncoding
     *
     * @return string|null
     */
    private function convertWithMultiByteString($string, $fromEncoding, $toEncoding)
    {
        // @codeCoverageIgnoreStart
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $toEncoding, $fromEncoding);
        }
        return null;
        // @codeCoverageIgnoreEnd
    }
}
