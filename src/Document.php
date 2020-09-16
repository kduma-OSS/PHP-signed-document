<?php


namespace Kduma\SignedDocument;


use DOMDocument;
use DOMElement;
use ParagonIE\Halite\Asymmetric\Crypto;
use ParagonIE\Halite\Halite;
use ParagonIE\Halite\KeyFactory;

class Document
{
    const NAMESPACE_URI = "https://opensource.duma.sh/xml/signed-document";

    protected ?string $id = null;
    protected string $sha256;
    protected $content;
    protected string $encoding  = 'raw';
    protected array $signatures = [];

    /**
     * Document constructor.
     */
    protected function __construct()
    {
    }

    public static function make($content, string $id = null)
    {
        $document = new self();

        $document->id = $id;
        $document->content = $content;
        $document->sha256 = hash('sha256', $content);

        return $document;
    }

    public static function makeBin($content, string $id = null)
    {
        $document = new self();

        $document->encoding = 'base64';
        $document->id = $id;
        $document->content = $content;
        $document->sha256 = hash('sha256', $content);

        return $document;
    }

    public static function fromXml(string $xml): Document
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        if (!@$dom->schemaValidate(__DIR__ . '/../schema/signed-document.xsd')) {
            throw new \Exception('Invalid XML schema!');
        }

        return self::fromDom(
            $dom->getElementsByTagNameNS(self::NAMESPACE_URI, "document")->item(0)
        );
    }

    public static function fromDom(DOMElement $element): Document
    {
        $document = new self();
        $documentId = $element->attributes->getNamedItem("id");
        $document->id = $documentId ? $documentId->nodeValue : null;


        $content = $element->getElementsByTagNameNS(self::NAMESPACE_URI, "content")->item(0);

        $contentEncoding = $content->attributes->getNamedItem("encoding");
        $document->encoding = $contentEncoding ? $contentEncoding->nodeValue : 'raw';

        switch ($document->getEncoding()) {
            case 'raw':
                $document->content = $content->nodeValue;
                break;

            case 'base64':
                $document->content = base64_decode(str_replace("\n", "", $content->nodeValue));
                break;

            default:
                throw new \Exception('Unsupported encoding: '.$document->getEncoding());
        }

        $contentSha256 = $content->attributes->getNamedItem("sha256");
        $document->sha256 = $contentSha256->nodeValue;

        foreach ($element->getElementsByTagNameNS(self::NAMESPACE_URI, "signature") as $signature) {
            $signaturePublicKey = $signature->attributes->getNamedItem("public-key");
            $signatureId = $signature->attributes->getNamedItem("id");

            $document->signatures[] = new Signature(
                $signaturePublicKey->nodeValue,
                $signature->nodeValue,
                $signatureId ? $signatureId->nodeValue : null
            );
        }

        return $document;
    }

    public function addSignature(Signature $sign)
    {
        $this->signatures[] = $sign;

        return $this;
    }

    /**
     * @param string|null $id
     *
     * @return Document
     */
    public function setId(?string $id): Document
    {
        $this->id = $id;
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
     * @return string
     */
    public function getSha256(): string
    {
        return $this->sha256;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @return array
     */
    public function getSignatures(): array
    {
        return $this->signatures;
    }

    public function getDom(DOMDocument $dom, DOMElement $document, bool $formatOutput = true)
    {
        $contents = $dom->createElementNS(self::NAMESPACE_URI, "content");
        $contents = $document->appendChild($contents);
        $encodingAttribute = $dom->createAttributeNS(self::NAMESPACE_URI, "encoding");
        $contents->appendChild($encodingAttribute);

        switch ($this->getEncoding()) {
            case 'raw':

                $contentCdata = $dom->createCDATASection($this->getContent());

                $contents->appendChild($contentCdata);
                $encodingAttribute->value = 'raw';
                break;

            case 'base64':
                if($formatOutput) {
                    $value = $dom->createCDATASection(
                        "\n" . implode("\n", str_split(base64_encode($this->getContent()), 64)) . "\n"
                    );
                }else {
                    $value = $dom->createTextNode(base64_encode($this->getContent()));
                }

                $contents->appendChild($value);
                $encodingAttribute->value = 'base64';
                break;

            default:
                throw new \Exception('Unsupported encoding: '.$this->getEncoding());
        }

        $sha256Attribute = $dom->createAttributeNS(self::NAMESPACE_URI, "sha256");
        $sha256Attribute->value = hash('sha256', $this->getContent());
        $contents->appendChild($sha256Attribute);

        /** @var Signature $item */
        foreach ($this->signatures as $item) {
            $signature = $dom->createElementNS(self::NAMESPACE_URI, "signature", $item->getSignature());
            $signature = $document->appendChild($signature);

            if($item->getId() !== null) {
                $publicKeyAttribute = $dom->createAttributeNS(self::NAMESPACE_URI, "id");
                $publicKeyAttribute->value = $item->getId();
                $signature->appendChild($publicKeyAttribute);
            }

            $publicKeyAttribute = $dom->createAttributeNS(self::NAMESPACE_URI, "public-key");
            $publicKeyAttribute->value = $item->getPublicKey();
            $signature->appendChild($publicKeyAttribute);
        }

        return $document;
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

        $xslt = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsl"');
        $dom->appendChild($xslt);

        $document = $dom->createElementNS(self::NAMESPACE_URI, "document");
        $dom->appendChild($document);

        if($this->getId() !== null)
        {
            $idAttribute = $dom->createAttributeNS(self::NAMESPACE_URI, "id");
            $idAttribute->value = $this->getId();
            $document->appendChild($idAttribute);
        }

        $schemaLocationAttribute = $dom->createAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "xsi:schemaLocation");
        $schemaLocationAttribute->value = self::NAMESPACE_URI.' https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd';
        $document->appendChild($schemaLocationAttribute);

        $this->getDom($dom, $document, $formatOutput);

        return $dom->saveXML();
    }

    public function __toString()
    {
        return $this->getXml(false);
    }
}
