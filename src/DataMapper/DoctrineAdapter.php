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
     * @param string        $entityClass
     * @param ObjectManager $objectManager
     */
    public function __construct($entityClass, ObjectManager $objectManager)
    {
        $this->entityClass = $entityClass;
        $this->setObjectManager($objectManager);
    }

    /**
     * The entity class handled by this adapter. It's immutable.
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param  $id
     * @return object|null
     */
    public function find($id)
    {
        return $this->objectManager->find($id, $this->entityClass);
    }

    /**
     * @param  object $entity
     */
    public function remove($entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
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
        $this->objectManager->flush($entity);
    }
}
