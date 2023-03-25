<?php

use BMVC\Libs\FS;

FS::setPath(__DIR__);

function locales()
{
  return \BMVC\Core\App::locales(...func_get_args());
}

function iController()
{
  return \BMVC\Core\Controller::import(...func_get_args());
}

function iMiddleware()
{
  return \BMVC\Core\Middleware::import(...func_get_args());
}

function iModel()
{
  return \BMVC\Core\Model::import(...func_get_args());
}