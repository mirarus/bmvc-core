<?php

use BMVC\Libs\{FS, Util};

FS::setPath(__DIR__);

/**
 * @phpstan-ignore-next-line
 */
function url()
{
	return Util::url(...func_get_args());
}

/**
 * @phpstan-ignore-next-line
 */
function pr()
{
	return Util::pr(...func_get_args());
}

/**
 * @phpstan-ignore-next-line
 */
function dump()
{
	return Util::dump(...func_get_args());
}

/**
 * @phpstan-ignore-next-line
 */
function redirect()
{
	return Util::redirect(...func_get_args());
}

/**
 * @phpstan-ignore-next-line
 */
function refresh()
{
	return Util::refresh(...func_get_args());
}