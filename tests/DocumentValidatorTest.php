<?php

namespace Kduma\SignedDocument\Tests;

use Kduma\SignedDocument\Document;
use Kduma\SignedDocument\DocumentCollection;
use Kduma\SignedDocument\DocumentValidator;
use PHPUnit\Framework\TestCase;

class DocumentValidatorTest extends TestCase
{
    const VALID_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="lipsum.txt" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
  <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">e7dca247652cae046d0f765f1d1a0cb3aff15b987f4de5103e4d61ca67d03b1aca55b616ed58ca6fc7902c76d8b6ce082d75838aea60c82540744fae1bf31c02</signature>
</document>
XML;
    const INVALID_CHECKSUM_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="lipsum.txt" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="0000000000000000000000000000000000000000000000000000000000000000"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
  <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">e7dca247652cae046d0f765f1d1a0cb3aff15b987f4de5103e4d61ca67d03b1aca55b616ed58ca6fc7902c76d8b6ce082d75838aea60c82540744fae1bf31c02</signature>
</document>
XML;

    const INVALID_SIGNATURE_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="lipsum.txt" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
  <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000</signature>
</document>
XML;

    const INVALID_SIGNATURE_AND_CHECKSUM_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="lipsum.txt" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="0000000000000000000000000000000000000000000000000000000000000000"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
  <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000</signature>
</document>
XML;

    const VALID_COLLECTION_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"?>
<documents xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <document>
    <content encoding="raw" sha256="5f9546d180f0522d5276323dc3e8b7a0fa3e1b9e26d8da2b00b970dd8f9d6ecb"><![CDATA[Document 1]]></content>
    <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">f5b5f6e9c5f139e52ce0222fc757c411c64f19a7c53e7c8f51a56e899edd06297a47bd7b70a9b5cb38a2fd3d5b7da16be69fb4015c347bc1925a9325fddc0908</signature>
  </document>
  <document>
    <content encoding="raw" sha256="2a6cbdf0227d63c28441d4c0c21336c3176223b29686d6501a041fe4af125354"><![CDATA[Document 2]]></content>
    <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">b63c5a4f53b3250fdb5966adbd35f503c13ac536b1b5fee1a5bc387e25eac9dd688dca66a5a7df9e3c9d7103e6133c98750d387e7abaebc560da226f9448e70b</signature>
  </document>
</documents>
XML;

    const INVALID_COLLECTION_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"?>
<documents xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <document>
    <content encoding="raw" sha256="0000000000000000000000000000000000000000000000000000000000000000"><![CDATA[Document 1]]></content>
    <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">f5b5f6e9c5f139e52ce0222fc757c411c64f19a7c53e7c8f51a56e899edd06297a47bd7b70a9b5cb38a2fd3d5b7da16be69fb4015c347bc1925a9325fddc0908</signature>
  </document>
  <document id="second">
    <content encoding="raw" sha256="2a6cbdf0227d63c28441d4c0c21336c3176223b29686d6501a041fe4af125354"><![CDATA[Document 2]]></content>
    <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000</signature>
  </document>
</documents>
XML;

    /**
     * @covers \Kduma\SignedDocument\DocumentValidator::validate
     */
    public function testValidate()
    {
        $validator = new DocumentValidator();
        $document = Document::fromXml(self::VALID_XML);

        $this->assertEquals(true, $validator->validate($document));
    }

    /**
     * @covers \Kduma\SignedDocument\DocumentValidator::validate
     */
    public function testValidateWithInvalidChecksum()
    {
        $validator = new DocumentValidator();
        $document = Document::fromXml(self::INVALID_CHECKSUM_XML);

        $this->expectExceptionObject(new \Exception('Provided sha256 checksum is invalid!'));

        $validator->validate($document);
    }

    /**
     * @covers \Kduma\SignedDocument\DocumentValidator::validate
     */
    public function testValidateWithInvalidSignature()
    {
        $validator = new DocumentValidator();
        $document = Document::fromXml(self::INVALID_SIGNATURE_XML);

        $this->expectExceptionObject(new \Exception('Signature with id of signature-id is invalid!'));

        $validator->validate($document);
    }

    /**
     * @covers \Kduma\SignedDocument\DocumentValidator::validate
     */
    public function testValidateWithInvalidSignatureAndChecksum()
    {
        $validator = new DocumentValidator();
        $document = Document::fromXml(self::INVALID_SIGNATURE_AND_CHECKSUM_XML);

        $this->expectExceptionObject(new \Exception('Provided sha256 checksum is invalid!, Signature with id of signature-id is invalid!'));

        $validator->validate($document);
    }

    /**
     * @covers \Kduma\SignedDocument\DocumentValidator::validateCollection
     */
    public function testValidateCollection()
    {
        $validator = new DocumentValidator();
        $documents = DocumentCollection::fromXml(self::VALID_COLLECTION_XML);

        $this->assertEquals(true, $validator->validateCollection($documents));
    }

    /**
     * @covers \Kduma\SignedDocument\DocumentValidator::validateCollection
     */
    public function testValidateCollectionInvalid()
    {
        $validator = new DocumentValidator();
        $documents = DocumentCollection::fromXml(self::INVALID_COLLECTION_XML);

        $this->expectExceptionObject(new \Exception('#0 -> Provided sha256 checksum is invalid!, second -> Signature with id of signature-id is invalid!'));
        $validator->validateCollection($documents);
    }
}
