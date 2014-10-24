<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use Thorr\Persistence\Doctrine\DocumentManager\Mongo\MongoDMAwareInterface;
use Thorr\Persistence\Doctrine\DocumentManager\Mongo\MongoDMAwareTrait;

class DoctrineMongoODMAdapter extends DoctrineAdapter implements MongoDMAwareInterface
{
    use MongoDMAwareTrait;

    /**
     * @param string $entityClass
     * @param ObjectManager $objectManager
     */
    public function __construct($entityClass, ObjectManager $objectManager)
    {
        parent::__construct($entityClass, $objectManager);

        $this->setDocumentManager($objectManager);
    }
}
