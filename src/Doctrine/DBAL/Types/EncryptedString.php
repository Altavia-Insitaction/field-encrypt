<?php

namespace Insitaction\FieldEncryptBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BinaryType;
use Exception;
use Insitaction\FieldEncryptBundle\EventListener\EncryptionListener;
use Insitaction\FieldEncryptBundle\Service\EncryptService;
use LogicException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class EncryptedString extends BinaryType
{
    public const NAME = 'ENCRYPTED_STRING';
    private ?EncryptService $encryptService = null;
    private ?HtmlSanitizerInterface $htmlSanitizer = null;

    public function getName()
    {
        return self::NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        if (!str_ends_with($value, EncryptionListener::ENCRYPTION_MARKER)) {
            throw new LogicException('Value already decrypted.');
        }

        $this->getServices($platform);

        return html_entity_decode($this->getEncryptService()
            ->decrypt(substr($value, 0, -strlen(EncryptionListener::ENCRYPTION_MARKER))));
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (str_ends_with($value, EncryptionListener::ENCRYPTION_MARKER)) {
            throw new LogicException('Value already encrypted.');
        }

        $this->getServices($platform);

        return $this->getEncryptService()->encrypt($this->sanitize($value)) . EncryptionListener::ENCRYPTION_MARKER;
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    private function getServices(AbstractPlatform $platform): void
    {
        $listeners = $platform->getEventManager()->getListeners('getEncryptService');
        $listener = array_shift($listeners);

        if (!$listener instanceof EncryptionListener) {
            throw new Exception('Cant find EncryptionListener.');
        }

        $this->encryptService = $listener->getEncryptService();
        $this->htmlSanitizer = $listener->getHtmlSanitizer();
    }

    private function getEncryptService(): EncryptService
    {
        if (!$this->encryptService instanceof EncryptService) {
            throw new Exception('EncryptService not loaded.');
        }

        return $this->encryptService;
    }

    private function getHtmlSanitizer(): HtmlSanitizer
    {
        if (!$this->htmlSanitizer instanceof HtmlSanitizer) {
            throw new Exception('HtmlSanitizer not loaded.');
        }

        return $this->htmlSanitizer;
    }

    private function sanitize(string $value): string
    {
        return $this->getHtmlSanitizer()->sanitize($value);
    }
}
