<?php
/**
 * Created J/12/09/2019
 * Updated M/01/12/2020
 *
 * Copyright 2008-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2019-2020 | Fabrice Creuzot <fabrice~cellublue~com>
 * Copyright 2020      | Jérôme Siau <jerome~cellublue~com>
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

	public function getModel() {
		return $this->_getModel();
	}

	public function init($product, $attribute, $path = null, $fixed = true) {

		$this->_reset();

		// sans le dossier, cela ne génère pas les miniatures wysiwyg ou category
		$dir = Mage::helper('apijs')->getCatalogProductImageDir(true);
		if (!is_dir($dir))
			@mkdir($dir, 0755, true);

		//if (!isset($this->_begin)) $this->_begin = microtime(true);
		//if (!isset($this->_count)) $this->_count = 0;
		//$this->_count += 1;
		//$this->_start = microtime(true);
		//Mage::log('Openning file '.$path.'...');

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
		if (empty($this->_config) || empty($this->_urls)) {

			$this->_config = @json_decode(Mage::app()->loadCache('apijs_config'), true);
			if (empty($this->_config) || !is_array($this->_config)) {
				$this->_config = [
					'date' => date('Y-m-d H:i:s \U\T\C'),
					'apijs/general/python' => Mage::getStoreConfigFlag('apijs/general/python'),
					'apijs/general/remove_store_id' => Mage::getStoreConfigFlag('apijs/general/remove_store_id')
				];
			}

			$this->_urls = @json_decode(Mage::app()->loadCache('apijs_urls'), true);
			if (empty($this->_urls) || !is_array($this->_urls)) {
				$this->_urls = [
					'date' => date('Y-m-d H:i:s \U\T\C')
				];
			}
		}

		// watermark
		if (!array_key_exists('design/watermark/'.$attribute.'_image', $this->_config)) {
			$this->_config['design/watermark/'.$attribute.'_image'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_image');
			$this->_config['design/watermark/'.$attribute.'_imageOpacity'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_imageOpacity');
			$this->_config['design/watermark/'.$attribute.'_position'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_position');
			$this->_config['design/watermark/'.$attribute.'_size'] = Mage::getStoreConfig('design/watermark/'.$attribute.'_size');
		}
		if (!empty($this->_config['design/watermark/'.$attribute.'_image'])) {
			$this->setWatermark($this->_config['design/watermark/'.$attribute.'_image']);
			$this->setWatermarkImageOpacity($this->_config['design/watermark/'.$attribute.'_imageOpacity']);
			$this->setWatermarkPosition($this->_config['design/watermark/'.$attribute.'_position']);
			$this->setWatermarkSize($this->_config['design/watermark/'.$attribute.'_size']);
		}

		if (empty($path))
			$path = $product->getData($attribute);

		$this->_svg = (!empty($path) && (mb_substr($path, -4) == '.svg'));
		$this->setImageFile($path);
		$this->trySetBaseFile($model, $path);

		if ($this->_config['apijs/general/python']) {
			$processor = Mage::getSingleton('apijs/python')->setFilename($model->getBaseFile())->setFixed($fixed);
			$model->setImageProcessor($processor);
		}

		return $this;
	}

	public function getImageProcessor() {
		return $this->_getModel()->getImageProcessor();
	}

	public function getOriginalWidth() {
		return empty($this->_svg) ? parent::getOriginalWidth() : 0;
	}

	public function getOriginalHeight() {
		return empty($this->_svg) ? parent::getOriginalHeight() : 0;
	}

	private function trySetBaseFile($model, $path) {

		try {
			// essaye le fichier source
			$this->wrapSetBaseFile($model, $path);
		}
		catch (Exception $e) {
			try {
				// si le fichier source n'existe pas
				// essaye avec le placeholder
				$model->setBaseFile('/no_selection');
			}
			catch (Exception $e) {
				$area = Mage::getDesign()->getArea();
				if ($area == 'adminhtml')
					Mage::getDesign()->setArea('frontend');
				// si le placeholder n'existe pas
				// essaye avec le placeholder image
				$model->setDestinationSubdir('image');
				$model->setBaseFile('/no_selection');
				// si ça crash encore
				// c'est éventuellement la faute à un lien symbolique, quelque part dans media, au lieu d'être complètement sur media
				if ($area == 'adminhtml')
					Mage::getDesign()->setArea($area);
			}
		}
	}

	private function wrapSetBaseFile($model, $file) {

		$filename = Mage::helper('apijs')->getCatalogProductImageDir().$file;

		// pour avoir une url unique
		// par exemple, en cas de suppression de l'image a.jpg, puis de l'ajout de l'image a.jpg, même nom mais contenu différent
		$old = $model->getWatermarkFile();
		$model->setWatermarkFile(is_file($filename) ? $old.filemtime($filename) : $old);
		$model->setBaseFile($file);
		$model->setWatermarkFile($old);
	}

	public function validateUploadFile($path) {
		return (is_file($path) && in_array(mime_content_type($path), ['image/svg', 'image/svg+xml'])) ? true : parent::validateUploadFile($path);
	}

	public function __toString() {

		$model = $this->_getModel();
		if (!empty($this->_svg)) {
			$this->resize(0, 0);
			if (!$this->_config['apijs/general/python']) {
				if ($model->getDestinationSubdir() == 'wysiwyg')
					return Mage::getBaseUrl('media').str_replace('../', '', $this->getImageFile());
				if ($model->getDestinationSubdir() == 'category')
					return Mage::getBaseUrl('media').'catalog/category/'.$this->getImageFile();
				return Mage::getBaseUrl('media').'catalog/product'.$this->getImageFile();
			}
		}

		try {
			$this->wrapSetBaseFile($model, $this->getImageFile());

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

				if (array_key_exists($filename, $this->_urls)) {
					$url = $this->_urls[$filename];
				}
				else {
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
					$this->_urls[$filename] = $url;
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

				if (array_key_exists($filename, $this->_urls)) {
					$url = $this->_urls[$filename];
				}
				else {
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
					$this->_urls[$filename] = $url;
				}
			}
			else {
				// if ($model->isCached())
				// /x/y/xyz.jpg
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/x/y/xyz.jpg
				$filename = $model->getNewFile();
				if ($this->_config['apijs/general/remove_store_id'])
					$filename = preg_replace('#/cache/\d+/#', '/cache/', $filename);

				if (array_key_exists($filename, $this->_urls)) {
					$url = $this->_urls[$filename];
				}
				else {
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
					if ($this->_config['apijs/general/remove_store_id'])
						$url = preg_replace('#/cache/\d+/#', '/cache/', $url);

					// cache
					$this->_urls[$filename] = $url;
				}
			}
		}
		catch (Exception $e) {
			$url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
		}

		//Mage::log('Closing file after '.number_format(microtime(true) - $this->_start, 2).' / since start '.number_format(microtime(true) - $this->_begin, 2).' / nb '.$this->_count);

		return $url;
	}

	public function __destruct() {

		if (!empty($this->_config) && Mage::app()->useCache('config')) // ici la balise XML du cache, ci-dessous une clef et le tag du cache
			Mage::app()->saveCache(json_encode($this->_config), 'apijs_config', ['CONFIG']);

		if (!empty($this->_urls) && Mage::app()->useCache('block_html')) // ici la balise XML du cache, ci-dessous une clef et le tag du cache
			Mage::app()->saveCache(json_encode($this->_urls), 'apijs_urls', ['CONFIG', 'BLOCK_HTML']);
	}
}