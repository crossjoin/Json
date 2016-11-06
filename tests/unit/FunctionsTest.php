<?php
namespace Crossjoin\Json\Tests\Unit;

/**
 * Class FunctionsTest
 *
 * @package Crossjoin\Json\Tests\Unit
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testValidJsonEncode()
    {
        $result = \Crossjoin\Json\json_encode(array());
        static::assertEquals('[]', $result);
    }

    /**
     * @requires PHP 5.5
     */
    public function testInvalidJsonEncode()
    {
        $result = \Crossjoin\Json\json_encode(fopen('php://memory', 'r'));
        static::assertFalse($result);
    }

    public function testValidJsonDecode()
    {
        $result = \Crossjoin\Json\json_decode('[]');
        static::assertEquals(array(), $result);
    }

    public function testInvalidJsonDecode()
    {
        $result = \Crossjoin\Json\json_decode('{]');
        static::assertNull($result);
    }

    public function testNoError()
    {
        \Crossjoin\Json\json_decode('[]');
        $code = \Crossjoin\Json\json_last_error();

        static::assertEquals(0, $code);
    }

    public function testError()
    {
        \Crossjoin\Json\json_decode('{]');
        $code = \Crossjoin\Json\json_last_error();
        $message = \Crossjoin\Json\json_last_error_msg();

        static::assertGreaterThan(0, $code);
        static::assertInternalType('string', $message);
        static::assertNotEquals('', $message);
    }
}