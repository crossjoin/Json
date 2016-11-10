<?php
namespace Crossjoin\Json\Exception;

/**
 * Class InvalidArgumentException
 *
 * @package Crossjoin\Json\Exception
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 */
class InvalidArgumentException extends \InvalidArgumentException implements JsonException
{
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING  = 'string';
    const TYPE_BOOLEAN = 'boolean';

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param $expectedType
     * @param $argumentName
     * @param $currentValue
     * @param $code
     *
     * @return bool
     * @throws \Crossjoin\Json\Exception\InvalidArgumentException
     */
    public static function validateArgument($expectedType, $argumentName, $currentValue, $code)
    {
        if (gettype($currentValue) !== $expectedType) {
            throw new self(
                sprintf(
                    "%s expected for argument '%s'. Got '%s'.",
                    ucfirst(strtolower($expectedType)),
                    $argumentName,
                    gettype($currentValue)
                ),
                $code
            );
        }

        return true;
    }
}
