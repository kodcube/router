<?php
namespace KodCube\Router;

use Psr\Http\Message\{ RequestInterface, ResponseInterface };

interface MiddlewareInterface
{
    public function __invoke( RequestInterface $request,ResponseInterface $response,Callable $next = null ):ResponseInterface;
}