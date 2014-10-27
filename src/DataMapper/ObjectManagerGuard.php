<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;

trait ObjectManagerGuard
{
    /**
     * @param string        $expected an ObjectManager implementation FQCN
     * @param ObjectManager $actual   an ObjectManager instance
     */
    protected function guardForSpecificObjectManager($expected, ObjectManager $actual)
    {
        if (! $actual instanceof $expected) {
            throw new \InvalidArgumentException(sprintf(
                "This class needs a %s, %s given",
                $expected,
                get_class($actual)
            ));
        }
    }
}
