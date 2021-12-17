<?php

use BMVC\Libs\{FS, Util};

FS::setPath(__DIR__);

/**
 * @return null|string
 */
function url(): ?string
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