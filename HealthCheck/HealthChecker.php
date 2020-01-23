<?php declare(strict_types=1);
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
namespace OAT\Bundle\HealthCheckBundle\HealthCheck;

use OAT\Bundle\HealthCheckBundle\HealthCheck\Checker\CheckerInterface;
use OAT\Bundle\HealthCheckBundle\HealthCheck\Result\CheckerResult;
use OAT\Bundle\HealthCheckBundle\HealthCheck\Result\CheckerResultCollection;
use Throwable;

class HealthChecker
{
    /** @var CheckerInterface[] */
    private $checkers;

    public function __construct(iterable $checkers)
    {
        foreach ($checkers as $checker) {
            $this->addChecker($checker);
        }
    }

    public function addChecker(CheckerInterface $checker): self
    {
        $this->checkers[] = $checker;

        return $this;
    }

    public function performChecks(): CheckerResultCollection
    {
        $collection = new CheckerResultCollection();

        foreach ($this->checkers as $checker) {
            try {
                $result = $checker->check();
            } catch (Throwable $exception) {
                $result = new CheckerResult(false, $exception->getMessage());
            }

            $collection->add($checker->getIdentifier(), $result);
        }

        return $collection;
    }
}
