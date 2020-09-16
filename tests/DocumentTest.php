<?php

namespace Kduma\SignedDocument\Tests;

use Kduma\SignedDocument\Document;
use Kduma\SignedDocument\Signature;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    const LIPSUM       = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...';
    const LIPSUM_SHA   = '5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb';

    const INVALID_XML                 = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <body>qwerty</body>
</document>

XML;

    const UNSIGNED_XML                 = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
</document>

XML;
    const UNSIGNED_BIN_XML             = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="base64" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb"><![CDATA[
TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQsIGNvbnNlY3RldHVyIGFkaXBpc2Np
bmcgZWxpdC4uLg==
]]></content>
</document>

XML;
    const SIGNED_XML                    = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="lipsum.txt" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
  <signature id="signature-id" public-key="public-key">signature</signature>
  <signature public-key="second-public-key">second-signature-without-id</signature>
</document>

XML;
    const UNSIGNED_NONFORMATTED_BIN_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd"><content encoding="base64" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb">TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQsIGNvbnNlY3RldHVyIGFkaXBpc2NpbmcgZWxpdC4uLg==</content></document>

XML;



    /**
     * @covers \Kduma\SignedDocument\Document::make
     * @covers \Kduma\SignedDocument\Document::__construct
     * @covers \Kduma\SignedDocument\Document::getId
     * @covers \Kduma\SignedDocument\Document::getSha256
     * @covers \Kduma\SignedDocument\Document::getContent
     * @covers \Kduma\SignedDocument\Document::getEncoding
     * @covers \Kduma\SignedDocument\Document::getSignatures
     */
    public function testMake()
    {
        $document = Document::make(self::LIPSUM);
        $this->assertEquals(self::LIPSUM, $document->getContent());
        $this->assertEquals(self::LIPSUM_SHA, $document->getSha256());
        $this->assertEquals(null, $document->getId());
        $this->assertEquals('raw', $document->getEncoding());
        $this->assertEquals([], $document->getSignatures());
    }

    /**
     * @covers \Kduma\SignedDocument\Document::makeBin
     * @covers \Kduma\SignedDocument\Document::__construct
     * @covers \Kduma\SignedDocument\Document::getId
     * @covers \Kduma\SignedDocument\Document::getSha256
     * @covers \Kduma\SignedDocument\Document::getContent
     * @covers \Kduma\SignedDocument\Document::getEncoding
     * @covers \Kduma\SignedDocument\Document::getSignatures
     */
    public function testMakeBin()
    {
        $document = Document::makeBin(self::LIPSUM);
        $this->assertEquals(self::LIPSUM, $document->getContent());
        $this->assertEquals(self::LIPSUM_SHA, $document->getSha256());
        $this->assertEquals(null, $document->getId());
        $this->assertEquals('base64', $document->getEncoding());
        $this->assertEquals([], $document->getSignatures());
    }

    /**
     * @covers \Kduma\SignedDocument\Document::getXml
     * @covers \Kduma\SignedDocument\Document::getDom
     */
    public function testGetXmlForUnsignedRaw()
    {
        $document = Document::make(self::LIPSUM);
        $this->assertEquals(self::UNSIGNED_XML, $document->getXml());
    }

    /**
     * @covers \Kduma\SignedDocument\Document::getXml
     * @covers \Kduma\SignedDocument\Document::getDom
     */
    public function testGetXmlForUnsignedBinary()
    {
        $document = Document::makeBin(self::LIPSUM);
        $this->assertEquals(self::UNSIGNED_BIN_XML, $document->getXml());
    }

    /**
     * @covers \Kduma\SignedDocument\Document::getXml
     * @covers \Kduma\SignedDocument\Document::getDom
     * @covers \Kduma\SignedDocument\Document::__toString
     */
    public function testGetXmlForUnsignedBinaryWithoutFormatting()
    {
        $document = Document::makeBin(self::LIPSUM);
        $this->assertEquals(self::UNSIGNED_NONFORMATTED_BIN_XML, (string) $document);
    }

    /**
     * @covers \Kduma\SignedDocument\Document::getXml
     * @covers \Kduma\SignedDocument\Document::getDom
     * @covers \Kduma\SignedDocument\Document::addSignature
     * @covers \Kduma\SignedDocument\Document::setId
     */
    public function testGetXmlForSignedRawWithId()
    {
        $document = Document::make(self::LIPSUM);
        $document->setId('lipsum.txt');
        $document->addSignature(new Signature('public-key', 'signature', 'signature-id'));
        $document->addSignature(new Signature('second-public-key', 'second-signature-without-id'));
        $this->assertEquals(self::SIGNED_XML, $document->getXml());
    }

    /**
     * @covers \Kduma\SignedDocument\Document::fromXml
     * @covers \Kduma\SignedDocument\Document::fromDom
     */
    public function testFromXmlRaw()
    {
        $document = Document::fromXml(self::SIGNED_XML);
        $this->assertEquals(self::LIPSUM, $document->getContent());
        $this->assertEquals(self::LIPSUM_SHA, $document->getSha256());
        $this->assertEquals('lipsum.txt', $document->getId());
        $this->assertEquals('raw', $document->getEncoding());
        $this->assertEquals([
            new Signature('public-key', 'signature', 'signature-id'),
            new Signature('second-public-key', 'second-signature-without-id')
        ], $document->getSignatures());
    }

    /**
     * @covers \Kduma\SignedDocument\Document::fromXml
     * @covers \Kduma\SignedDocument\Document::fromDom
     */
    public function testFromXmlBinary()
    {
        $document = Document::fromXml(self::UNSIGNED_BIN_XML);
        $this->assertEquals(self::LIPSUM, $document->getContent());
        $this->assertEquals(self::LIPSUM_SHA, $document->getSha256());
        $this->assertEquals(null, $document->getId());
        $this->assertEquals('base64', $document->getEncoding());
        $this->assertEquals([], $document->getSignatures());
    }

    /**
     * @covers \Kduma\SignedDocument\Document::fromXml
     * @covers \Kduma\SignedDocument\Document::fromDom
     */
    public function testFromXmlInvalid()
    {
        $this->expectExceptionObject(new \Exception('Invalid XML schema!'));

        Document::fromXml(self::INVALID_XML);
    }
}
