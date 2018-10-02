<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Tests\Sdk\Model;

use PHPUnit\Framework\TestCase;
use SymfonyCorp\Insight\Sdk\Model\Violations;

class ViolationsTest extends TestCase
{
    public function testCount()
    {
        $violations = new Violations();

        $reflector = new \ReflectionObject($violations);
        $violationsAttr = $reflector->getProperty('violations');
        $violationsAttr->setAccessible(true);
        $violationsAttr->setValue($violations, range(1, 10));
        $violationsAttr->setAccessible(false);

        $this->assertSame(10, count($violations));
    }

    public function testIterable()
    {
        $violations = new Violations();

        $reflector = new \ReflectionObject($violations);
        $violationsAttr = $reflector->getProperty('violations');
        $violationsAttr->setAccessible(true);
        $violationsAttr->setValue($violations, range(0, 10));
        $violationsAttr->setAccessible(false);

        foreach ($violations as $k => $violation) {
            $this->assertSame($k, $violation);
        }
    }
}
