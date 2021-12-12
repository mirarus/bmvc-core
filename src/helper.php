<?php

use BMVC\Libs\{FS, Util};

FS::setPath(__DIR__);

function url()
{
	return Util::url(...func_get_args());
}

function pr()
{
	return Util::pr(...func_get_args());
}

function dump()
{
	return Util::dump(...func_get_args());
}

function redirect()
{
	return Util::redirect(...func_get_args());
}

function refresh()
{
	return Util::refresh(...func_get_args());
}