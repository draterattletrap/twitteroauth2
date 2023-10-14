<?php declare(strict_types=1);
/*
 * This file is part of TwitterOAuth2.
 *
 * (c) Drate Rattletrap <draterattletrap@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DrateRattletrap\Twitter\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class UnitTestSetupTest extends TestCase
{
    public function testGreeting(): void
    {
        $greeting = 'Hello, Alice!';

        $this->assertSame('Hello, Alice!', $greeting);
    }
}
