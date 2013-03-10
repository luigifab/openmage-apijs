<?php
/**
 * Created L/18/02/2013
 * Updated M/26/02/2013
 * Version 6
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

$lang = (isset($_GET['lang'])) ? $_GET['lang'] : 'en';
$width = (isset($_GET['w'])) ? $_GET['w'].'px' : '100%';
$height = (isset($_GET['h'])) ? $_GET['h'].'px' : '100%';

$translations = array();
$translations['en'] = array('numb' => '404', 'text' => 'Error');
$translations['fr'] = array('numb' => '404', 'text' => 'Erreur');

$lang = (!array_key_exists($lang, $translations)) ? 'en' : $lang;
$opacity = ((strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) ? 1 : 0;

?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="<?php echo $width ?>" height="<?php echo $height ?>" xmlns="http://www.w3.org/2000/svg" version="1.1">
	<g transform="rotate(-20, <?php echo intval(intval($width) / 2) ?>, <?php echo intval(intval($height) / 2) ?>)">
		<g font-family="Verdana, sans-serif" font-weight="bold" fill="black" fill-opacity="<?php echo $opacity ?>">
			<text x="38%" y="47%" font-size="<?php echo intval(1.1 * intval($height) / 6) ?>">
				<tspan><?php echo $translations[$lang]['text'] ?></tspan>
				<animate attributeName="x" dur="0.4s" begin="0.5s" from="28%" to="38%" />
			</text>
			<text x="38%" y="64%" font-size="<?php echo intval(1.3 * intval($height) / 6) ?>">
				<tspan><?php echo $translations[$lang]['numb'] ?></tspan>
				<animate attributeName="x" dur="0.4s" begin="0.5s" from="32%" to="38%" />
			</text>
			<animate attributeName="fill-opacity" dur="0.6s" begin="0.7s" from="0" to="1" fill="freeze" />
		</g>
		<g opacity="<?php echo $opacity ?>">
			<line x1="10%" y1="37%" x2="11.75%" y2="39%" stroke="black" stroke-width="6%" stroke-linecap="round" />
			<circle cx="20%" cy="50%" r="16%" fill="black" />
			<line x1="12.5%" y1="42.5%" x2="27.5%" y2="57.5%" stroke="red" stroke-width="7%" stroke-linecap="round" />
			<line x1="27.5%" y1="42.5%" x2="12.5%" y2="57.5%" stroke="red" stroke-width="7%" stroke-linecap="round" />
			<animate attributeName="opacity" dur="0.5s" begin="0.5s" from="0" to="1" fill="freeze" />
		</g>
	</g>
</svg>