<?php
namespace KodCube\Router;

use Psr\Http\Message\{ ServerRequestInterface, ResponseInterface };

interface RouterInterface
{
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response):ResponseInterface;
}