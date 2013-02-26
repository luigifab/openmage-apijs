<?php
/**
 * Created L/13/04/2009
 * Updated M/19/02/2013
 * Version 15
 *
 * Copyright 2009-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" version="1.1" style="background-color:#AAA; fill:#AAA;">
	<rect x="0" y="0" width="16.66%" height="100%" fill="#FFD200" id="bar" />
	<text x="50%" y="48%" dy="25%" font-family="Verdana, sans-serif" font-size="0.65em" text-anchor="middle" fill="black" id="text"> </text>
</svg>