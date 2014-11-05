<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;

trait ObjectManagerGuard
{
    /**
     * @param string        $expected an ObjectManager implementation FQCN
     * @param ObjectManager $actual   an ObjectManager instance
     */
    protected function guardForSpecificObjectManager($expected, ObjectManager $actual)
    {
        if (! is_a($actual, $expected)) {
            throw new InvalidArgumentException(sprintf(
                "Expected '%s', got '%s'",
                $expected,
                get_class($actual)
            ));
        }
    }
}
