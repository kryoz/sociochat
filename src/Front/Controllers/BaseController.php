<?php

namespace Front\Controllers;

use Silex\Application;

abstract class BaseController
{
    /**
     * @var Application
     */
    protected $app;

    public function injectApp(Application $app)
    {
        $this->app = $app;
    }
}