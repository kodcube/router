<?php
namespace KodCube\Router\Throwable;

use Exception;

class NotFound404 extends Exception
{   
    protected $code = 404;
}