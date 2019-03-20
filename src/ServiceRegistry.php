<?php
/**
 * This file is part of event-engine/discolight.
 * (c) 2018-2019 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EventEngine\Discolight;

trait ServiceRegistry
{
    /**
     * @var array
     */
    private $serviceRegistry = [];

    private function makeSingleton(string $serviceId, callable $factory)
    {
        if (! isset($this->serviceRegistry[$serviceId])) {
            $this->serviceRegistry[$serviceId] = $factory();
        }

        return $this->serviceRegistry[$serviceId];
    }
}
