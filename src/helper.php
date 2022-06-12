<?php

use BMVC\Libs\{FS, Route, Util};

FS::setPath(__DIR__);

/**
 * @return null|string
 */
function url(): ?string
{
  return Util::url(...func_get_args());
}

/**
 * @return null|string
 */
function page(): ?string
{
  return Util::page_url();
}

/**
 * @return void
 */
function pr()
{
  return Util::pr(...func_get_args());
}

/**
 * @return void
 */
function dump()
{
  return Util::dump(...func_get_args());
}

/**
 * @return void
 */
function redirect()
{
  return Util::redirect(...func_get_args());
}

/**
 * @return void
 */
function refresh()
{
  return Util::refresh(...func_get_args());
}

/**
 * @return void
 */
function getErrors()
{
  return Route::getErrors(...func_get_args());
}

function getViewData()
{
  return \BMVC\Core\View::getData(...func_get_args());
}

function locales()
{
  return \BMVC\Core\App::locales(...func_get_args());
}

if (!function_exists('_')) {
  function _()
  {
    return func_get_arg(0);
  }
}