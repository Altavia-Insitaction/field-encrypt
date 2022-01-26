<?php

namespace Insitaction\FieldEncryptBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Insitaction\FieldEncryptBundle\Annotations\Encrypt;
use Insitaction\FieldEncryptBundle\Model\EncryptedFieldsInterface;
use Insitaction\FieldEncryptBundle\Service\EncryptService;
use ReflectionClass;

class EncryptionSubscriber implements EventSubscriber
{
    public const ENCRYPTION_MARKER = '<ENC>';

    public function __construct(
        private EncryptService $encryptService
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postLoad,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof EncryptedFieldsInterface) {
            $this->encryptFields($object);
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof EncryptedFieldsInterface) {
            $this->decryptFields($object);
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof EncryptedFieldsInterface) {
            $this->encryptFields($object);
        }
    }

    private function encryptFields(EncryptedFieldsInterface $entity): void
    {
        foreach ((new ReflectionClass($entity))->getProperties() as $reflectionproperty) {
            if (0 !== count($reflectionproperty->getAttributes(Encrypt::class))) {
                $set = 'set' . ucfirst($reflectionproperty->name);
                $get = 'get' . ucfirst($reflectionproperty->name);

                if (is_callable([$entity, $set]) && is_callable([$entity, $get])) {
                    if (self::ENCRYPTION_MARKER !== substr($entity->$get(), -strlen(self::ENCRYPTION_MARKER))) {
                        $entity->$set($this->encryptService->encrypt($entity->$get() . self::ENCRYPTION_MARKER));
                    }
                } else {
                    throw new Exception('You need to define get' . ucfirst($reflectionproperty->name) . 'in entity ' . $entity::class);
                }
            }
        }
    }

    private function decryptFields(EncryptedFieldsInterface $entity): void
    {
        /** @var class-string $entityClass */
        $entityClass = str_replace('Proxies\__CG__\\', '', get_class($entity));
        foreach ((new ReflectionClass($entityClass))->getProperties() as $reflectionproperty) {
            if (0 !== count($reflectionproperty->getAttributes(Encrypt::class))) {
                $set = 'set' . ucfirst($reflectionproperty->name);
                $get = 'get' . ucfirst($reflectionproperty->name);

                if (is_callable([$entity, $set]) && is_callable([$entity, $get])) {
                    if (!is_resource($entity->$get())) {
                        $stream = fopen('php://memory', 'r+');

                        if (false === $stream) {
                            throw new Exception('Cant fopen.');
                        }

                        fwrite($stream, $entity->$get());
                        rewind($stream);
                        $entity->$set($stream);
                    }
                    $decrypt = $this->encryptService->decrypt($entity->$get());
                    if (self::ENCRYPTION_MARKER === substr($decrypt, -strlen(self::ENCRYPTION_MARKER))) {
                        $entity->$set(substr($decrypt, 0, -strlen(self::ENCRYPTION_MARKER)));
                    }
                } else {
                    throw new Exception('You need to define get' . ucfirst($reflectionproperty->name) . 'in entity ' . $entity::class);
                }
            }
        }
    }
}
