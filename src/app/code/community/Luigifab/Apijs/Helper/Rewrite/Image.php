<?php
/**
 * Created J/12/09/2019
 * Updated V/19/02/2021
 *
 * Copyright 2008-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2019-2021 | Fabrice Creuzot <fabrice~cellublue~com>
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

class Luigifab_Apijs_Helper_Rewrite_Image extends Mage_Catalog_Helper_Image {

	public function init($product, $attribute, $path = null, $fixed = true) {

		$this->_reset();

		// debug
		//if (!isset($this->_debugBegin)) $this->_debugBegin = microtime(true);
		//if (!isset($this->_debugCount)) $this->_debugCount = 0;
		//if (!isset($this->_debugCache)) $this->_debugCache = 0;
		//if (!isset($this->_debugRenew)) $this->_debugRenew = 0;
		//$this->_debugCount += 1;
		//$this->_debugStart = microtime(true);
		//Mage::log('Open file '.$path.'...');

		// sans le dossier, cela ne génère pas les miniatures wysiwyg ou category
		$dir = Mage::helper('apijs')->getCatalogProductImageDir(true);
		if (!is_dir($dir))
			@mkdir($dir, 0755, true);

		if ($attribute == 'wysiwyg') {
			// wysiwyg/abc/xyz.jpg => .../media/wysiwyg/abc/xyz.jpg
			if ($path[0] != '/')
				$path = Mage::getBaseDir('media').'/'.$path;
			// .../media/wysiwyg/abc/xyz.jpg => ../../wysiwyg/abc/xyz.jpg
			$dir  = Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY;
			$path = '../../'.$dir.'/'.mb_substr($path, mb_stripos($path, '/'.$dir.'/') + mb_strlen('/'.$dir.'/'));
		}
		else if ($attribute == 'category') {
			// xyz.jpg => ../category/xyz.jpg
			$path = '../category/'.$path;
		}

		$model = Mage::getModel('catalog/product_image');
		$model->setDestinationSubdir($attribute);
		$attribute = $model->getDestinationSubdir();
		$this->_setModel($model);

		// cache de la config et des urls générées
		if (empty($this->_cacheConfig) || empty($this->_cacheUrls)) {

			$this->_cacheConfig = Mage::app()->useCache('config') ? @json_decode(Mage::app()->loadCache('apijs_config'), true) : null;
			if (empty($this->_cacheConfig) || !is_array($this->_cacheConfig)) {
				$this->_cacheConfig = [
					'date' => date('Y-m-d H:i:s \U\T\C'),
					'apijs/general/python' => Mage::getStoreConfigFlag('apijs/general/python'),
					'apijs/general/remove_store_id' => Mage::getStoreConfigFlag('apijs/general/remove_store_id')
				];
			}

			$this->_cacheUrls = Mage::app()->useCache('config') ? @json_decode(Mage::app()->loadCache('apijs_urls'), true) : null;
			if (empty($this->_cacheUrls) || !is_array($this->_cacheUrls)) {
				$this->_cacheUrls = [
					'date' => date('Y-m-d H:i:s \U\T\C')
				];
			}
		}

		// watermark
		if (!array_key_exists('design/watermark/'.$attribute.'_image', $this->_cacheConfig)) {
			$this->_cacheConfig['design/watermark/'.$attribute.'_image'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_image');
			$this->_cacheConfig['design/watermark/'.$attribute.'_imageOpacity'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_imageOpacity');
			$this->_cacheConfig['design/watermark/'.$attribute.'_position'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_position');
			$this->_cacheConfig['design/watermark/'.$attribute.'_size'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_size');
		}
		if (!empty($this->_cacheConfig['design/watermark/'.$attribute.'_image'])) {
			$this->setWatermark($this->_cacheConfig['design/watermark/'.$attribute.'_image']);
			$this->setWatermarkImageOpacity($this->_cacheConfig['design/watermark/'.$attribute.'_imageOpacity']);
			$this->setWatermarkPosition($this->_cacheConfig['design/watermark/'.$attribute.'_position']);
			$this->setWatermarkSize($this->_cacheConfig['design/watermark/'.$attribute.'_size']);
		}

		if (empty($path))
			$path = $product->getData($attribute);

		$this->_svg = !empty($path) && (mb_substr($path, -4) == '.svg');
		$this->_fixed = $fixed;
		$this->_imageFile = $path;

		return $this;
	}

	public function getModel() {
		return $this->_getModel();
	}

	public function getImageProcessor() {
		return $this->_getModel()->getImageProcessor();
	}

	public function getOriginalWidth() {

		if (empty($this->_svg)) {
			if (empty($this->_getModel()->getBaseFile()))
				$this->setBaseFile();
			return parent::getOriginalWidth();
		}

		return 0;
	}

	public function getOriginalHeight() {

		if (empty($this->_svg)) {
			if (empty($this->_getModel()->getBaseFile()))
				$this->setBaseFile();
			return parent::getOriginalHeight();
		}

		return 0;
	}

	public function validateUploadFile($path) {
		return (is_file($path) && in_array(mime_content_type($path), ['image/svg', 'image/svg+xml'])) ? true : parent::validateUploadFile($path);
	}

	public function setBaseFile() {

		//$go  = microtime(true);
		$model = $this->_getModel();
		$file  = Mage::helper('apijs')->getCatalogProductImageDir().trim($this->_imageFile, '/');

		if ($this->_cacheConfig['apijs/general/python']) {
			$processor = Mage::getSingleton('apijs/python')->setFilename($file)->setFixed($this->_fixed);
			$model->setImageProcessor($processor);
		}

		try {
			// essaye le fichier source ou le placeholder
			// setWatermarkFile pour avoir une url unique
			// par exemple, en cas de suppression de l'image a.jpg, puis de l'ajout de l'image a.jpg, même nom mais contenu différent
			if (is_file($file)) {
				$old = $model->getWatermarkFile();
				$model->setWatermarkFile($old.filemtime($file));
				$model->setBaseFile($this->_imageFile);
				$model->setWatermarkFile($old);
			}
			else {
				$model->setBaseFile('/no_selection');
				$this->_imageFile = $model->getBaseFile();
				if (is_object($processor))
					$processor->setFilename($this->_imageFile);
			}
		}
		catch (Throwable $e) {
			$area = Mage::getDesign()->getArea();
			if ($area == 'adminhtml')
				Mage::getDesign()->setArea('frontend');
			// si le placeholder n'existe pas
			// essaye avec le placeholder image
			$model->setDestinationSubdir('image');
			$model->setBaseFile('/no_selection');
			$this->_imageFile = $model->getBaseFile();
			if (is_object($processor))
				$processor->setFilename($this->_imageFile);
			// si ça crash encore
			// c'est éventuellement la faute à un lien symbolique, quelque part dans media, au lieu d'être complètement sur media
			if ($area == 'adminhtml')
				Mage::getDesign()->setArea($area);
		}

		//Mage::log(' setBaseFile '.number_format(microtime(true) - $go, 4));
		return $this;
	}

	public function __toString() {

		$model = $this->_getModel();
		if (!empty($this->_svg)) {
			$this->resize(0, 0);
			if (!$this->_cacheConfig['apijs/general/python']) {
				if ($model->getDestinationSubdir() == 'wysiwyg')
					return Mage::getBaseUrl('media').str_replace('../', '', $this->_imageFile);
				if ($model->getDestinationSubdir() == 'category')
					return Mage::getBaseUrl('media').'catalog/category/'.$this->_imageFile;
				// (true)
				     return Mage::getBaseUrl('media').'catalog/product/'.$this->_imageFile;
			}
		}

		try {
			//$go = microtime(true);
			$this->setBaseFile();

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

				if (array_key_exists($filename, $this->_cacheUrls)) {
					//Mage::log(' CACHE HIT '.$filename); $this->_debugCache++;
					$url = $this->_cacheUrls[$filename];
				}
				else {
					//Mage::log(' generate '.$filename); $this->_debugRenew++;
					if (!is_file($filename)) {

						if (!empty($this->_scheduleRotate))
							$model->rotate($this->getAngle());
						if (!empty($this->_scheduleResize))
							$model->resize();
						if (!empty($this->getWatermark()))
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

					// cache
					$this->_cacheUrls[$filename] = $url;
				}
			}
			else if ($model->getDestinationSubdir() == 'category') {
				// if ($model->isCached())
				// oui mais non car il faut supprimer les ../ des chemins et des urls
				// ../category/xyz.jpg
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/../category/xyz.jpg
				// .../media/catalog/category/cache/[0/]1200x/040ec09b1e35df139433887a97daa66f/xyz.jpg
				$filename = $model->getNewFile();
				$filename = str_replace(['../', '//', '/category/', '/catalog/product/'], ['', '/', '/', '/catalog/category/'], $filename);

				if (array_key_exists($filename, $this->_cacheUrls)) {
					//Mage::log(' CACHE HIT '.$filename); $this->_debugCache++;
					$url = $this->_cacheUrls[$filename];
				}
				else {
					//Mage::log(' generate '.$filename); $this->_debugRenew++;
					if (!is_file($filename)) {

						if (!empty($this->_scheduleRotate))
							$model->rotate($this->getAngle());
						if (!empty($this->_scheduleResize))
							$model->resize();
						if (!empty($this->getWatermark()))
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

					// cache
					$this->_cacheUrls[$filename] = $url;
				}
			}
			else {
				// if ($model->isCached())
				// /x/y/xyz.jpg
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/x/y/xyz.jpg
				$filename = $model->getNewFile();
				if ($this->_cacheConfig['apijs/general/remove_store_id'])
					$filename = preg_replace('#/cache/\d+/#', '/cache/', $filename);

				if (array_key_exists($filename, $this->_cacheUrls)) {
					//Mage::log(' CACHE HIT '.$filename); $this->_debugCache++;
					$url = $this->_cacheUrls[$filename];
				}
				else {
					//Mage::log(' generate '.$filename); $this->_debugRenew++;
					if (!is_file($filename)) {

						if (!empty($this->_scheduleRotate))
							$model->rotate($this->getAngle());
						if (!empty($this->_scheduleResize))
							$model->resize();
						if (!empty($this->getWatermark()))
							$model->setWatermark($this->getWatermark());

						// $url = $model->saveFile()->getUrl();
						// oui mais non car il faut supprimer les ../ des chemins et des urls
						$model->getImageProcessor()->save($filename);
					}

					// return $model->getUrl();
					// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/x/y/xyz.jpg
					$url = $model->getUrl();
					if ($this->_cacheConfig['apijs/general/remove_store_id'])
						$url = preg_replace('#/cache/\d+/#', '/cache/', $url);

					// cache
					$this->_cacheUrls[$filename] = $url;
				}
			}
		}
		catch (Throwable $e) {
			$url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
		}

		//Mage::log(' toString '.number_format(microtime(true) - $go, 4));
		//Mage::log(' closing file after '.number_format(microtime(true) - $this->_debugStart, 4).' / since first init '.number_format(microtime(true) - $this->_debugBegin, 4).' / total '.$this->_debugCount.' = cache '.$this->_debugCache.' + generate '.$this->_debugRenew);

		return $url;
	}

	public function __destruct() {

		if (!empty($this->_cacheConfig) && Mage::app()->useCache('config'))
			Mage::app()->saveCache(json_encode($this->_cacheConfig), 'apijs_config', [Mage_Core_Model_Config::CACHE_TAG]);

		if (!empty($this->_cacheUrls) && Mage::app()->useCache('block_html'))
			Mage::app()->saveCache(json_encode($this->_cacheUrls), 'apijs_urls', [Mage_Core_Model_Config::CACHE_TAG, Mage_Core_Block_Abstract::CACHE_GROUP]);
	}
}