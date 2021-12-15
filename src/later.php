<?php

/**
 * @param  string $file
 * @return mixed
 */
function _ob(string $file)
{
	ob_start();
	require_once $file;
	$ob_content = ob_get_contents();
	ob_end_clean();
	return $ob_content;
}

/**
 * @param string $file
 */
function ob_template(string $file)
{
	ob_start();
	require_once $file;
	$ob_content = ob_get_contents();
	ob_end_clean();

	$ob_content = preg_replace([
		"/({{ url\(\) }})/i",
		"/({{ url\('(.*?)'\) }})/i",
		"/({{ url\(\"(.*?)\"\) }})/i",
		"/({{ url\((.*?)\) }})/i"
	], url('$2'), $ob_content);

	return $ob_content;
}

/**
 * @param  string|null $class
 * @param  string|null $method
 * @param  array       $params
 * @return mixed
 */
function app(string $class=null, string $method=null, array $params=[])
{
	$std = new \stdClass;
	if ($class) {
		if (isset($method) && !empty($method)) {
			return call_user_func_array([$std->$class, $method], $params);
		} else {
			return $std->$class;
		}
	}
	return $std;
}

/**
 * @param  string $par
 * @return string
 */
function html_decode(string $par): string
{
	return htmlspecialchars_decode(html_entity_decode(htmlspecialchars_decode($par, ENT_QUOTES), ENT_QUOTES), ENT_QUOTES);
}

/**
 * @param string $date
 * @param string $format
 */
function datetotime(string $date, string $format='YYYY-MM-DD')
{
	$year = 0;
	$month = 0;
	$day = 0;

	if ($format == 'YYYY-MM-DD') list($year, $month, $day) = explode('-', $date);
	if ($format == 'YYYY/MM/DD') list($year, $month, $day) = explode('/', $date);
	if ($format == 'YYYY.MM.DD') list($year, $month, $day) = explode('.', $date);

	if ($format == 'DD-MM-YYYY') list($day, $month, $year) = explode('-', $date);
	if ($format == 'DD/MM/YYYY') list($day, $month, $year) = explode('/', $date);
	if ($format == 'DD.MM.YYYY') list($day, $month, $year) = explode('.', $date);

	if ($format == 'MM-DD-YYYY') list($month, $day, $year) = explode('-', $date);
	if ($format == 'MM/DD/YYYY') list($month, $day, $year) = explode('/', $date);
	if ($format == 'MM.DD.YYYY') list($month, $day, $year) = explode('.', $date);

	return mktime(0, 0, 0, $month, $day, $year);
}

function resize_image($file, $w, $h, $crop=false)
{
	list($width, $height) = getimagesize($file);
	$r = $width / $height;
	if ($crop) {
		if ($width > $height) {
			$width = ceil($width-($width * abs($r - $w / $h)));
		} else {
			$height = ceil($height-($height * abs($r - $w / $h)));
		}
		$newwidth = $w;
		$newheight = $h;
	} else {
		if ($w/$h > $r) {
			$newwidth = $h*$r;
			$newheight = $h;
		} else {
			$newheight = $w/$r;
			$newwidth = $w;
		}
	}
	$src = imagecreatefromjpeg($file);
	$dst = imagecreatetruecolor($newwidth, $newheight);
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	return $dst;
}