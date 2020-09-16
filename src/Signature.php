<?php


namespace Kduma\SignedDocument;


class Signature
{
    protected string $publicKey;
    protected string $signature;
    protected ?string  $id;

    /**
     * Signature constructor.
     *
     * @param string      $publicKey
     * @param string      $signature
     * @param string|null $id
     */
    public function __construct(string $publicKey, string $signature, string $id = null)
    {
        $this->publicKey = $publicKey;
        $this->signature = $signature;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
