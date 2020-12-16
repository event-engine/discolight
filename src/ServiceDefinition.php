<?php
/**
 * This file is part of event-engine/discolight.
 * (c) 2018-2020 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EventEngine\Discolight;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class ServiceDefinition
{
    public $serviceId;
    public function __construct(string $serviceId)
    {
        $this->serviceId = $serviceId;
    }
}
