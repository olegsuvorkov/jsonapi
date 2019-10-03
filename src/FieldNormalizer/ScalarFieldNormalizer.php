<?php


namespace JsonApi\FieldNormalizer;


use JsonApi\Metadata\FieldInterface;

class ScalarFieldNormalizer implements ConfigureFieldNormalizerInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $check;

    /**
     * @var string
     */
    private $transform;

    public function __construct(string $type, string $check, string $transform)
    {
        $this->type = $type;
        $this->check = $check;
        $this->transform = $transform;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function normalize(FieldInterface $field, $data)
    {
        if (($this->check)($data)) {
            return ($this->transform)($data);
        }
        throw new \Exception();
    }

    /**
     * @inheritDoc
     */
    public function denormalize(FieldInterface $field, $data)
    {
        if (($this->check)($data)) {
            return ($this->transform)($data);
        }
        throw new \Exception();
    }

    public function configureAttribute(FieldInterface $field, array &$parameters): void
    {
        $field->setType($this->type);
    }
}
