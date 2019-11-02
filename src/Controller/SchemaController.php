<?php

namespace JsonApi\Controller;

use JsonApi\Metadata\RegisterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package JsonApi\Controller
 */
class SchemaController
{
    /**
     * @var RegisterInterface
     */
    private $register;
    /**
     * @var string
     */
    private $path;

    /**
     * @param RegisterInterface $register
     * @param string $path
     */
    public function __construct(RegisterInterface $register, string $path)
    {
        $this->register = $register;
        $this->path = $path;
    }

    /**
     * @return Response
     */
    public function schema()
    {
        $data = [
            'default' => [
                'path' => $this->path,
                'register' => $this->register,
            ],
        ];
        $data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $data = "window.JSON_API_SCHEMA={$data};";
        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/javascript',
        ]);
    }
}
