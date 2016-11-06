<?php
namespace Crossjoin\Json\Tests\Unit;

use Crossjoin\Json\Decoder;

/**
 * Class DecoderTest
 *
 * @package Crossjoin\Json\Tests\Unit
 * @author Christoph Ziegenberg <ziegenberg@crossjoin.com>
 *
 * @coversDefaultClass \Crossjoin\Json\Decoder
 */
class DecoderTest extends \PHPUnit_Framework_TestCase
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
    public function dataDecodeValidDataWithoutBom()
    {
        $data = array();

        foreach (
            array(Decoder::UTF8, Decoder::UTF16BE, Decoder::UTF16LE, Decoder::UTF32BE, Decoder::UTF32LE) as $encoding
        ) {
            foreach ($this->getJsonOptions() as $option) {
                foreach ($this->getValues() as $value) {
                    $json = \json_encode($value, $option);
                    if ($encoding !== Decoder::UTF8) {
                        $json = iconv('UTF-8', $encoding . '//IGNORE', $json);
                    }

                    if ($option === \JSON_FORCE_OBJECT && is_array($value)) {
                        $value = (object)$value;
                    }

                    $data[] = array($json, $value, $encoding);
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function dataDecodeValidDataWithBom()
    {
        $data = $this->dataDecodeValidDataWithoutBom();

        foreach ($data as &$value) {
            $bom = '';
            switch ($value[2]) {
                case Decoder::UTF8;
                    $bom = chr(239) . chr(187) . chr(191);
                    break;
                case Decoder::UTF16BE;
                    $bom = chr(254) . chr(255);
                    break;
                case Decoder::UTF16LE;
                    $bom = chr(255) . chr(254);
                    break;
                case Decoder::UTF32BE;
                    $bom = chr(0) . chr(0) . chr(254) . chr(255);
                    break;
                case Decoder::UTF32LE;
                    $bom = chr(255) . chr(254) . chr(0) . chr(0);
                    break;
            }

            if ($bom !== '') {
                $value[0] = $bom . $value[0];
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function dataNoBooleanTypes()
    {
        return array(
            array(1),
            array(1.23),
            array('string'),
            array(array('foo')),
            array(new \stdClass()),
            array(fopen('php://memory', 'r'))
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
    public function dataInvalidJson()
    {
        $data = array(array('{]'));

        // The following examples are now handled as invalid in PHP < 7.0.0
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $data[] = array('');
            $data[] = array('"?\udc4d"');
            $data[] = array('"\ud83d?"');
        }

        return $data;
    }

    /**
     * @covers ::__construct
     * @covers ::setIgnoreByteOrderMark
     * @covers ::getIgnoreByteOrderMark
     */
    public function testIgnoreValidByteOrderMarkValues()
    {
        $decoder = new Decoder();
        static::assertTrue($decoder->getIgnoreByteOrderMark());

        $decoder = new Decoder(true);
        static::assertTrue($decoder->getIgnoreByteOrderMark());

        $decoder = new Decoder(false);
        static::assertFalse($decoder->getIgnoreByteOrderMark());
    }

    /**
     * @param mixed $value
     *
     * @dataProvider dataNoBooleanTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478195542
     * @covers ::setIgnoreByteOrderMark
     */
    public function testIgnoreInvalidByteOrderMarkValues($value)
    {
        new Decoder($value);
    }

    /**
     * @param string $json
     *
     * @dataProvider dataNoStringTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478195652
     * @covers ::getEncoding
     */
    public function testEncodingValueOfInvalidType($json)
    {
        $decoder = new Decoder();
        $decoder->getEncoding($json);
    }

    /**
     * @param string $json
     * @param mixed $expectedData
     * @param string $expectedEncoding
     *
     * @dataProvider dataDecodeValidDataWithoutBom
     *
     * @covers ::decode
     * @covers ::getEncoding
     */
    public function testDecodeValidDataWithoutBom($json, $expectedData, $expectedEncoding)
    {
        $decoder = new Decoder(false);
        static::assertEquals($expectedEncoding, $decoder->getEncoding($json));
        static::assertEquals($expectedData, $decoder->decode($json));
    }

    /**
     * @param string $json
     * @param mixed $expectedData
     * @param string $expectedEncoding
     *
     * @dataProvider dataDecodeValidDataWithBom
     *
     * @covers ::decode
     * @covers ::getEncoding
     */
    public function testDecodingValidDataWithIgnoredBom($json, $expectedData, $expectedEncoding)
    {
        $decoder = new Decoder(true);
        static::assertEquals($expectedEncoding, $decoder->getEncoding($json));
        static::assertEquals($expectedData, $decoder->decode($json));
    }

    /**
     * @param string $json
     * @param string $expectedData
     *
     * @dataProvider dataDecodeValidDataWithBom
     *
     * @expectedException \Crossjoin\Json\Exception\EncodingNotSupportedException
     * @expectedExceptionCode 1478092834
     * @covers ::decode
     * @covers ::getEncoding
     */
    public function testDecodingValidDataWithPreservedBom($json, $expectedData, $expectedEncoding)
    {
        $decoder = new Decoder(false);
        $decoder->getEncoding($json);
    }

    /**
     * @param string $json
     *
     * @dataProvider dataInvalidJson
     *
     * @expectedException \Crossjoin\Json\Exception\NativeJsonErrorException
     * @covers ::decode
     */
    public function testDecodeInvalidJson($json)
    {
        $decoder = new Decoder(false);
        $decoder->decode($json);
    }

    /**
     * @param mixed $json
     *
     * @dataProvider dataNoStringTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478418105
     * @covers ::decode
     */
    public function testDecodeArgumentInvalidJson($json)
    {
        $decoder = new Decoder();
        $decoder->decode($json);
    }

    /**
     * @param mixed $assoc
     *
     * @dataProvider dataNoBooleanTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478418106
     * @covers ::decode
     */
    public function testDecodeArgumentInvalidAssoc($assoc)
    {
        $decoder = new Decoder();
        $decoder->decode('"string"', $assoc);
    }

    /**
     * @param mixed $depth
     *
     * @dataProvider dataNoIntegerTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478418107
     * @covers ::decode
     */
    public function testDecodeArgumentInvalidDepth($depth)
    {
        $decoder = new Decoder();
        $decoder->decode('"string"', false, $depth);
    }

    /**
     * @param mixed $options
     *
     * @dataProvider dataNoIntegerTypes
     *
     * @expectedException \Crossjoin\Json\Exception\InvalidArgumentException
     * @expectedExceptionCode 1478418108
     * @covers ::decode
     */
    public function testDecodeArgumentInvalidOptions($options)
    {
        $decoder = new Decoder();
        $decoder->decode('"string"', false, 512, $options);
    }
}
