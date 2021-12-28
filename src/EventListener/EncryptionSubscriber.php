<?php

namespace Insitaction\EventListener;

use Insitaction\Annotations\Encrypt;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Insitaction\Service\EncryptService;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EncryptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EncryptService $encryptService,
        private Reader $reader,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->encryptFields($args->getObject());
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->encryptFields($args->getObject());
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $this->decryptFields($args->getObject());
    }

    private function encryptFields(object $entity): void
    {
        foreach ((new ReflectionClass($entity))->getProperties() as $reflectionproperty) {
            if (null !== $this->reader->getPropertyAnnotation(
                    new ReflectionProperty($entity, $reflectionproperty->name),
                    Encrypt::class
                )) {
                $set = 'set' . ucfirst($reflectionproperty->name);
                $get = 'get' . ucfirst($reflectionproperty->name);

                if (is_callable([$entity, $set]) && is_callable([$entity, $get])) {
                    $entity->$set($this->encryptService->encrypt($entity->$get()));
                } else {
                    throw new Exception('You need to define get' . ucfirst($reflectionproperty->name) . 'in entity ' . $entity::class);
                }
            }
        }
    }

    private function decryptFields(object $entity): void
    {
        /** @var class-string $entityClass */
        $entityClass = str_replace('Proxies\__CG__\\', '', get_class($entity));
        foreach ((new ReflectionClass($entityClass))->getProperties() as $reflectionproperty) {
            if (null !== $this->reader->getPropertyAnnotation(
                    new ReflectionProperty($entityClass, $reflectionproperty->name),
                    Encrypt::class
                )) {
                $set = 'set' . ucfirst($reflectionproperty->name);
                $get = 'get' . ucfirst($reflectionproperty->name);

                if (is_callable([$entity, $set]) && is_callable([$entity, $get])) {
                    $entity->$set($this->encryptService->decrypt($entity->$get()));
                } else {
                    throw new Exception('You need to define get' . ucfirst($reflectionproperty->name) . 'in entity ' . $entity::class);
                }
            }
        }
    }
}
