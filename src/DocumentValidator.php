<?php


namespace Kduma\SignedDocument;


use ParagonIE\Halite\Asymmetric\Crypto;
use ParagonIE\Halite\Halite;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;

class DocumentValidator
{
    public function validate(Document $document)
    {
        $validation_errors = [];

        if($document->getSha256() !== hash('sha256', $document->getContent())) {
            $validation_errors[] = 'Provided sha256 checksum is invalid!';
        }

        /** @var Signature $signature */
        foreach ($document->getSignatures() as $signature) {
            $publicKey = KeyFactory::importSignaturePublicKey(new HiddenString($signature->getPublicKey()));
            if(!Crypto::verify($signature->getId().'|'.$document->getContent(), $publicKey, $signature->getSignature(), Halite::ENCODE_HEX)) {
                $validation_errors[] = $signature->getId() ? 'Signature with id of '.$signature->getId().' is invalid!' : 'Signature with is invalid!';
            }
        }

        if(!empty($validation_errors))
            throw new \Exception(implode(', ', $validation_errors));

        return true;
    }

    public function validateCollection(DocumentCollection $documents)
    {
        $validation_errors = [];

        foreach ($documents->getDocuments() as $no => $document) {
            try {
                $this->validate($document);
            } catch (\Exception $e) {
                $id = $document->getId() ?? "#$no";
                $validation_errors[] = sprintf("%s -> %s", $id, $e->getMessage());
            }
        }

        if(!empty($validation_errors))
            throw new \Exception(implode(', ', $validation_errors));

        return true;
    }
}
