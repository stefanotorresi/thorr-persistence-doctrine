<?php
/**
 * @license See the file LICENSE for copying permission
 */

namespace Thorr\Persistence\Doctrine\Test\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerAwareTrait;

class ObjectManagerAwareTraitTest extends TestCase
{
    public function testAccessors()
    {
        $sut           = $this->getMockForTrait(ObjectManagerAwareTrait::class);
        $objectManager = $this->getMock(ObjectManager::class);

        $sut->setObjectManager($objectManager);

        $this->assertSame($objectManager, $sut->getObjectManager());
    }
}
