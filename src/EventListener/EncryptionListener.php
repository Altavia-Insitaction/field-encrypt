<?php

namespace Insitaction\FieldEncryptBundle\EventListener;

use Insitaction\FieldEncryptBundle\Service\EncryptService;

class EncryptionListener
{
    public const ENCRYPTION_MARKER = '<ENC>';

    public function __construct(
        private EncryptService $encryptService,
    ) {
    }

    public function getEncryptService(): EncryptService
    {
        return $this->encryptService;
    }
}
