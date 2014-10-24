<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use Thorr\Persistence\Doctrine\EntityManager\EntityManagerAwareInterface;
use Thorr\Persistence\Doctrine\EntityManager\EntityManagerAwareTrait;

class DoctrinePHPCRODMAdapter extends DoctrineAdapter implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param string $entityClass
     * @param ObjectManager $objectManager
     */
    public function __construct($entityClass, ObjectManager $objectManager)
    {
        parent::__construct($entityClass, $objectManager);

        $this->setEntityManager($objectManager);
    }
}
