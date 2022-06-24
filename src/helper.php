<?php

use BMVC\Libs\FS;

FS::setPath(__DIR__);

function locales()
{
  return \BMVC\Core\App::locales(...func_get_args());
}