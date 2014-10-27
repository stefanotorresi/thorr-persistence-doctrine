<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
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

        if (! $objectManager->getMetadataFactory()->hasMetadataFor($this->entityClass)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not a valid entity class for requested mapper "%s"',
                $entityClass,
                __CLASS__
            ));
        }

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
        $this->objectManager->flush($entity);
    }
}
