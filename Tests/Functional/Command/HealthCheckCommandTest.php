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
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class HealthCheckCommandTest extends KernelTestCase
{
    /** @var CommandTester */
    private $commandTester;

    /** @var LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        $application = new Application(static::bootKernel());

        $this->commandTester = new CommandTester($application->find(HealthCheckCommand::NAME));

        $this->logger = static::$container->get(LoggerInterface::class);
    }

    public function testHealthCheckCommandWithRegisteredSuccessCheckers(): void
    {
        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result);

        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('success 2 message', $display);
        $this->assertStringContainsString('success 1 message', $display);

        $this->assertTrue($this->logger->hasInfo('[health-check] checker success1 success: success 1 message'));
        $this->assertTrue($this->logger->hasInfo('[health-check] checker success2 success: success 2 message'));
    }

    public function testHealthCheckCommandWithAddedFailingChecker(): void
    {
        // registers manually a supplementary failing checker onto the registered HealthChecker container service
        static::$container->get(HealthChecker::class)->registerChecker(new ErrorTestChecker());

        $result = $this->commandTester->execute([]);

        $this->assertEquals(1, $result);

        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('success 2 message', $display);
        $this->assertStringContainsString('success 1 message', $display);
        $this->assertStringContainsString('error', $display);

        $this->assertTrue($this->logger->hasInfo('[health-check] checker success1 success: success 1 message'));
        $this->assertTrue($this->logger->hasInfo('[health-check] checker success2 success: success 2 message'));
        $this->assertTrue($this->logger->hasError('[health-check] checker error failure: error message'));
    }

    public function testHealthCheckCommandWithCustomError(): void
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
