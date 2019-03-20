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

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFound extends \RuntimeException implements NotFoundExceptionInterface
{
    public static function withServiceId(string $id): ServiceNotFound
    {
        return new self('Service not found with id: ' . $id);
    }
}
