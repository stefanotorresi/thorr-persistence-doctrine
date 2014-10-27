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
        $metadataFactory = $this->getMock(ClassMetadataFactory::class);

        $objectManager->expects($this->any())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $metadataFactory->expects($this->any())
            ->method('hasMetadataFor')
            ->with(AbstractEntity::class)
            ->willReturn(true);

        $adapter = new DoctrineAdapter($objectManager, AbstractEntity::class);
        $this->assertSame(AbstractEntity::class, $adapter->getEntityClass());
    }

    public function testInvalidEntityClassName()
    {
        $objectManager = $this->getMock(ObjectManager::class);
        $metadataFactory = $this->getMock(ClassMetadataFactory::class);

        $objectManager->expects($this->any())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $metadataFactory->expects($this->any())
            ->method('hasMetadataFor')
            ->with(null)
            ->willReturn(false);

        $this->setExpectedException(\InvalidArgumentException::class, 'not a valid entity class');
        $adapter = new DoctrineAdapter($objectManager);
    }
}
