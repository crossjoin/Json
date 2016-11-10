<?php
namespace Crossjoin\Json\Tests\Unit;

use Crossjoin\Json\Decoder;

/**
 * Class ConverterTest
 *
 * @package Crossjoin\Json\Tests\Unit
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 *
 * @coversDefaultClass \Crossjoin\Json\Converter
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    public function dataValidRemoveByteOrderMarkValues()
    {
        return array(
            array(chr(239) . chr(187) . chr(191) . 'UTF-8', 'UTF-8'),
            array(chr(254) . chr(255) . 'UTF-16BE', 'UTF-16BE'),
            array(chr(255) . chr(254) . 'UTF-16LE', 'UTF-16LE'),
            array(chr(0) . chr(0) . chr(254) . chr(255) . 'UTF-32BE', 'UTF-32BE'),
            array(chr(255) . chr(254) . chr(0) . chr(0) . 'UTF-32LE', 'UTF-32LE'),
        );
    }

    public function dataInvalidRemoveByteOrderMarkValues()
    {
        return array(
            array(1),
            array(1.23),
            array(true),
            array(array('foo')),
            array(new \stdClass()),
            array(fopen('php://memory', 'r'))
        );
    }

    /**
     * @param string $value
     * @param string $expectedResult
     *
     * @dataProvider dataValidRemoveByteOrderMarkValues
     *
     * @covers ::removeByteOrderMark
     */
    public function testValidRemoveByteOrderMarkValues($value, $expectedResult)
    {
        $decoder = new Decoder();
        $result = $decoder->removeByteOrderMark($value);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * @param string $value
     *
     * @dataProvider dataInvalidRemoveByteOrderMarkValues
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478195910
     * @covers ::removeByteOrderMark
     */
    public function testInvalidRemoveByteOrderMarkValues($value)
    {
        $decoder = new Decoder();
        $decoder->removeByteOrderMark($value);
    }

    /**
     * @param string $value
     *
     * @dataProvider dataInvalidRemoveByteOrderMarkValues
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478195990
     * @covers ::convertEncoding
     * @covers ::tryConvertEncoding
     */
    public function testInvalidConvertEncodingStringValue($value)
    {
        $decoder = new Decoder();
        $decoder->convertEncoding($value, 'UTF-8', 'UTF-16BE');
    }

    /**
     * @param string $value
     *
     * @dataProvider dataInvalidRemoveByteOrderMarkValues
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478195991
     * @covers ::convertEncoding
     * @covers ::tryConvertEncoding
     */
    public function testInvalidConvertEncodingFromEncodingValue($value)
    {
        $decoder = new Decoder();
        $decoder->convertEncoding('string', $value, 'UTF-16BE');
    }

    /**
     * @param string $value
     *
     * @dataProvider dataInvalidRemoveByteOrderMarkValues
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478195992
     * @covers ::convertEncoding
     * @covers ::tryConvertEncoding
     */
    public function testInvalidConvertEncodingToEncodingValue($value)
    {
        $decoder = new Decoder();
        $decoder->convertEncoding('string', 'UTF-8', $value);
    }
}
