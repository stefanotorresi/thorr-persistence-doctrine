<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Thorr\Persistence\DataMapper\DeferredOperationProvider;
use Thorr\Persistence\DataMapper\DeferredRemoveProvider;
use Thorr\Persistence\DataMapper\DeferredSaveProvider;
use Thorr\Persistence\DataMapper\EntityFinderInterface;
use Thorr\Persistence\DataMapper\EntityRemoverInterface;
use Thorr\Persistence\DataMapper\EntitySaverInterface;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerAwareInterface;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerAwareTrait;

class DoctrineAdapter implements
    EntityFinderInterface,
    EntitySaverInterface,
    EntityRemoverInterface,
    DeferredOperationProvider,
    DeferredSaveProvider,
    DeferredRemoveProvider,
    ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param ObjectManager $objectManager
     * @param string        $entityClass
     */
    public function __construct($entityClass, ObjectManager $objectManager)
    {
        $this->entityClass = $entityClass;
        $this->setObjectManager($objectManager);
    }

    /**
     * The entity class handled by this adapter. Must be set during instantiation and it's immutable.
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUuid($uuid)
    {
        if (! $uuid instanceof Uuid) {
            try {
                $uuid = Uuid::fromString($uuid);
            } catch (InvalidArgumentException $e) {
                return;
            }
        }

        return $this->objectManager->getRepository($this->entityClass)->findOneBy(['uuid' => $uuid->toString()]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeByUuid($uuid)
    {
        $entity = $this->findByUuid($uuid);
        $this->remove($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity)
    {
        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred($entity)
    {
        $this->objectManager->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function removeDeferred($entity)
    {
        $this->objectManager->remove($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->objectManager->flush();
    }
}
