<?php

namespace Kduma\SignedDocument\Tests;

use Kduma\SignedDocument\Document;
use Kduma\SignedDocument\DocumentSigner;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
use PHPUnit\Framework\TestCase;

class DocumentSignerTest extends TestCase
{
    const SECRET_KEY = '31400400b8ea9e71ac2a13ef2afb786d2ecb01657804dbd61003a6e2ea504bf42c735b47e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf065ff7c9f2da34541a461ae3058850a642c614c21b9fa0f76db2f0f2dfe7e4de91e2ef16d51b077d677a445db093bc6bff23981485ed2d13f0a4ed8128e68538';
    const PUBLIC_KEY   = '31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71';

    const UNSIGNED_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="lipsum.txt" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
</document>

XML;
    const SIGNED_XML   = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="lipsum.txt" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <content encoding="raw" sha256="5bd6045a7697c48316411ff00be02595cf3d8596d99ba12482d18c90d61633cb"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipiscing elit...]]></content>
  <signature id="signature-id" public-key="31400400e81832336bfad7c6495e7e1698a9e844746dc0278ed55af3e9a9574214a78fdf6953374f0524099019df1e69ca22c0a74ef7b83ba3ccd5bd5f922ff8fc4d6539b559b1a13a2ad6a3e27ff70689640b29b13f351391b15ea8c453d48ad999ff71">e7dca247652cae046d0f765f1d1a0cb3aff15b987f4de5103e4d61ca67d03b1aca55b616ed58ca6fc7902c76d8b6ce082d75838aea60c82540744fae1bf31c02</signature>
</document>

XML;
    const SIGNATURE    = 'e7dca247652cae046d0f765f1d1a0cb3aff15b987f4de5103e4d61ca67d03b1aca55b616ed58ca6fc7902c76d8b6ce082d75838aea60c82540744fae1bf31c02';

    /**
     * @covers \Kduma\SignedDocument\DocumentSigner::__construct
     * @covers \Kduma\SignedDocument\DocumentSigner::sign
     * @covers \Kduma\SignedDocument\Signature::__construct
     * @covers \Kduma\SignedDocument\Signature::getSignature
     * @covers \Kduma\SignedDocument\Signature::getPublicKey
     * @covers \Kduma\SignedDocument\Signature::getId
     */
    public function testSign()
    {
        $signer = new DocumentSigner(
            KeyFactory::importSignatureSecretKey(
                new HiddenString(
                    self::SECRET_KEY
                )
            )
        );

        $document = Document::fromXml(self::UNSIGNED_XML);
        $signature = $signer->sign($document, 'signature-id');
        $document->addSignature($signature);

        $this->assertEquals(self::SIGNED_XML, $document->getXml());
        $this->assertEquals(self::SIGNATURE, $signature->getSignature());
        $this->assertEquals(self::PUBLIC_KEY, $signature->getPublicKey());
        $this->assertEquals('signature-id', $signature->getId());
    }
}
