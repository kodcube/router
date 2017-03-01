<?php
namespace KodCube\Router\Exception;

use Exception;

class NotFound404 extends Exception
{   
    protected $code = 404;
}