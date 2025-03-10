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

namespace OAT\Bundle\HealthCheckBundle\Tests\Functional\Action;

use OAT\Bundle\HealthCheckBundle\Tests\Resources\Checker\ErrorTestChecker;
use OAT\Library\HealthCheck\HealthChecker;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckActionTest extends WebTestCase
{
    /** @var KernelBrowser */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->logger = $this->getContainer()->get(LoggerInterface::class);
    }

    public function testHealthCheckEndpointWithRegisteredSuccessCheckers(): void
    {
        $this->client->request(Request::METHOD_GET, '/health-check');

        $response = $this->client->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->assertEquals(
            [
                'success' => true,
                'checkers' => [
                    'success2' => [
                        'success' => true,
                        'message' => 'success 2 message',
                    ],
                    'success1' => [
                        'success' => true,
                        'message' => 'success 1 message',
                    ],
                ]
            ],
            json_decode($response->getContent(), true)['data']
        );

        $this->assertTrue($this->logger->hasInfo('[health-check] checker success1 success: success 1 message'));
        $this->assertTrue($this->logger->hasInfo('[health-check] checker success2 success: success 2 message'));
    }

    public function testHealthCheckEndpointWithAddedFailingChecker(): void
    {
        // registers manually a supplementary failing checker onto the registered HealthChecker container service
        $this->getContainer()->get(HealthChecker::class)->registerChecker(new ErrorTestChecker());

        $this->client->request(Request::METHOD_GET, '/health-check');

        $response = $this->client->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $this->assertEquals(
            [
                'success' => false,
                'checkers' => [
                    'success2' => [
                        'success' => true,
                        'message' => 'success 2 message',
                    ],
                    'success1' => [
                        'success' => true,
                        'message' => 'success 1 message',
                    ],
                    'error' => [
                        'success' => false,
                        'message' => 'error message',
                    ]
                ]
            ],
            json_decode($response->getContent(), true)['data']
        );

        $this->assertTrue($this->logger->hasInfo('[health-check] checker success1 success: success 1 message'));
        $this->assertTrue($this->logger->hasInfo('[health-check] checker success2 success: success 2 message'));
        $this->assertTrue($this->logger->hasError('[health-check] checker error failure: error message'));
    }
}
