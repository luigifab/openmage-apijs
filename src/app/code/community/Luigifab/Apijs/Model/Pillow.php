<?php
/**
 * Created S/09/05/2020
 * Updated S/04/07/2020
 *
 * Copyright 2008-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Model_Pillow extends Varien_Image {

	// singleton
	protected $_quality = 100;
	protected $_pids = [];
	protected $_core = 1;
	protected $_python;

	public function __construct($file = null, $adapter = null) {

		exec('command -v python3 || command -v python || command -v python2', $cmd);
		$this->_python = trim(implode($cmd));

		exec('nproc', $core);
		$this->_core = max(1, (int) trim(implode($core)));
	}

	public function __destruct() {

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

	public function getProgramVersions() {

		$cmd = $this->_python;

		if (strpos($cmd, ':') !== false) { // pas de mb_strpos
			$pyt = 'not available';
			$pil = $pyt;
		}
		else {
			exec($cmd.' --version 2>&1', $pyt);
			$pyt = str_replace('Python ', '', trim(implode($pyt)));

			exec($cmd.' -c "from PIL import Image; print(Image.__version__)" 2>&1', $pil);
			$pil = trim(implode($pil));

			if (mb_stripos($pil, 'o module named') !== false)
				$pil = 'not available';
			else if (mb_stripos($pil, '__version__') !== false)
				$pil = 'available';
		}

		return sprintf('python %s / python-pil %s / %d cpu', $pyt, $pil, $this->_core);
	}

	public function open() {

		if (!is_file($this->_fileName))
			Mage::throwException('File '.$this->_fileName.' does not exists.');

		return $this;
	}

	public function save($destination = null, $newFileName = null) {

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
				empty($this->_resizeWidth) ? 0 : $this->_resizeWidth,
				empty($this->_resizeHeight) ? 0 : $this->_resizeHeight,
				// uniquement pour JPEG (ignoré et toujouts à 9 pour PNG, inutile pour GIF)
				$this->_quality,
				empty($this->_resizeFixed) ? '' : 'fixed'
			);

			$this->_pids[] = exec($cmd);
			Mage::log($cmd, Zend_Log::INFO, 'apijs.log');

			$this->reset();
		}
		catch (Exception $e) {
			Mage::logException($e);
			throw $e;
		}

		return $this;
	}

	public function display() {

	}

	// simple getter

	public function getOriginalWidth() {

		if (empty($this->_imagesize)) {
			$this->open();
			$this->_imagesize = getimagesize($this->_fileName);
		}

		return $this->_imagesize[0];
	}

	public function getOriginalHeight() {

		if (empty($this->_imagesize)) {
			$this->open();
			$this->_imagesize = getimagesize($this->_fileName);
		}

		return $this->_imagesize[1];
	}

	public function getMimeType() {

		if (empty($this->_imagesize)) {
			$this->open();
			$this->_imagesize = getimagesize($this->_fileName);
		}

		return $this->_imagesize[2];
	}

	// simple setter

	public function rotate($angle) {
		$this->_rotateAngle = $angle;
		return $this;
	}

	public function resize($width, $height = null) {
		$this->_resizeWidth  = $width;
		$this->_resizeHeight = $height;
		return $this;
	}

	public function crop(int $top = 0, int $left = 0, int $right = 0, int $bottom = 0) {
		$this->_cropTop = $top;
		$this->_cropLeft = $left;
		$this->_cropRight = $right;
		$this->_cropBottom = $bottom;
		return $this;
	}

	public function watermark($image, int $positionX = 0, int $positionY = 0, int $opacity = 30, bool $repeat = false) {
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
		$this->_quality = null;
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
		$this->_resizeFixed = null;
		return $this;
	}
}