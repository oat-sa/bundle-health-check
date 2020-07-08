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

namespace OAT\Bundle\HealthCheckBundle\Command;

use OAT\Library\HealthCheck\HealthChecker;
use OAT\Library\HealthCheck\Result\CheckerResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class HealthCheckCommand extends Command
{
    public const NAME = 'health:check';

    /** @var HealthChecker */
    private $checker;

    public function __construct(HealthChecker $checker)
    {
        parent::__construct(self::NAME);

        $this->checker = $checker;
    }

    protected function configure(): void
    {
        $this->setDescription('Execute configured health checkers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $resultsCollection = $this->checker->performChecks();

            $io->table(
                ['Checker', 'Status', 'Message'],
                array_map(
                    function (CheckerResult $result, string $identifier): array
                    {
                        return [
                            $identifier,
                            $result->isSuccess() ? 'OK' : 'ERROR',
                            $result->getMessage()
                        ];
                    },
                    $resultsCollection->getIterator()->getArrayCopy(),
                    array_keys($resultsCollection->getIterator()->getArrayCopy())
                )
            );

            return $resultsCollection->hasErrors() ? 1 : 0;

        } catch (Throwable $exception) {
            $io->error('Error: ' . $exception->getMessage());

            return 1;
        }
    }
}
