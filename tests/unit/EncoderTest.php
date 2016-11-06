<?php
namespace Crossjoin\Json\Tests\Unit;

use Crossjoin\Json\Decoder;
use Crossjoin\Json\Encoder;

/**
 * Class EncoderTest
 *
 * @package Crossjoin\Json\Tests\Unit
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 *
 * @coversDefaultClass \Crossjoin\Json\Encoder
 */
class EncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getJsonOptions()
    {
        $options = array(0);
        $options[] = \JSON_HEX_QUOT;
        $options[] = \JSON_HEX_TAG;
        $options[] = \JSON_HEX_AMP;
        $options[] = \JSON_HEX_APOS;
        $options[] = \JSON_NUMERIC_CHECK;
        $options[] = \JSON_FORCE_OBJECT;
        if (defined('\JSON_PRETTY_PRINT')) {
            $options[] = \JSON_PRETTY_PRINT;
        }
        if (defined('\JSON_UNESCAPED_SLASHES')) {
            $options[] = \JSON_UNESCAPED_SLASHES;
        }
        if (defined('\JSON_UNESCAPED_UNICODE')) {
            $options[] = \JSON_UNESCAPED_UNICODE;
        }
        if (defined('\JSON_PARTIAL_OUTPUT_ON_ERROR')) {
            $options[] = \JSON_PARTIAL_OUTPUT_ON_ERROR;
        }
        if (defined('\JSON_PRESERVE_ZERO_FRACTION')) {
            $options[] = \JSON_PRESERVE_ZERO_FRACTION;
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values   = array();
        $values[] = '';
        $values[] = null;
        $values[] = 1;
        $values[] = 1.23;
        $values[] = true;
        $values[] = array();
        $values[] = new \stdClass();
        $values[] = 'foo';
        $values[] = 'äöüßÄÖÜ';
        $values[] = '👍';

        $ascii = '';
        for ($i = 0; $i < 128; $i++) {
            $ascii .= chr($i);
        }
        $values[] = $ascii;

        return $values;
    }

    /**
     * @return array
     */
    public function dataValidEncodingValues()
    {
        return array(
            array(Encoder::UTF8),
            array(Encoder::UTF16),
            array(Encoder::UTF16LE),
            array(Encoder::UTF16BE),
            array(Encoder::UTF32),
            array(Encoder::UTF32BE),
            array(Encoder::UTF32LE),
        );
    }

    /**
     * @return array
     */
    public function dataNoStringTypes()
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
     * @return array
     */
    public function dataNoIntegerTypes()
    {
        return array(
            array('string'),
            array(1.23),
            array(true),
            array(array('foo')),
            array(new \stdClass()),
            array(fopen('php://memory', 'r'))
        );
    }
    
    /**
     * @return array
     */
    public function dataEncodingValidData()
    {
        $data = array();
        $options = $this->getJsonOptions();
        $values = $this->getValues();

        foreach (
            array(Encoder::UTF8, Encoder::UTF16BE, Encoder::UTF16LE, Encoder::UTF32BE, Encoder::UTF32LE) as $encoding
        ) {
            foreach ($options as $option) {
                foreach ($values as $value) {
                    $data[] = array($value, $option, $encoding);
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function dataEncodingInvalidData()
    {
        return array(
            array(fopen('php://memory', 'r')),
        );
    }

    /**
     * @param string $encoding
     *
     * @dataProvider dataValidEncodingValues
     *
     * @covers ::__construct
     * @covers ::setEncoding
     * @covers ::getEncoding
     */
    public function testValidEncodingValues($encoding)
    {
        $encoder = new Encoder($encoding);
        static::assertSame($encoding, $encoder->getEncoding());
    }

    /**
     * @expectedException \Crossjoin\Json\Exception\EncodingNotSupportedException
     * @expectedExceptionCode 1478101930
     */
    public function testInvalidEncodingValue()
    {
        new Encoder('ISO-8859-1');
    }

    /**
     * @param mixed $value
     *
     * @dataProvider dataNoStringTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478196374
     * @covers ::setEncoding
     */
    public function testInvalidEncodingTypes($value)
    {
        new Encoder($value);
    }

    public function testEndiannessDefaults()
    {
        static::assertSame(Encoder::UTF16BE, Encoder::UTF16);
        static::assertSame(Encoder::UTF32BE, Encoder::UTF32);
    }

    /**
     * @param mixed $value
     * @param int $options
     * @param string $encoding
     *
     * @dataProvider dataEncodingValidData
     *
     * @covers ::encode
     */
    public function testEncodingValidData($value, $options, $encoding)
    {
        $encoder = new Encoder($encoding);
        $json = $encoder->encode($value, $options);

        $decoder = new Decoder();
        static::assertSame($encoding, $decoder->getEncoding($json));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider dataEncodingInvalidData
     *
     * @expectedException \Crossjoin\Json\Exception\NativeJsonErrorException
     * @covers ::encode
     * @requires PHP 5.5
     */
    public function testEncodingInvalidData($value)
    {
        $encoder = new Encoder();
        $encoder->encode($value);
    }

    /**
     * @param mixed $options
     *
     * @dataProvider dataNoIntegerTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478418109
     * @covers ::encode
     */
    public function testEncodingInvalidOptions($options)
    {
        $encoder = new Encoder();
        $encoder->encode('string', $options);
    }

    /**
     * @param mixed $depth
     *
     * @dataProvider dataNoIntegerTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478418110
     * @covers ::encode
     */
    public function testEncodingInvalidDepth($depth)
    {
        $encoder = new Encoder();
        $encoder->encode('string', 0, $depth);
    }
}
