<?php

namespace Insitaction\FieldEncryptBundle\EventListener;

use Insitaction\FieldEncryptBundle\Service\EncryptService;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

class EncryptionListener
{
    public const ENCRYPTION_MARKER = '<ENC>';

    public function __construct(
        private readonly EncryptService $encryptService,
        private readonly HtmlSanitizer $htmlSanitizer,
    ) {}

    public function getEncryptService(): EncryptService
    {
        return $this->encryptService;
    }

    public function getHtmlSanitizer(): HtmlSanitizer
    {
        return $this->htmlSanitizer;
    }
}
