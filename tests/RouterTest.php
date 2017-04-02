<?php

use KodCube\Router\{ Router, MiddlewareInterface };
use KodCube\Invoker\Invoker;
use Psr\Http\Message\{ RequestInterface,ResponseInterface };
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\{ ServerRequestFactory, Response,Response\TextResponse };

class RouterTest extends TestCase
{

/*****************************************************************************************************
    Router Construction
******************************************************************************************************/

    /**
     * Test Missing Invoker
     * @test
     * @expectedException TypeError
     */
    public function missingInvoker()
    {
        $r = new Router();
        $this->assertTrue(true);

    }

    /**
     * Test Successful Constructor
     * @test
     */
    public function constructor()
    {
        $r = new Router([],[],new Invoker());
        $this->assertTrue(true);

    }

    /**
     * Test Not Found Exception when route not found
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function notFound()
    {
        $r = new Router([],[],new Invoker());

        $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [ 'REQUEST_URI' => '/test'],
                    $_SERVER
                )
            ),
            $this->createMock(ResponseInterface::class)
        );
    }

    /**
     * Test Static Route
     * @test
     */
    public function staticRouteFound()
    {
        $r = new Router(
            [
                'test' => TestController::class,
                'level1/level2' => TestController::class

            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [ 'REQUEST_URI' => '/test'],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame($response,$result);
    }

    /**
     * Test Multi Level Static Route
     * @test
     */
    public function multiLevelStaticRouteFound()
    {
        $r = new Router(
            [
                'level1/level2' => TestController::class

            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [ 'REQUEST_URI' => '/level1/level2'],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame($response,$result);
    }

    /**
     * Test Sub Level Static Route
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function subLevelStaticRouteNotFound()
    {
        $r = new Router(
            [
                'level1/level2' => TestController::class
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [ 'REQUEST_URI' => '/level1'],
                    $_SERVER
                )
            ),
            $response
        );
    }

    /**
     * Test Controller Not Found
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function controllerNotFound()
    {
        $r = new Router(
            [
                '/test' => DoesNotExistController::class

            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [ 'REQUEST_URI' => '/test'],
                    $_SERVER
                )
            ),
            $response
        );
    }

    /**
     * Test Get Request Found
     * @test
     */
    public function getRequestFound()
    {
        $r = new Router(
            [
                'test' => [
                    'GET' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [ 'REQUEST_URI' => '/test'],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame($response,$result);
    }

    /**
     * Test GET Request Not Found
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function getRequestNotFound()
    {
        $r = new Router(
            [
                'test' => [
                    'POST' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [ 'REQUEST_URI' => '/test'],
                    $_SERVER
                )
            ),
            $response
        );
    }

    /**
     * Test POST Request Found
     * @test
     */
    public function postRequestFound()
    {
        $r = new Router(
            [
                'test' => [
                    'POST' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'POST'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame($response,$result);
    }

    /**
     * Test POST Request Not Found
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function postRequestNotFound()
    {
        $r = new Router(
            [
                'test' => [
                    'GET' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'POST'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
    }

    /**
     * Test PUT Request Found
     * @test
     */
    public function putRequestFound()
    {
        $r = new Router(
            [
                'test' => [
                    'PUT' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'PUT'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame($response,$result);
    }

    /**
     * Test PUT Request Not Found
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function putRequestNotFound()
    {
        $r = new Router(
            [
                'test' => [
                    'GET' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'PUT'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
    }

    /**
     * Test DELETE Request Found
     * @test
     */
    public function deleteRequestMatch()
    {
        $r = new Router(
            [
                'test' => [
                    'DELETE' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'DELETE'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame($response,$result);
    }

    /**
     * Test DELETE Request Not Found
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function deleteRequestNoMatch()
    {
        $r = new Router(
            [
                'test' => [
                    'GET' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'DELETE'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
    }

    /**
     * Test OPTIONS Request Found
     * @test
     */
    public function optionsRequestMatch()
    {
        $r = new Router(
            [
                'test' => [
                    'OPTIONS' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'OPTIONS'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame($response,$result);
    }

    /**
     * Test OPTIONS Request Not Found
     * @test
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function optionsRequestNoMatch()
    {
        $r = new Router(
            [
                'test' => [
                    'GET' => TestController::class
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = $this->createMock(ResponseInterface::class);
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test',
                        'REQUEST_METHOD' => 'OPTIONS'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
    }

    /**
     * Test Simple Regex Request Found
     * @test
     */
    public function simpleRegexMatch()
    {
        $r = new Router(
            [
                'test/([\d+])' => [
                    'GET'    => TextController::class,
                    'fields' => ['id']
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = new Response();
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test/2'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
        $this->assertSame('{"id":"2"}',$result->getBody()->getContents());
    }

    /**
     * Test Simple Regex Request Found
     * @test
     * @covers Router::__invoke
     * @expectedException KodCube\Router\Throwable\NotFound404
     */
    public function simpleRegexNoMatch()
    {
        $r = new Router(
            [
                'test/([\d+])' => [
                    'GET'    => TextController::class,
                    'fields' => ['id']
                ]
            ],
            [], // Middleware
            new Invoker() // Invoker
        );
        $response = new Response();
        $result = $r(
            ServerRequestFactory::fromGlobals(
                array_merge(
                    [
                        'REQUEST_URI' => '/test/a'
                    ],
                    $_SERVER
                )
            ),
            $response
        );
    }

}



class TestController implements MiddlewareInterface
{
    public function __invoke( RequestInterface $request,ResponseInterface $response,Callable $next = null ):ResponseInterface
    {
        return $response;
    }
}
class TextController implements MiddlewareInterface
{
    public function __invoke( RequestInterface $request,ResponseInterface $response,Callable $next = null ):ResponseInterface
    {
        return new TextResponse(
            json_encode( $request->getQueryParams() )
        );
    }
}
