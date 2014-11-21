<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use Thorr\Persistence\DataMapper\DataMapperInterface;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerAwareTrait;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerAwareInterface;

class DoctrineAdapter implements DataMapperInterface, ObjectManagerAwareInterface
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
    public function __construct(ObjectManager $objectManager, $entityClass = null)
    {
        if ($entityClass) {
            $this->entityClass = $entityClass;
        }

        $this->setObjectManager($objectManager);
    }

    /**
     * The entity class handled by this adapter. It's immutable.
     * Can be set at instantiation or provided as default value by a subclass
     *
     * @see Thorr\Persistence\Doctrine\DataMapper\Manager\DoctrineAdapterAbstractFactory::createServiceWithName()
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param  mixed       $id
     * @return object|null
     */
    public function findById($id)
    {
        return $this->objectManager->find($id, $this->entityClass);
    }

    /**
     * @param object $entity
     */
    public function remove($entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
    }

    /**
     * @param mixed $id
     */
    public function removeById($id)
    {
        $entity = $this->findById($id);
        $this->remove($entity);
    }

    /**
     * @param object $entity
     */
    public function save($entity)
    {
        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    /**
     * @param object $entity
     */
    public function update($entity)
    {
        $this->save($entity);
    }
}
