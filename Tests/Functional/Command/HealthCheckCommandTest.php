<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Bundle\HealthCheckBundle\Tests\Functional\Command;

use Exception;
use OAT\Bundle\HealthCheckBundle\Command\HealthCheckCommand;
use OAT\Bundle\HealthCheckBundle\Tests\Resources\Checker\ErrorTestChecker;
use OAT\Library\HealthCheck\HealthChecker;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class HealthCheckCommandTest extends KernelTestCase
{
    /** @var CommandTester */
    private $commandTester;

    protected function setUp(): void
    {
        $application = new Application(static::bootKernel());

        $this->commandTester = new CommandTester($application->find(HealthCheckCommand::NAME));
    }

    public function testHealthCheckCommandWithRegisteredSuccessCheckers(): void
    {
        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result);

        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('success 2 message', $display);
        $this->assertStringContainsString('success 1 message', $display);
    }

    public function testHealthCheckEndpointWithAddedFailingChecker(): void
    {
        // registers manually a supplementary failing checker onto the registered HealthChecker container service
        static::$container->get(HealthChecker::class)->registerChecker(new ErrorTestChecker());

        $result = $this->commandTester->execute([]);

        $this->assertEquals(1, $result);

        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('success 2 message', $display);
        $this->assertStringContainsString('success 1 message', $display);
        $this->assertStringContainsString('error', $display);
    }

    public function testHealthCheckEndpointWithCustomError(): void
    {
        $checkerMock = $this->createMock(HealthChecker::class);
        $checkerMock
            ->expects($this->once())
            ->method('performChecks')
            ->willThrowException(new Exception('custom error'));

        $commandTester = new CommandTester(new HealthCheckCommand($checkerMock));

        $result = $commandTester->execute([]);

        $this->assertEquals(1, $result);
        $this->assertStringContainsString('Error: custom error', $commandTester->getDisplay());
    }
}
