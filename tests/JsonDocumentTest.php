<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUtils\Json\JsonDocument;

final class JsonDocumentTest extends TestCase
{
    public function testFromStringAndToString(): void
    {
        $string = '{"foo": {"bar": "baz", "qux": ["quux", "quuz", "corge"]}}';
        $doc = JsonDocument::fromString($string);
        $this->assertIsObject($doc);
        $this->assertInstanceOf(JsonDocument::class, $doc);
        $out = (string) $doc;
        $this->assertJsonStringEqualsJsonString($out, $string);
    }

    public function testSetAndGetOptions()
    {
        $doc = JsonDocument::fromString('{}');

        $option1 = 1;
        $option2 = 2;
        $option16 = 16;

        $this->assertSame($doc->getOptions(), 0);

        $doc->setOption($option1);
        $this->assertSame($doc->getOptions(), $option1);

        $doc->setOption($option2);
        $this->assertSame($doc->getOptions(), $option1 + $option2);

        $doc->setOptions($option2 + $option16);
        $this->assertSame($doc->getOptions(), $option16 + $option2);

        $doc->unsetOptions($option2)->unsetOptions($option16);
        $this->assertSame($doc->getOptions(), 0);

        $doc->setOption($option1)->setOption($option16);
        $this->assertSame($doc->getOptions(), $option16 + $option1);
    }

    public function testArrayAccess()
    {
        $doc = JsonDocument::fromString('{"foo": {"bar": "baz", "qux": ["quux", "quuz", "corge"]}}');

        $this->assertSame($doc['foo.bar'], 'baz');
        $this->assertSame($doc['foo.bar.whatever'], null);

        $this->assertTrue(isset($doc['foo.bar']));
        $this->assertFalse(isset($doc['foo.bar.whatever']));

        $doc['foo.hello'] = 'world';
        $this->assertSame($doc['foo.hello'], 'world');

        unset($doc['foo.bar']);
        $this->assertSame($doc['foo.bar'], null);
        $this->assertFalse(isset($doc['foo.bar']));
    }

    public function testMagicAccessors()
    {
        $doc = JsonDocument::fromString('{"foo": {"bar": "baz", "qux": ["quux", "quuz", "corge"]}}');

        $this->assertSame($doc->foo->bar, "baz");
        $this->assertSame($doc->{"foo.bar"}, "baz");
        $this->assertTrue(isset($doc->{"foo.bar"}));
        $this->assertFalse(isset($doc->{"foo.bar.whatever"}));
        unset($doc->{"foo.bar"});
        $this->assertFalse(isset($doc->{"foo.bar"}));
    }

    public function testTraditionalAccessories()
    {
        $doc = JsonDocument::fromString('{"foo": {"bar": "baz", "qux": ["quux", "quuz", "corge"]}}');

        $this->assertSame($doc->get('foo.bar'), "baz");
        $this->assertTrue($doc->exists("foo.bar"));
        $this->assertFalse($doc->exists("foo.bar.whatever"));
        $doc->remove("foo.bar");
        $this->assertFalse($doc->exists("foo.bar"));
    }

    public function testCombine()
    {
        $doc = JsonDocument::fromString('{"foo": "bar"}');
        $source = json_decode('{"baz": "qux"}');

        $doc->combine($source);

        $this->assertSame($doc->foo, "bar");
        $this->assertSame($doc->baz, "qux");
    }
}
