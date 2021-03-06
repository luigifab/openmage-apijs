<?php
/**
 * Created S/09/05/2020
 * Updated V/02/07/2021
 *
 * Copyright 2008-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/apijs
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

class Luigifab_Apijs_Model_Python extends Varien_Image {

	// singleton
	protected $_python;
	protected $_quality = 100;
	protected $_files = [];
	protected $_pids  = [];
	protected $_core  = 1;
	protected $_svg;

	public function __construct($file = null, $adapter = null) {

		exec('command -v python3', $cmd);
		$this->_python = trim(implode($cmd));

		exec('nproc', $core);
		$this->_core = max(1, (int) trim(implode($core)));
	}

	public function __destruct() {
		$this->waitThreads();
	}

	public function getProgramVersions($helpPil, $helpSco) {

		$cmd = $this->_python;

		if (empty($cmd)) {
			$pyt = 'not found';
			$pil = $pyt;
			$sco = $pyt;
		}
		else {
			exec($cmd.' --version 2>&1', $pyt);
			$pyt = trim(str_replace('Python', '', implode($pyt)));
			$pyt = implode('.', array_slice(preg_split('#\D#', $pyt), 0, 3));

			exec($cmd.' -c "from PIL import Image; print(Image.__version__)" 2>&1', $pil);
			$pil = trim(implode($pil));

			if (mb_stripos($pil, 'o module named') !== false)
				$pil = 'not available';
			else if (mb_stripos($pil, '__version__') !== false)
				$pil = 'available';
			else
				$pil = implode('.', array_slice(preg_split('#\D#', $pil), 0, 3));

			exec($cmd.' -c "import scour; print(scour.__version__)" 2>&1', $sco);
			$sco = trim(implode($sco));

			if (mb_stripos($sco, 'o module named') !== false)
				$sco = 'not available';
			else if (mb_stripos($sco, '__version__') !== false)
				$sco = 'available';
			else
				$sco = implode('.', array_slice(preg_split('#\D#', $sco), 0, 3));
		}

		return sprintf('python %s / python-pil %s %s / python-scour %s %s / %d cpu', $pyt, $pil, $helpPil, $sco, $helpSco, $this->_core);
	}

	public function open() {

		if (!is_file($this->_fileName))
			Mage::throwException('File '.$this->_fileName.' does not exists.');

		return $this;
	}

	public function save($destination = null, $newFilename = null) {

		$this->open();

		try {
			$core = max(1, $this->_core - 1); // pour laisser 1 coeur de libre
			while (count($this->_pids) >= $core) {
				foreach ($this->_pids as $key => $pid) {
					if (!file_exists('/proc/'.$pid))
						unset($this->_pids[$key]);
					else
						clearstatcache('/proc/'.$pid);
				}
				sleep(0.1);
			}

			$cmd = sprintf('%s %s %s %s %d %d %d %s >/dev/null 2>&1 & echo $!',
				$this->_python,
				str_replace('Apijs/etc', 'Apijs/lib/image.py', Mage::getModuleDir('etc', 'Luigifab_Apijs')),
				escapeshellarg($this->_fileName),
				escapeshellarg($destination),
				empty($this->_resizeFixed) ?
					(empty($this->_resizeWidth) ? 0 : $this->_resizeWidth) :
					(empty($this->_resizeWidth) ? (empty($this->_resizeHeight) ? 0 : $this->_resizeHeight) : $this->_resizeWidth),
				empty($this->_resizeFixed) ?
					(empty($this->_resizeHeight) ? 0 : $this->_resizeHeight) :
					(empty($this->_resizeHeight) ? (empty($this->_resizeWidth) ? 0 : $this->_resizeWidth) : $this->_resizeHeight),
				// uniquement pour JPEG (ignoré et toujouts à 9 pour PNG, inutile pour GIF)
				$this->_quality,
				empty($this->_resizeFixed) ? '' : 'fixed'
			);

			// ne génère pas deux fois la même image
			if (!in_array($destination, $this->_files)) {
				Mage::log($cmd, Zend_Log::DEBUG, 'apijs.log');
				$this->_files[] = $destination;
				$this->_pids[]  = exec($cmd);
			}

			$this->reset();
		}
		catch (Throwable $t) {
			Mage::logException($t);
			throw $t;
		}

		return $this;
	}

	public function waitThreads() {

		while (!empty($this->_pids)) {
			foreach ($this->_pids as $key => $pid) {
				if (!file_exists('/proc/'.$pid))
					unset($this->_pids[$key]);
				else
					clearstatcache('/proc/'.$pid);
			}
			sleep(0.1);
		}
	}

	public function display() {

	}

	// getter

	public function getOriginalWidth() {

		if ($this->isSvg())
			return 0;

		if (empty($this->_imagesize)) {
			$this->open();
			$this->_imagesize = getimagesize($this->_fileName);
		}

		return $this->_imagesize[0];
	}

	public function getOriginalHeight() {

		if ($this->isSvg())
			return 0;

		if (empty($this->_imagesize)) {
			$this->open();
			$this->_imagesize = getimagesize($this->_fileName);
		}

		return $this->_imagesize[1];
	}

	public function getMimeType() {

		if ($this->isSvg())
			return 'image/svg+xml';

		if (empty($this->_imagesize)) {
			$this->open();
			$this->_imagesize = getimagesize($this->_fileName);
		}

		return $this->_imagesize[2];
	}

	public function isSvg() {

		if (is_null($this->_svg)) {
			$this->open();
			$this->_svg = (mb_substr($this->_fileName, -4) == '.svg') || in_array(mime_content_type($this->_fileName), ['image/svg', 'image/svg+xml']);
		}

		return $this->_svg;
	}

	// setter

	public function rotate($angle) {
		$this->_rotateAngle = $angle;
		return $this;
	}

	public function resize($width, $height = null) {
		$this->_resizeWidth  = $width;
		$this->_resizeHeight = $height;
		return $this;
	}

	public function crop($top = 0, $left = 0, $right = 0, $bottom = 0) {
		$this->_cropTop = $top;
		$this->_cropLeft = $left;
		$this->_cropRight = $right;
		$this->_cropBottom = $bottom;
		return $this;
	}

	public function watermark($image, $positionX = 0, $positionY = 0, $opacity = 30, $repeat = false) {
		if (!is_file($image))
			Mage::throwException('Required file '.$image.' does not exists.');
		$this->_watermarkImage = $watermarkImage;
		$this->_watermarkPositionX = $positionX;
		$this->_watermarkPositionY = $positionY;
		$this->_watermarkOpacity   = $opacity;
		$this->_watermarkRepeat    = $repeat;
		return $this;
	}

	public function quality($value) {
		$this->_quality = $value;
		return $this;
	}

	public function keepAspectRatio($value) {
		$this->_keepAspectRatio = $value;
		return $this;
	}

	public function keepFrame($value) {
		$this->_keepFrame = $value;
		return $this;
	}

	public function keepTransparency($value) {
		$this->_keepTransparency = $value;
		return $this;
	}

	public function constrainOnly($value) {
		$this->_constrainOnly = $value;
		return $this;
	}

	public function backgroundColor($value) {
		$this->_backgroundColor = $value;
		return $this;
	}

	public function setImageBackgroundColor($value) {
		$this->_backgroundColor = $value;
		return $this;
	}

	public function setWatermarkPosition($value) {
		$this->_watermarkPosition = $value;
		return $this;
	}

	public function setWatermarkImageOpacity($value) {
		$this->_watermarkImageOpacity = $value;
		return $this;
	}

	public function setWatermarkWidth($value) {
		$this->_watermarkWidth = $value;
		return $this;
	}

	public function setWatermarkHeigth($value) {
		$this->_watermarkHeigth = $value;
		return $this;
	}

	public function setFilename($value) {
		$this->_fileName = $value;
		$this->_svg = null;
		return $this;
	}

	public function setFixed($value) {
		$this->_resizeFixed = $value;
		return $this;
	}

	public function reset() {
		$this->_rotateAngle = null;
		$this->_resizeWidth = null;
		$this->_resizeHeight = null;
		$this->_cropTop = null;
		$this->_cropLeft = null;
		$this->_cropRight = null;
		$this->_cropBottom = null;
		$this->_watermarkImage = null;
		$this->_watermarkPositionX = null;
		$this->_watermarkPositionY = null;
		$this->_watermarkOpacity = null;
		$this->_watermarkRepeat = null;
		$this->_quality = 100;
		$this->_keepAspectRatio = null;
		$this->_keepFrame = null;
		$this->_keepTransparency = null;
		$this->_constrainOnly = null;
		$this->_backgroundColor = null;
		$this->_watermarkPosition = null;
		$this->_watermarkImageOpacity = null;
		$this->_watermarkWidth = null;
		$this->_watermarkHeigth = null;
		$this->_fileName = null;
		$this->_svg = null;
		$this->_resizeFixed = null;
		return $this;
	}
}