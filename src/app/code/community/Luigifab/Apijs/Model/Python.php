<?php
/**
 * Created S/09/05/2020
 * Updated S/30/12/2023
 *
 * Copyright 2008-2025 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-apijs
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
	protected $_files = [];
	protected $_pids  = [];
	protected $_core  = 0;
	protected $_imageSize = [];
	protected $_isVarienRewrite = false;
	protected $_quality = 100;
	protected $_rotateAngle;
	protected $_resizeWidth;
	protected $_resizeHeight;
	protected $_cropTop;
	protected $_cropLeft;
	protected $_cropRight;
	protected $_cropBottom;
	protected $_watermarkImage;
	protected $_watermarkPositionX;
	protected $_watermarkPositionY;
	protected $_watermarkOpacity;
	protected $_watermarkRepeat;
	protected $_keepAspectRatio;
	protected $_keepFrame;
	protected $_keepTransparency;
	protected $_constrainOnly;
	protected $_backgroundColor;
	protected $_watermarkPosition;
	protected $_watermarkImageOpacity;
	protected $_watermarkWidth;
	protected $_watermarkHeigth;
	protected $_fileName;
	protected $_isSvg;
	protected $_resizeFixed;

	// model
	public function __construct($file = null, $adapter = null) {

		if ($this->_isVarienRewrite && !empty($file))
			$this->setFilename($file);
	}

	public function __destruct() {
		$this->waitThreads();
	}

	public function getProgramVersions($helpPil, $helpSco) {

		if (empty($this->_core)) {
			exec('nproc', $core);
			$this->_core = max(1, (int) trim(implode($core)));
		}

		exec('command -v python3', $cmd);
		$cmd = trim(implode($cmd));

		if (empty($cmd)) {
			$pyt = 'not found';
			$pil = $pyt;
			$sco = $pyt;
		}
		else {
			exec('python3 --version 2>&1', $pyt);
			$pyt = trim(str_replace('Python', '', implode($pyt)));
			$pyt = implode('.', array_slice(preg_split('#\D#', $pyt), 0, 3));

			exec('python3 -c "from PIL import Image; print(Image.__version__)" 2>&1', $pil);
			$pil = trim(implode($pil));

			if (mb_stripos($pil, 'o module named') !== false)
				$pil = 'not available';
			else if (mb_stripos($pil, '__version__') !== false)
				$pil = 'available';
			else
				$pil = implode('.', array_slice(preg_split('#\D#', $pil), 0, 3));

			exec('python3 -c "import scour; print(scour.__version__)" 2>&1', $sco);
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

	public function save($destination = null, $dummy = null, $immediate = false) {

		$this->open();

		if (empty($this->_core)) {
			exec('nproc', $core);
			$this->_core = max(1, (int) trim(implode($core)));
		}

		try {
			// leaves 2 cores free, but because $runs include grep check, we add 2 for $maxc
			// [] => 18:14 0:00 /usr/bin/python3 ...
			// [] => 18:14 0:00 sh -c ps aux | grep Apijs/lib/image.py
			// [] => 18:14 0:00 grep Apijs/lib/image.py
			$maxc = 2 + max(1, $this->_core - 2);

			while (count($this->_pids) >= $maxc) {
				foreach ($this->_pids as $key => $pid) {
					if (file_exists('/proc/'.$pid))
						clearstatcache(true, '/proc/'.$pid);
					else
						unset($this->_pids[$key]);
				}
				usleep(100000); // 0.1 s
			}

			exec('ps aux | grep Apijs/lib/image.py', $runs);
			while (count($runs) >= $maxc) {
				usleep(90000); // 0.09 s
				$runs = [];
				exec('ps aux | grep Apijs/lib/image.py', $runs);
			}

			$dir = Mage::getBaseDir('log');
			if (!is_dir($dir))
				@mkdir($dir, 0755);

			$cmd = sprintf('%s %s %s %s %d %d %d %s >> %s 2>&1'.($immediate ? '' : ' & echo $!'),
				'python3',
				str_replace('Apijs/etc', 'Apijs/lib/image.py', Mage::getModuleDir('etc', 'Luigifab_Apijs')),
				escapeshellarg($this->_fileName),
				escapeshellarg($destination),
				empty($this->_resizeFixed) ?
					(empty($this->_resizeWidth) ? 0 : $this->_resizeWidth) :
					(empty($this->_resizeWidth) ? (empty($this->_resizeHeight) ? 0 : $this->_resizeHeight) : $this->_resizeWidth),
				empty($this->_resizeFixed) ?
					(empty($this->_resizeHeight) ? 0 : $this->_resizeHeight) :
					(empty($this->_resizeHeight) ? (empty($this->_resizeWidth) ? 0 : $this->_resizeWidth) : $this->_resizeHeight),
				// uniquement pour JPEG (ignoré et toujours à 9 pour PNG, inutile pour GIF)
				$this->_quality,
				empty($this->_resizeFixed) ? '' : 'fixed',
				$dir.'/apijs.log'
			);

			// ne génère pas deux fois la même image
			if (!in_array($destination, $this->_files)) {

				//Mage::log(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), Zend_Log::DEBUG, 'apijs.log');
				Mage::log($cmd, Zend_Log::DEBUG, 'apijs.log');

				$this->_files[] = $destination;
				$this->_pids[]  = exec($cmd);

				if ($immediate)
					array_pop($this->_pids);
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
				if (file_exists('/proc/'.$pid))
					clearstatcache(true, '/proc/'.$pid);
				else
					unset($this->_pids[$key]);
			}
			usleep(100000); // 0.1 s
		}
	}

	public function display() {

	}

	// getter
	public function getOriginalWidth() {

		if ($this->isSvg())
			return 0;

		if (empty($this->_imageSize)) {
			$this->open();
			$this->_imageSize = (array) getimagesize($this->_fileName); // (yes)
		}

		return $this->_imageSize[0] ?? 0;
	}

	public function getOriginalHeight() {

		if ($this->isSvg())
			return 0;

		if (empty($this->_imageSize)) {
			$this->open();
			$this->_imageSize = (array) getimagesize($this->_fileName); // (yes)
		}

		return $this->_imageSize[1] ?? 0;
	}

	public function getMimeType() {

		if ($this->isSvg())
			return 'image/svg+xml';

		if (empty($this->_imageSize)) {
			$this->open();
			$this->_imageSize = (array) getimagesize($this->_fileName); // (yes)
		}

		return $this->_imageSize[2] ?? null;
	}

	public function isSvg() {

		if (is_null($this->_isSvg))
			$this->_isSvg = str_ends_with($this->_fileName, '.svg');

		return $this->_isSvg;
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
		$this->_watermarkImage     = $image;
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
		$this->_fileName  = $value;
		$this->_imageSize = [];
		$this->_isSvg = null;
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
		$this->_imageSize = [];
		$this->_isSvg = null;
		$this->_resizeFixed = null;
		return $this;
	}
}