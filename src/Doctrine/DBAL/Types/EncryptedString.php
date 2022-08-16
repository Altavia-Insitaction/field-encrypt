<?php

namespace Insitaction\FieldEncryptBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Exception;
use Insitaction\FieldEncryptBundle\EventListener\EncryptionListener;
use Insitaction\FieldEncryptBundle\Service\EncryptService;
use LogicException;

class EncryptedString extends Type
{
    public const ENCRYPTED_STRING = 'encrypted_string';

    public function getName()
    {
        return self::ENCRYPTED_STRING;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'ENCRYPTED_STRING';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!str_ends_with($value, EncryptionListener::ENCRYPTION_MARKER)) {
            throw new LogicException('Value already decrypted.');
        }

        return $this->getEncryptService($platform)
                ->decrypt(substr($value, 0, -strlen(EncryptionListener::ENCRYPTION_MARKER)))
        ;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (str_ends_with($value, EncryptionListener::ENCRYPTION_MARKER)) {
            throw new LogicException('Value already encrypted.');
        }

        return $this->getEncryptService($platform)
            ->encrypt($value) . EncryptionListener::ENCRYPTION_MARKER;
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    private function getEncryptService(AbstractPlatform $platform): EncryptService
    {
        $listeners = $platform->getEventManager()->getListeners('getEncryptService');
        $listener = array_shift($listeners);

        if (!$listener instanceof EncryptionListener) {
            throw new Exception('Cant find EncryptionListener.');
        }

        return $listener->getEncryptService();
    }
}
