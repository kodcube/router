<?php
namespace KodCube\Router;

use Psr\Http\Message\{ ServerRequestInterface, RequestInterface, ResponseInterface };
use KodCube\Invoker\InvokerInterface;
use KodCube\Router\Throwable\NotFound404;
use Psr\Log\{ LoggerInterface, NullLogger};

class Router implements RouterInterface
{

    protected $invoker = null;

    protected $middleware  = null;

    protected $relay = [];

    protected $routeMap = [
        'GET'    => [
            'static'  => [],
            'regex' => []
        ],
        'POST'   => [
            'static'  => [],
            'regex' => []
        ],
        'PUT'    => [
            'static'  => [],
            'regex' => []
        ],
        'FETCH'  => [
            'static'  => [],
            'regex' => []
        ],
        'DELETE' => [
            'static'  => [],
            'regex' => []
        ],
        'OPTIONS' => [
            'static'  => [],
            'regex' => []
        ]
    ];



    public function __construct(array $routes=[],array $middleware = [],InvokerInterface $invoker,LoggerInterface $logger=null)
    {
        $this->middleware = $middleware;
        $this->invoker = $invoker;
        $this->logger = $logger ?? new NullLogger();
        $this->applyRoutes($routes);
    }


    public function __invoke(ServerRequestInterface $request,ResponseInterface $response):ResponseInterface
    {
        // First Run setup relay
        if ( empty($this->middleware) ) {
            // Add global middleware
            $middleware = $this->getMiddleware();

        }
        list($middleware,$request) = $this->getController($request);

        $this->relay = array_merge($this->relay,$middleware);

        // Call Middleware
        $middleware = array_shift($this->relay);
        return $this->invoker->invoke($middleware,[$request,$response,$this]);
    }
    
    protected function getMiddleware():array
    {
        return $this->middleware;
    }

    protected function getController(RequestInterface $request):array
    {

        $method = strtoupper($request->getMethod());
        
        $uri = trim($request->getUri()->getPath(),'/');

        // Check for simple static uri match

        if ( $config = $this->getRoute($method,'static',$uri) ) {

            if ( is_string($config) ) return [[$config],$request];

            if ( ! isset($config['callable']) ) throw new \Exception('Missing Controller Class Name for '.$method.'::'.$uri);

            $middleware = [];

            if ( isset($config['middleware']) ) {
                $middleware = $config['middleware'];
                unset($config['middleware']);
            }

            $middleware[] = $config['callable'];

            unset($config['callable']);

            $queryParams = array_merge($config,$request->getQueryParams());

            
            return [$middleware,$request->withQueryParams($queryParams)];
        }
        // Check if cache has been set

        $hasCache = isset($this->cache);

        $keys = explode('/',$uri);
        $routes = $this->getRoutes($method,'regex',...$keys);
        if (empty($routes)) throw new NotFound404('Not Found',404);

        // Check to dynamic/regex uri match
        foreach ($routes AS $pattern => $config ) {

            if ( preg_match('~^'.$pattern.'$~i',$uri,$matches) )
            {

                array_shift($matches); // Remove the Matched String
             
                $queryParams = $request->getQueryParams();

                // Match Variables from Regex to Variable Names
                foreach($matches AS $i=>$value) {
                    if (isset($config['fields'][$i])) $queryParams[$config['fields'][$i]] = $value;
                }
                unset($config['fields']);

                // Build Route specific middleware
                $middleware = [];
                if ( isset($config['middleware']) ) {
                    $middleware = $config['middleware'];
                    unset($config['middleware']);
                }

                // Add Route Controller
                $middleware[] = $config['callable'];
                unset($config['callable']);

                // Merge any additionally passed params into query params
                if (!empty($config)) {
                    $queryParams = array_merge($config, $queryParams);
                }

                // Build Returned result
                $result = [$middleware,$request->withQueryParams($queryParams)];

                return $result;
            }
        }
        
        // TODO: Check if they have just used the wrong method & return 405
        foreach ($this->routeMap AS $method=>$types) {
            foreach ($types AS $type=>$routes) {
                if (isset($routes[$uri])) {
                    throw new NotFound404('The request method must be '.$method.' when requesting an '.$uri,405);
                }
            }
        }
        throw new NotFound404('Not Found',404);
    }

    protected function applyRoutes(array $routes) 
    {
        foreach ($routes AS $route=>$params) {

            $methodKeys = array_keys($this->routeMap);

            if ( is_string($params) ) {
                $this->routeMap['GET']['static'][$route] = $params;
                continue;
            }
            $methods = array_intersect_key($params,array_flip($methodKeys));
            $params = array_diff_key($params,array_flip($methodKeys));

            foreach ($methods AS $method=>$callable) {

                if ( isset($params['fields']) ) {

                    // Find First Instance of Regex
                    $regexPos = strpos($route,'(');
                    // Get URI prefix before Regex
                    $routePrefix = substr($route,0,$regexPos);
                    // Explode into path parts
                    $keys = explode('/',$routePrefix);
                    // Remove Last Element of Keys Array
                    array_pop($keys);

                    $routeMap = &$this->routeMap[$method]['regex'];
                    foreach ($keys AS $name) {
                        if (strpos($name,'(') !== false) {
                            $routeMap[implode('/',$keys)] = $value;
                            break;
                        }

                        if ( isset( $routeMap[$name] ) ) {
                            $routeMap = &$routeMap[$name];
                            continue;
                        }

                        $routeMap[$name] = [];
                        $routeMap = &$routeMap[$name];
                    }

                    $routeMap[$route] = array_merge(['callable' => $callable],$params);

                    continue;
                }
                
                $this->routeMap[$method]['static'][$route] = array_merge(['callable' => $callable],$params);
            }
        }
    }


    protected function getRoute(...$args)
    {
        $cfg = $this->routeMap;

        foreach ($args AS $name) {

            if ( ! isset( $cfg[$name] ) ) return false;

            $cfg = $cfg[$name];

        }
        return $cfg;
    }

    protected function getRoutes(...$args)
    {
        $cfg = $this->routeMap;

        foreach ($args AS $name) {

            if ( ! isset( $cfg[$name] ) ) return $cfg;

            $cfg = $cfg[$name];
        }
        return $cfg;
    }


}