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
    protected ?string $id = null;

    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public static function fromXml(string $xml): DocumentCollection
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        if (!@$dom->schemaValidate(__DIR__ . '/../schema/signed-document.xsd')) {
            throw new \Exception('Invalid XML schema!');
        }

        $collection = new DocumentCollection();

        $documents = $dom->getElementsByTagNameNS(Document::NAMESPACE_URI, "documents")->item(0);

        $collectionId = $documents->attributes->getNamedItem("id");
        $collection->id = $collectionId ? $collectionId->nodeValue : null;

        foreach ($documents->getElementsByTagNameNS(Document::NAMESPACE_URI, "document") as $document) {
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
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return DocumentCollection
     */
    public function setId(?string $id): DocumentCollection
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param bool        $formatOutput
     *
     * @param string|null $xslUrl
     *
     * @return false|string
     * @throws \Exception
     */
    public function getXml(bool $formatOutput = true, ?string $xslUrl = Document::XSL_URL)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $formatOutput;

        if($xslUrl) {
            $xslt = $dom->createProcessingInstruction('xml-stylesheet', sprintf('type="text/xsl" href="%s"', $xslUrl));
            $dom->appendChild($xslt);
        }

        $documents = $dom->createElementNS(Document::NAMESPACE_URI, "documents");
        $dom->appendChild($documents);

        if($this->getId() !== null)
        {
            $idAttribute = $dom->createAttributeNS(Document::NAMESPACE_URI, "id");
            $idAttribute->value = $this->getId();
            $documents->appendChild($idAttribute);
        }

        $schemaLocationAttribute = $dom->createAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "xsi:schemaLocation");
        $schemaLocationAttribute->value = sprintf("%s %s", Document::NAMESPACE_URI, Document::XSD_URL);
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
