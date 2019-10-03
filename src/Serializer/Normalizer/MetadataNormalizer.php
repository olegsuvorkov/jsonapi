<?php

namespace JsonApi\Serializer\Normalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use JsonApi\Metadata\RegisterInterface;
use JsonApi\Metadata\UndefinedMetadataException;
use JsonApi\Serializer\Encoder\JsonVndApiEncoder;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetadataNormalizer implements NormalizerInterface
{
    /**
     * @var RegisterInterface
     */
    private $register;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(RegisterInterface $register, ManagerRegistry $managerRegistry)
    {
        $this->register = $register;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $metadata = $this->register->getByClass(get_class($object));
        return ['asdf' => 1];
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        if ($format === JsonVndApiEncoder::FORMAT) {
            try {
                $this->register->getByClass(get_class($data));
                return true;
            } catch (UndefinedMetadataException $e) {
            }
        }
        return false;
    }
}
