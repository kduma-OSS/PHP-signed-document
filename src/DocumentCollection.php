<?php


namespace Kduma\SignedDocument;


use DOMDocument;
use DOMElement;

class DocumentCollection
{
    /**
     * @var array|Document[]
     */
    protected array $documents = [];

    public static function fromXml(string $xml): DocumentCollection
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        if (!@$dom->schemaValidate(__DIR__ . '/../schema/signed-document.xsd')) {
            throw new \Exception('Invalid XML schema!');
        }

        $collection = new DocumentCollection();
        foreach ($dom->getElementsByTagNameNS(Document::NAMESPACE_URI, "document") as $document) {
            $collection->addDocument(
                Document::fromDom($document)
            );
        }

        return $collection;
    }

    /**
     * @return array|Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Document $document
     *
     * @return DocumentCollection
     */
    public function addDocument(Document $document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * @param bool $formatOutput
     *
     * @return false|string
     * @throws \Exception
     */
    public function getXml(bool $formatOutput = true)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $formatOutput;

        $documents = $dom->createElementNS(Document::NAMESPACE_URI, "documents");
        $dom->appendChild($documents);

        $schemaLocationAttribute = $dom->createAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "xsi:schemaLocation");
        $schemaLocationAttribute->value = Document::NAMESPACE_URI.' https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd';
        $documents->appendChild($schemaLocationAttribute);

        foreach ($this->documents as $document) {
            $document_dom = $dom->createElementNS(Document::NAMESPACE_URI, "document");
            $documents->appendChild($document_dom);
            $document->getDom($dom, $document_dom, $formatOutput);
        }

        return $dom->saveXML();
    }

    public function __toString()
    {
        return $this->getXml(false);
    }
}
