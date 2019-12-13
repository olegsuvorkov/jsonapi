<?php

namespace JsonApi\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use JsonApi\Transformer\InvalidArgumentException;
use JsonApi\Transformer\TransformerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @package JsonApi\Metadata\Field
 */
class Field implements FieldInterface, \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $serializeName;

    /**
     * @var bool
     */
    private $context = false;

    /**
     * @var string|null
     */
    private $getter;

    /**
     * @var string|null
     */
    private $setter;

    /**
     * @var string
     */
    private $type;

    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var MetadataContainerInterface
     */
    private $container;

    /**
     * @var int|null
     */
    private $associationType = null;

    /**
     * @param string $name
     * @param bool $context
     * @param string|null $getter
     * @param string|null $setter
     */
    public function __construct(string $name, bool $context, ?string $getter, ?string $setter)
    {
        $this->name          = $name;
        $this->serializeName = $name;
        $this->context       = $context;
        $this->getter        = $getter;
        $this->setter        = $setter;
    }

    /**
     * @inheritDoc
     */
    public function initialize(MetadataInterface $metadata, MetadataContainerInterface $container): void
    {
        $this->metadata = $metadata;
        $this->container = $container;
        $this->transformer = $container->getTransformer($this->type);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSerializeName(): string
    {
        return $this->serializeName;
    }

    /**
     * @param string $serializeName
     * @return void
     */
    public function setSerializeName(string $serializeName): void
    {
        $this->serializeName = $serializeName;
    }

    /**
     * @return string|null
     */
    public function getGetter(): ?string
    {
        return $this->getter;
    }

    /**
     * @return string|null
     */
    public function getSetter(): ?string
    {
        return $this->setter;
    }

    public function getValue($object)
    {
        return $object->{$this->getter}();
    }

    /**
     * @param $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getNormalizeValue($object)
    {
        $value = $this->getValue($object);
        if ($value !== null) {
            try {
                $value = $this->transformer->transform($value, $this->options);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(sprintf('Invalid normalize `%s`', $this->name), $e->getCode(), $e);
            }
        }
        return $value;
    }

    public function reverseTransform(array $data)
    {
        try {
            return $this->transformer->reverseTransform($data, $this->options);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(sprintf('Invalid reverseTransform in field `%s`', $this->name), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array $data, array $options)
    {
        if (array_key_exists($this->serializeName, $data)) {
            try {
                return $this->transformer->reverseTransform($data[$this->serializeName], $this->options);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(sprintf('Invalid denormalize in field `%s`', $this->name), 0, $e);
            }
        } else {
            throw new InvalidArgumentException(sprintf('Invalid denormalize in field `%s`', $this->name));
        }
    }

    public function setDenormalizeValue($object, array $data, array $options): void
    {
        if ($this->setter !== null && array_key_exists($this->serializeName, $data)) {
            try {
                $value = $data[$this->serializeName];
                if ($value !== null) {
                    $value = $this->transformer->reverseTransform($value, array_merge($options, $this->options, [
                        'data' => $this->getValue($object),
                    ]));
                }
                $object->{$this->setter}($value);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(
                    sprintf('Invalid setDenormalizeValue in field `%s`', $this->name),
                    0,
                    $e
                );
            }
        }
    }

    /**
     * @param array $data
     * @param array $ids
     */
    public function parseScalarValue(array &$data, array &$ids)
    {
        $data[$this->name] = $this->transformer->reverseTransformScalar($ids, $this->options);
    }

    /**
     * @param $object
     * @return mixed|string
     * @throws InvalidArgumentException
     */
    public function getScalarValue($object)
    {
        $value = $this->getValue($object);
        if ($value !== null) {
            $value = $this->transformer->transformScalar($value, $this->options);
        }
        return $value;
    }

    /**
     * @param TransformerInterface $transformer
     */
    public function setTransformer(TransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
        $this->type = $transformer->getType();
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function setOption(string $name, $value)
    {
        return $this->options[$name] = $value;
    }

    public function inContext(?array &$fields): bool
    {
        if ($this->getter === null) {
            return false;
        } elseif ($fields === null) {
            return !$this->context;
        } elseif (false !== ($key = array_search($this->serializeName, $fields))) {
            unset($fields[$key]);
            return true;
        } else {
            return false;
        }
    }

    public function isRelationship(): bool
    {
        return $this->metadata->containsRelationship($this);
    }

    private function getAssociationType(): int
    {
        if ($this->associationType === null) {
            try {
                $type = $this->metadata->getClassMetadata()->getAssociationMapping($this->name)['type'];
            } catch (MappingException $e) {
                $type = 0;
            }
            $this->associationType = $type;
        }
        return $this->associationType;
    }

    /**
     * @inheritDoc
     */
    public function isManyToMany(): bool
    {
        return $this->getAssociationType() === ClassMetadata::MANY_TO_MANY;
    }

    /**
     * @inheritDoc
     */
    public function isOneToMany(): bool
    {
        return $this->getAssociationType() === ClassMetadata::ONE_TO_MANY;
    }

    /**
     * @inheritDoc
     */
    public function isManyToOne(): bool
    {
        return $this->getAssociationType() === ClassMetadata::MANY_TO_ONE;
    }

    /**
     * @inheritDoc
     */
    public function isOneToOne(): bool
    {
        return $this->getAssociationType() === ClassMetadata::ONE_TO_ONE;
    }

    /**
     * @inheritDoc
     */
    public function isToMany(): bool
    {
        return 0 !== ($this->getAssociationType() & ClassMetadata::TO_MANY);
    }

    /**
     * @inheritDoc
     */
    public function isToOne(): bool
    {
        return 0 !== ($this->getAssociationType() & ClassMetadata::TO_ONE);
    }

    /**
     * @inheritDoc
     */
    public function getTargetMetadata(): MetadataInterface
    {
        /** @var MetadataInterface $target */
        $target = $this->options['target'] ?? null;
        if ($target) {
            return $target;
        }
        throw new InvalidArgumentException();
    }

    /**
     * @inheritDoc
     */
    public function isGranted(string $attribute, $subject = null): bool
    {
        return $this->getTargetMetadata()->isGranted($attribute, $subject);
    }

    /**
     * @inheritDoc
     */
    public function denyAccessUnlessGranted(string $attribute, $subject = null): void
    {
        $this->getTargetMetadata()->denyAccessUnlessGranted($attribute, $subject);
    }

    /**
     * @inheritDoc
     */
    public function generateRelationshipUrl($entity): string
    {
        return $this->container->generateRelationshipUrl(
            $this->metadata->getType(),
            $this->metadata->getId($entity),
            $this->serializeName
        );
    }

    /**
     * @inheritDoc
     */
    public function __sleep()
    {
        return [
            'name',
            'serializeName',
            'context',
            'getter',
            'setter',
            'type',
            'options'
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        try {
            return [
                'name' => $this->serializeName,
                'context' => $this->context,
                'options' => $this->transformer->serializeOptions($this->options),
            ];
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Undefined relationship for `{$this->name}`");
        }
    }
}
