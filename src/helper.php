<?php

use BMVC\Libs\FS;

FS::setPath(__DIR__);

function iController()
{
  return \BMVC\Core\Controller::import(...func_get_args());
}

function iMiddleware()
{
  return \BMVC\Core\Middleware::import(...func_get_args());
}