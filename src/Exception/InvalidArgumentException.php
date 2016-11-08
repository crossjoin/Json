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
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param string $expectedType
     * @param string $argumentName
     * @param mixed $currentValue
     * @param int $code
     *
     * @return InvalidArgumentException
     */
    public static function getInstance($expectedType, $argumentName, $currentValue, $code = 0)
    {
        return new self(
            sprintf(
                "%s expected for argument '%s'. Got '%s'.",
                ucfirst(strtolower($expectedType)),
                $argumentName,
                gettype($currentValue)
            ),
            $code
        );
    }
}
