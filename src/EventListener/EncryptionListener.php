<?php

namespace Insitaction\FieldEncryptBundle\EventListener;

use Insitaction\FieldEncryptBundle\Service\EncryptService;

class EncryptionListener
{
    public const ENCRYPTION_MARKER = '<ENC>';

    private EncryptService $encryptService;

    public function __construct(
        EncryptService $encryptService
    ) {
        $this->encryptService = $encryptService;
    }

    public function getEncryptService(): EncryptService
    {
        return $this->encryptService;
    }
}
