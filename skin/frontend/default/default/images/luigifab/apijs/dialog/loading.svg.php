<?php
/**
 * Created S/16/02/2013
 * Updated M/19/02/2013
 * Version 3
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
	<circle cx="25%" cy="50%" r="12%" fill="#DDD">
		<animate attributeName="fill" dur="0.24s" begin="0;e.end+0.65s" from="#DDD" to="#888" fill="freeze" id="a" />
		<animate attributeName="fill" dur="0.24s" begin="a.end+0.2s" from="#888" to="#DDD" fill="freeze" id="b" />
	</circle>
	<circle cx="50%" cy="50%" r="12%" fill="#DDD">
		<animate attributeName="fill" dur="0.24s" begin="b.end-0.2s" from="#DDD" to="#888" fill="freeze" id="c" />
		<animate attributeName="fill" dur="0.24s" begin="c.end+0.2s" from="#888" to="#DDD" fill="freeze" id="d" />
	</circle>
	<circle cx="75%" cy="50%" r="12%" fill="#DDD">
		<animate attributeName="fill" dur="0.24s" begin="d.end-0.2s" from="#DDD" to="#888" fill="freeze" id="e" />
		<animate attributeName="fill" dur="0.24s" begin="e.end+0.2s" from="#888" to="#DDD" fill="freeze" id="f" />
	</circle>
</svg>