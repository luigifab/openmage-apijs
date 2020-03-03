<?php
/**
 * Created J/12/09/2019
 * Updated S/01/02/2020
 *
 * Copyright 2008-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/apijs
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

class Luigifab_Apijs_Helper_Rewrite_Image extends Mage_Catalog_Helper_Image {

	public function getModel() {
		return $this->_getModel();
	}

	public function init($product, $attribute, $path = null) {

		if (!in_array($attribute, ['wysiwyg', 'category']))
			return parent::init($product, $attribute, $path);

		if ($attribute == 'wysiwyg') {
			// wysiwyg/abc/xyz.jpg => .../media/wysiwyg/abc/xyz.jpg
			if ($path[0] != '/')
				$path = Mage::getBaseDir('media').'/'.$path;
			// .../media/wysiwyg/abc/xyz.jpg => ../../wysiwyg/abc/xyz.jpg
			$dir  = Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY;
			$path = '../../'.$dir.'/'.mb_substr($path, mb_stripos($path, '/'.$dir.'/') + mb_strlen('/'.$dir.'/'));
		}
		else {
			// xyz.jpg => ../category/xyz.jpg
			$path = '../category/'.$path;
		}

		$this->_reset();
		$this->_setModel(Mage::getModel('catalog/product_image'));
		$this->_getModel()->setDestinationSubdir($attribute);
		$this->setWatermark(Mage::getStoreConfig('design/watermark/'.$this->_getModel()->getDestinationSubdir().'_image'));
		$this->setWatermarkImageOpacity(Mage::getStoreConfig('design/watermark/'.$this->_getModel()->getDestinationSubdir().'_imageOpacity'));
		$this->setWatermarkPosition(Mage::getStoreConfig('design/watermark/'.$this->_getModel()->getDestinationSubdir().'_position'));
		$this->setWatermarkSize(Mage::getStoreConfig('design/watermark/'.$this->_getModel()->getDestinationSubdir().'_size'));
		$this->setImageFile($path);

		return $this;
	}

	public function __toString() {

		$model = $this->_getModel();

		try {
			$model->setBaseFile($this->getImageFile());

			if ($model->getDestinationSubdir() == 'wysiwyg') {
				// if ($model->isCached())
				// oui mais non car il faut supprimer les ../ des chemins et des urls
				// ../../wysiwyg/abc/xyz.jpg
				// .../media/catalog/product/cache/[0/]wysiwyg/1200x/040ec09b1e35df139433887a97daa66f/../../wysiwyg/abc/xyz.jpg
				// .../media/wysiwyg/cache/1200x/040ec09b1e35df139433887a97daa66f/wysiwyg/abc/xyz.jpg
				$dir = Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY;
				$filename = $model->getNewFile();
				$filename = str_replace(['../', '//'], ['', '/'], Mage::helper('apijs')->getWysiwygImageDir(true).
					mb_substr($filename, mb_stripos($filename, '/'.$dir.'/') + mb_strlen('/'.$dir.'/')));

				if (!is_file($filename)) {

					if ($this->_scheduleRotate)
						$model->rotate($this->getAngle());
					if ($this->_scheduleResize)
						$model->resize();
					if ($this->getWatermark())
						$model->setWatermark($this->getWatermark());

					// $url = $model->saveFile()->getUrl();
					// oui mais non car il faut supprimer les ../ des chemins et des urls
					$model->getImageProcessor()->save($filename);
				}

				// return $model->getUrl();
				// .../media/catalog/product/cache/[0/]wysiwyg/1200x/040ec09b1e35df139433887a97daa66f/../../wysiwyg/abc/xyz.jpg
				// .../media/wysiwyg/cache/1200x/040ec09b1e35df139433887a97daa66f/wysiwyg/abc/xyz.jpg
				$url = $model->getUrl();
				$url = str_replace('../', '', preg_replace('#catalog/product/cache/(?:[0-9]+/)?'.$dir.'/#', $dir.'/cache/', $url));
			}
			else if ($model->getDestinationSubdir() == 'category') {
				// if ($model->isCached())
				// oui mais non car il faut supprimer les ../ des chemins et des urls
				// ../category/xyz.jpg
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/../category/xyz.jpg
				// .../media/catalog/category/cache/[0/]1200x/040ec09b1e35df139433887a97daa66f/xyz.jpg
				$filename = $model->getNewFile();
				$filename = str_replace(['../', '//', '/category/', '/catalog/product/'], ['', '/', '/', '/catalog/category/'], $filename);

				if (!is_file($filename)) {

					if ($this->_scheduleRotate)
						$model->rotate($this->getAngle());
					if ($this->_scheduleResize)
						$model->resize();
					if ($this->getWatermark())
						$model->setWatermark($this->getWatermark());

					// $url = $model->saveFile()->getUrl();
					// oui mais non car il faut supprimer les ../ des chemins et des urls
					$model->getImageProcessor()->save($filename);
				}

				// return $model->getUrl();
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/../category/xyz.jpg
				// .../media/catalog/category/cache/[0/]1200x/040ec09b1e35df139433887a97daa66f/xyz.jpg
				$url = $model->getUrl();
				$url = str_replace(['../', '/category/', '/catalog/product/'], ['', '/', '/catalog/category/'], $url);
			}
			else {
				if (empty($this->getImageFile()))
					$model->setBaseFile($this->getProduct()->getData($model->getDestinationSubdir()));

				// if ($model->isCached())
				// oui mais non car il faut supprimer les ../ des chemins et des urls
				// /x/y/xyz.jpg
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/x/y/xyz.jpg
				$filename = $model->getNewFile();
				if (Mage::getStoreConfigFlag('apijs/general/remove_store_id'))
					$filename = preg_replace('#/cache/\d+/#', '/cache/', $filename);

				if (!is_file($filename)) {

					if ($this->_scheduleRotate)
						$model->rotate($this->getAngle());
					if ($this->_scheduleResize)
						$model->resize();
					if ($this->getWatermark())
						$model->setWatermark($this->getWatermark());

					// $url = $model->saveFile()->getUrl();
					// oui mais non car il faut supprimer les ../ des chemins et des urls
					$model->getImageProcessor()->save($filename);
				}

				// return $model->getUrl();
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/x/y/xyz.jpg
				$url = $model->getUrl();
				if (Mage::getStoreConfigFlag('apijs/general/remove_store_id'))
					$url = preg_replace('#/cache/\d+/#', '/cache/', $url);
			}
		}
		catch (Exception $e) {
			$url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
		}

		return $url;
	}

	public function getOriginalWidth() {

		if ($this->getImageFile())
			$this->_getModel()->setBaseFile($this->getImageFile());

		return $this->_getModel()->getImageProcessor()->getOriginalWidth();
	}

	public function getOriginalHeight() {

		if ($this->getImageFile())
			$this->_getModel()->setBaseFile($this->getImageFile());

		return $this->_getModel()->getImageProcessor()->getOriginalHeight();
	}
}