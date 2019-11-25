<?php

namespace JsonApi\Transformer;

use DateTime;

/**
 * @package JsonApi\Transformer
 */
class DateTimeTransformer implements TransformerInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $format;

    /**
     * @param string $type
     * @param string $format
     */
    public function __construct(string $type, string $format)
    {
        $this->type = $type;
        $this->format = $format;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'datetime';
    }

    /**
     * @inheritDoc
     */
    public function transformScalar($data, array $options)
    {
        return $this->transform($data, $options) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function reverseTransformScalar(array &$ids, array $options)
    {
        $value = current($ids);
        if ($value === false) {
            throw new InvalidArgumentException();
        }
        next($ids);
        if ($value === '') {
            return null;
        } elseif (is_string($value)) {
            return $this->reverseTransform($value, $options);
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @inheritDoc
     */
    public function transform($data, array $options)
    {
        if ($data instanceof DateTime) {
            return $data->format('Y-m-d\TH:i:s');
        } elseif ($data === null) {
            return null;
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($data, array $options)
    {
        if ($data === null) {
            return null;
        } elseif (is_string($data)) {
            return DateTime::createFromFormat('Y-m-d\TH:i:s', $data);
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @inheritDoc
     */
    public function serializeOptions(array $options): array
    {
        return [
            'type' => $this->getType(),
        ];
    }
}