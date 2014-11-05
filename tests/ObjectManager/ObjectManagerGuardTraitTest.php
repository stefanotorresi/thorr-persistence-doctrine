<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\Test\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerGuardTrait;

class ObjectManagerGuardTraitTest extends TestCase
{
    public function testGuardForSpecificObjectManager()
    {
        $trait = $this->getMockForTrait(ObjectManagerGuardTrait::class);

        $objectManager = $this->getMock(ObjectManager::class);

        $method = new \ReflectionMethod($trait, 'guardForSpecificObjectManager');
        $method->setAccessible(true);

        $method->invoke($trait, ObjectManager::class, $objectManager);

        $this->setExpectedException(InvalidArgumentException::class, get_class($objectManager));

        $method->invoke($trait, 'SomeObjectManagerSubClass', $objectManager);
    }
}
