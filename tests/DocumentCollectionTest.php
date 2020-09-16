<?php

namespace Kduma\SignedDocument\Tests;

use Kduma\SignedDocument\Document;
use Kduma\SignedDocument\DocumentCollection;
use PHPUnit\Framework\TestCase;

class DocumentCollectionTest extends TestCase
{
    const COLLECTION_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"?>
<documents xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="doc-collection" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd"><document><content encoding="raw" sha256="5f9546d180f0522d5276323dc3e8b7a0fa3e1b9e26d8da2b00b970dd8f9d6ecb"><![CDATA[Document 1]]></content></document><document><content encoding="raw" sha256="2a6cbdf0227d63c28441d4c0c21336c3176223b29686d6501a041fe4af125354"><![CDATA[Document 2]]></content></document></documents>

XML;


    /**
     * @covers \Kduma\SignedDocument\DocumentCollection::getXml
     * @covers \Kduma\SignedDocument\DocumentCollection::__toString
     * @covers \Kduma\SignedDocument\DocumentCollection::addDocument
     * @covers \Kduma\SignedDocument\DocumentCollection::setId
     */
    public function testCollectionToXml()
    {
        $collection = new DocumentCollection();
        $collection->addDocument(Document::make('Document 1'));
        $collection->addDocument(Document::make('Document 2'));
        $collection->setId('doc-collection');
        $this->assertEquals(self::COLLECTION_XML, (string) $collection);
    }

    /**
     * @covers \Kduma\SignedDocument\DocumentCollection::fromXml
     * @covers \Kduma\SignedDocument\DocumentCollection::getDocuments
     * @covers \Kduma\SignedDocument\DocumentCollection::__construct
     */
    public function testCollectionFromXml()
    {
        $collection = DocumentCollection::fromXml(self::COLLECTION_XML);
        $this->assertEquals('doc-collection', $collection->getId());
        $this->assertEquals([
            Document::make('Document 1'),
            Document::make('Document 2'),
        ], $collection->getDocuments());
    }
}
