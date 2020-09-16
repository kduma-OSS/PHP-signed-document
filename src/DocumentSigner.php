<?php


namespace Kduma\SignedDocument;

use ParagonIE\Halite\Asymmetric\Crypto;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\Halite\Halite;
use ParagonIE\Halite\KeyFactory;

class DocumentSigner
{
    /**
     * @var SignatureSecretKey
     */
    private SignatureSecretKey $secretKey;

    /**
     * DocumentSigner constructor.
     *
     * @param SignatureSecretKey $secretKey
     */
    public function __construct(SignatureSecretKey $secretKey)
    {
        $this->secretKey = $secretKey;
    }


    public function sign(Document $document, string $id = null): Signature
    {
        return new Signature(
            KeyFactory::export($this->secretKey->derivePublicKey())->getString(),
            Crypto::sign(
                $id.'|'.$document->getContent(),
                $this->secretKey,
                Halite::ENCODE_HEX
            ),
            $id
        );
    }
}
