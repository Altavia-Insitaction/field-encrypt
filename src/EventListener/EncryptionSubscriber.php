<?php

namespace Insitaction\FieldEncryptBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Insitaction\FieldEncryptBundle\Annotations\Encrypt;
use Insitaction\FieldEncryptBundle\Model\EncryptedFieldsInterface;
use Insitaction\FieldEncryptBundle\Service\EncryptService;
use Insitaction\FieldEncryptBundle\Service\Misc\CacheItem;
use ReflectionClass;

class EncryptionSubscriber implements EventSubscriber
{
    /** @var string[] */
    private array $cacheToDelete = [];

    public function __construct(
        private EncryptService $encryptService,
        private CacheItem $cacheItem
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad,
            Events::postFlush,
        ];
    }

    public function postFlush(): void
    {
        foreach ($this->cacheToDelete as $cacheToDelete) {
            $this->cacheItem->remove($cacheToDelete);
        }
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof EncryptedFieldsInterface) {
            $this->encryptFields($object);
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof EncryptedFieldsInterface) {
            $this->encryptFields($object);
        }
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof EncryptedFieldsInterface) {
            $this->decryptFields($object);
        }
    }

    private function encryptFields(EncryptedFieldsInterface $entity): void
    {
        /** @var class-string $entityClass */
        $entityClass = str_replace('Proxies\__CG__\\', '', get_class($entity));

        foreach ((new ReflectionClass($entity))->getProperties() as $reflectionproperty) {
            if (0 !== count($reflectionproperty->getAttributes(Encrypt::class))) {
                $set = 'set' . ucfirst($reflectionproperty->name);
                $get = 'get' . ucfirst($reflectionproperty->name);

                if (is_callable([$entity, $set]) && is_callable([$entity, $get])) {
                    $entity->$set($this->encryptService->encrypt($entity->$get()));
                    $this->cacheToDelete[] = md5($entityClass . 'decrypt' . $entity->getUniqueIdentifier() . $get);
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
                    $key = md5($entityClass . 'decrypt' . $entity->getUniqueIdentifier() . $get);
                    $cache = $this->cacheItem->get($key);
                    if (null === $cache) {
                        $decrypt = $this->encryptService->decrypt($entity->$get());
                        $entity->$set($decrypt);
                        $this->cacheItem->cache($decrypt, $key);
                    } else {
                        $entity->$set($cache);
                    }
                } else {
                    throw new Exception('You need to define get' . ucfirst($reflectionproperty->name) . 'in entity ' . $entity::class);
                }
            }
        }
    }
}
