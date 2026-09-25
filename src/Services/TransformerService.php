<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Services;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\TransformerAbstract;

final class TransformerService
{
    private Manager $fractal;

    public function __construct()
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new DataArraySerializer());
        $serializer = $_ENV['FRACTAL_SERIALIZER'] ?? 'data_array';
        if ($serializer === 'json_api') {
            $this->fractal->setSerializer(new JsonApiSerializer());
        } else {
            $this->fractal->setSerializer(new DataArraySerializer());
        }
    }

    /**
     * @param list<mixed> $items
     * @return list<array<string, mixed>>
     */
    public function transformCollection(array $items, TransformerAbstract $transformer): array
    {
        $resource = new Collection($items, $transformer);
        $result = $this->fractal->createData($resource)->toArray();

        if (!is_array($result)) {
            return [];
        }

        return array_values($result);
    }

    /**
     * Transform collection and return JSON string and content type depending on serializer.
     *
     * @param list<mixed> $items
     * @return array{body: string, contentType: string}
     */
    public function transformCollectionJson(array $items, TransformerAbstract $transformer): array
    {
        $resource = new Collection($items, $transformer);
        $data = $this->fractal->createData($resource);
        $body = json_encode($data->toArray(), JSON_THROW_ON_ERROR);

        $contentType = 'application/json';
        if ($this->fractal->getSerializer() instanceof JsonApiSerializer) {
            $contentType = 'application/vnd.api+json';
        }

        return ['body' => $body, 'contentType' => $contentType];
    }
}
