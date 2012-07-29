<?php
/**
 * Created L/13/04/2009
 * Updated L/23/04/2012
 * Version 14
 *
 * Copyright 2009-2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>',"\n";

?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" version="1.1" style="background-color:#999; fill:#999;">
	<defs>
		<linearGradient id="deco" x1="0" y1="0" x2="10%" y2="100%">
			<stop offset="0%" stop-color="#333" />
			<stop offset="100%" stop-color="#000" />
		</linearGradient>
	</defs>
	<rect x="0" y="0" width="16.66%" height="100%" fill="url(#deco)" id="bar" />
	<text x="50%" y="48%" font-family="Verdana, sans-serif" text-anchor="middle" dy="25%" font-size="0.65em" fill="white" id="text"> </text>
	<line x1="10%" y1="0" x2="10%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="20%" y1="0" x2="20%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="30%" y1="0" x2="30%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="40%" y1="0" x2="40%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="50%" y1="0" x2="50%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="60%" y1="0" x2="60%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="70%" y1="0" x2="70%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="80%" y1="0" x2="80%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
	<line x1="90%" y1="0" x2="90%" y2="100%" stroke="rgba(255,255,255,0.8)" stroke-width="0.1%" />
</svg>