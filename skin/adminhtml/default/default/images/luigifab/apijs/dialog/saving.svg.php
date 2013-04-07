<?php
/**
 * Created S/16/02/2013
 * Updated V/05/04/2013
 * Version 7
 *
 * Copyright 2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * http://www.luigifab.info/apijs
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

date_default_timezone_set('UTC');
header('Pragma: public');
header('Cache-Control: public');
header('Content-Type: image/svg+xml; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+1 week')).' GMT');

$width = (isset($_GET['w'])) ? $_GET['w'].'px' : '100%';
$height = (isset($_GET['h'])) ? $_GET['h'].'px' : '100%';

?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="<?php echo $width ?>" height="<?php echo $height ?>" xmlns="http://www.w3.org/2000/svg" version="1.1">
	<circle cx="50%" cy="50%" r="5%" fill="#222" />
	<circle cx="50%" cy="50%" r="40%" stroke="#222" stroke-opacity="0" fill="none" stroke-width="10%">
		<animate attributeName="r" dur="0.9s" begin="0.1s;a.end+0.7s" from="2%" to="40%" id="a" />
		<animate attributeName="stroke-opacity" dur="0.9s" begin="0.1s;b.end+0.7s" from="1" to="0" id="b" />
	</circle>
	<circle cx="50%" cy="50%" r="40%" stroke="#222" stroke-opacity="0" fill="none" stroke-width="10%">
		<animate attributeName="r" dur="0.9s" begin="0.55s;c.end+0.7s" from="2%" to="40%" id="c" />
		<animate attributeName="stroke-opacity" dur="0.9s" begin="0.55s;d.end+0.7s" from="1" to="0" id="d" />
	</circle>
</svg>