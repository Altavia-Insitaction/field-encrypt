<?php

namespace Insitaction\FieldEncryptBundle\EventListener;

use Insitaction\FieldEncryptBundle\Service\EncryptService;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class EncryptionListener
{
    public const ENCRYPTION_MARKER = '<ENC>';

    public function __construct(
        private readonly EncryptService $encryptService,
        private readonly HtmlSanitizerInterface $htmlSanitizer,
    ) {}

    public function getEncryptService(): EncryptService
    {
        return $this->encryptService;
    }

    public function getHtmlSanitizer(): HtmlSanitizerInterface
    {
        return $this->htmlSanitizer;
    }
}
