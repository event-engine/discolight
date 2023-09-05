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

use Psr\Container\ContainerInterface;

final class Discolight implements ContainerInterface
{
    /**
     * @var array
     */
    private $aliasMap;

    /**
     * @var array
     */
    private $serviceFactoryMap;

    private $serviceFactory;

    public function __construct($serviceFactory, array $aliasMap = [], array $serviceFactoryMap = null)
    {
        if (null === $serviceFactoryMap) {
            $serviceFactoryMap = $this->scanServiceFactory($serviceFactory);
        }

        $this->serviceFactory = $serviceFactory;
        $this->aliasMap = $aliasMap;
        $this->serviceFactoryMap = $serviceFactoryMap;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $id = $this->aliasMap[$id] ?? $id;

        if (! $this->has($id)) {
            throw ServiceNotFound::withServiceId($id);
        }

        return ([$this->serviceFactory, $this->serviceFactoryMap[$id]])();
    }

    /**
     * {@inheritdoc}
     */
    public function has($id): bool
    {
        $id = $this->aliasMap[$id] ?? $id;

        return \array_key_exists($id, $this->serviceFactoryMap);
    }

    /**
     * Cache the array and pass it to constructor again to avoid scanning of service factory
     *
     * @return array
     */
    public function getServiceFactoryMap(): array
    {
        return $this->serviceFactoryMap;
    }

    private function scanServiceFactory($serviceFactory): array
    {
        $serviceFactoryMap = [];

        $ref = new \ReflectionClass($serviceFactory);

        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) {
                continue;
            }

            // PHP < 8 does not have the getAttributes method
            if (method_exists($method, 'getAttributes')) {
                $serviceIdAttributes = $method->getAttributes(ServiceDefinition::class, \ReflectionAttribute::IS_INSTANCEOF);
                if (\count($serviceIdAttributes) > 0) {
                    foreach ($serviceIdAttributes as $serviceIdAttribute) {
                        /** @var ServiceDefinition $serviceDefinition */
                        $serviceDefinition = $serviceIdAttribute->newInstance();
                        $serviceFactoryMap = $this->addToServiceFactoryMap($serviceFactoryMap, $serviceDefinition->serviceId, $method->getName());
                    }
                    continue;
                }
            }

            $returnType = $method->getReturnType();
            if ($returnType instanceof \ReflectionUnionType) {
                $returnTypes = $returnType->getTypes();
            } else {
                $returnTypes = [$returnType];
            }

            foreach ($returnTypes as $returnType) {
                if (null === $returnType || $returnType->allowsNull()) {
                    throw new \RuntimeException(\sprintf(
                        'The returnType of function %s is weird and breaks our system. Use the %s attribute if you can not specify a return type or it is ambiguous.',
                        $method->getName(),
                        ServiceDefinition::class
                    ));
                }

                if ($returnType->isBuiltin()) {
                    continue;
                }

                $serviceFactoryMap = $this->addToServiceFactoryMap($serviceFactoryMap, $returnType->getName(), $method->getName());
            }
        }

        return $serviceFactoryMap;
    }

    private function addToServiceFactoryMap(array $serviceFactoryMap, string $serviceId, string $methodName): array
    {
        if (\array_key_exists($serviceId, $serviceFactoryMap)) {
            throw new \RuntimeException(\sprintf(
                'Duplicate service id in service factory detected. Method %s has the same return type like method %s. Type is %s. Use the %s attribute if you need to register multiple instances of the same type.',
                $methodName,
                $serviceFactoryMap[$serviceId],
                $serviceId,
                ServiceDefinition::class
            ));
        }

        $serviceFactoryMap[$serviceId] = $methodName;

        return $serviceFactoryMap;
    }
}
