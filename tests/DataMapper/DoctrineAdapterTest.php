<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\Test\DataMapper;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Thorr\Persistence\Entity\AbstractEntity;

/**
 * @covers Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter
 */
class DoctrineAdapterTest extends TestCase
{
    public function testConstructor()
    {
        $objectManager = $this->getMock(ObjectManager::class);

        $adapter = new DoctrineAdapter($objectManager, AbstractEntity::class);
        $this->assertSame(AbstractEntity::class, $adapter->getEntityClass());
        $this->assertSame($objectManager, $adapter->getObjectManager());
    }
}
