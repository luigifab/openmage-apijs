<?php
/**
 * Created J/12/09/2019
 * Updated S/30/12/2023
 *
 * Copyright 2008-2025 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2019-2023 | Fabrice Creuzot <fabrice~cellublue~com>
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

class Luigifab_Apijs_Helper_Rewrite_Image extends Mage_Catalog_Helper_Image {

	// singleton
	protected $_usePython;
	protected $_cacheConfig;
	protected $_cacheUrls;
	protected $_webp;
	protected $_processor;
	protected $_helper;
	protected $_modelImg;
	protected $_cleanUrl;
	protected $_storeId;
	protected $_svg;
	protected $_fixed;
	protected $_imageFile;


	public function __construct() {
		$this->_usePython = Mage::getStoreConfigFlag('apijs/general/python');
	}

	public function init($object, $attribute, $path = null, bool $fixed = true, bool $webp = false) {

		$this->_reset();
		$this->_webp = $webp;

		// debug
		//if (!isset($this->_debugBegin)) $this->_debugBegin = microtime(true);
		//if (!isset($this->_debugCount)) $this->_debugCount = 0;
		//if (!isset($this->_debugCache)) $this->_debugCache = 0;
		//if (!isset($this->_debugRenew)) $this->_debugRenew = 0;
		//$this->_debugCount += 1;
		//$this->_debugStart = microtime(true);
		//Mage::log('Open file '.$path.'...', Zend_Log::DEBUG);

		if (empty($this->_helper)) {
			$this->_helper   = Mage::helper('apijs');
			$this->_modelImg = Mage::getModel('catalog/product_image');
			$this->_cleanUrl = (PHP_SAPI != 'cli') && str_starts_with(Mage::getBaseUrl('media'), Mage::getBaseUrl('web'));
			$this->_storeId  = Mage::app()->getStore()->getId();
		}

		// sans le dossier, cela ne génère pas les miniatures wysiwyg ou category
		$dir = $this->_helper->getCatalogProductImageDir(true);
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
			if (empty($path) && !empty($object->getId()))
				$path = $object->getResource()->getAttributeRawValue($object->getId(), $attribute, $object->getStoreId());
			// xyz.jpg => ../category/xyz.jpg
			$path = '../category/'.$path;
		}
		else if (empty($path)) {
			$path = $object->getData($attribute);
			if (empty($path) && !empty($object->getId()))
				$path = $object->getResource()->getAttributeRawValue($object->getId(), $attribute, $object->getStoreId());
		}

		$model = clone $this->_modelImg;
		$model->setDestinationSubdir($attribute);

		$attribute = $model->getDestinationSubdir();
		$this->_setModel($model);

		// cache de la config et des chemins des images et des URLs générées
		if (empty($this->_cacheConfig) || empty($this->_cacheUrls)) {

			$this->_processor = Mage::getSingleton('apijs/python');

			if (Mage::app()->useCache('config')) {
				$this->_cacheConfig = Mage::app()->loadCache('apijs_config');
				$this->_cacheConfig = empty($this->_cacheConfig) ? null : @json_decode($this->_cacheConfig, true);
			}

			if (empty($this->_cacheConfig) || !is_array($this->_cacheConfig)) {

				$this->_cacheConfig = [
					'date' => date('c'),
					'apijs/general/python' => $this->_usePython,
					'apijs/general/remove_store_id' => Mage::getStoreConfigFlag('apijs/general/remove_store_id'),
					'list_search'  => [],
					'list_replace' => [],
				];

				if (Mage::getStoreConfigFlag('apijs/general/use_link')) {

					$dir = Mage::getBaseDir('media');
					$tmp = ['wysiwyg/cache', 'catalog/category/cache'];

					$attrs = Mage::getModel('catalog/product')->getMediaAttributes();
					if ($this->_cacheConfig['apijs/general/remove_store_id']) {
						foreach ($attrs as $attrCode => $attr)
							$tmp[] = 'catalog/product/cache/'.$attrCode;
					}
					else {
						$storeIds = Mage::getResourceModel('core/store_collection')->getAllIds(); // with admin
						foreach ($attrs as $attrCode => $attr) {
							$tmp[] = 'catalog/product/cache/'.$attrCode;
							foreach ($storeIds as $storeId)
								$tmp[] = 'catalog/product/cache/'.$storeId.'/'.$attrCode;
						}
					}

					foreach ($tmp as $full) {

						$short = '';
						$key = crc32($full);
						$idx = 1;

						// ajoute l'id pour éviter une boucle infini
						foreach (explode('/', $full) as $word)
							$short .= substr($word, 0, $idx); // not mb_substr
						while (in_array('/media/'.$short.'/', $this->_cacheConfig['list_replace']))
							$short .= substr($word.$key, ++$idx, 1); // not mb_substr

						// $dir $short => /var/www/xyz/web/media wc
						// $dir $full  => /var/www/xyz/web/media wysiwyg/cache
						if (!file_exists($dir.'/'.$full))
							@mkdir($dir.'/'.$full, 0755, true);
						if (!file_exists($dir.'/'.$short))
							@symlink($full, $dir.'/'.$short);

						$this->_cacheConfig['list_search'][]  = '/media/'.$full.'/';
						$this->_cacheConfig['list_replace'][] = '/media/'.$short.'/';
					}
				}
			}

			if (Mage::app()->useCache('block_html')) {
				$this->_cacheUrls = Mage::app()->loadCache('apijs_urls');
				$this->_cacheUrls = empty($this->_cacheUrls) ? null : @json_decode($this->_cacheUrls, true);
			}

			if (empty($this->_cacheUrls) || !is_array($this->_cacheUrls)) {
				$this->_cacheUrls = [
					'date' => date('c'),
				];
			}
		}

		// watermark
		// @todo by store view
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

		$this->_svg = !empty($path) && str_ends_with($path, '.svg');
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

		if (!is_file($path))
			return false;

		if (!$this->_usePython)
			return parent::validateUploadFile($path);

		$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
		if (!in_array(finfo_file($fileInfo, $path), Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions('image', true))) {
			finfo_close($fileInfo);
			return false;
		}
		finfo_close($fileInfo);

		return parent::validateUploadFile($path);
	}

	public function setBaseFile() {

		//$go  = microtime(true);
		$model = $this->_getModel();
		$file  = $this->_imageFile ? $this->_helper->getCatalogProductImageDir().trim($this->_imageFile, '/') : false;

		if ($this->_cacheConfig['apijs/general/python']) {
			$this->_processor->setFilename($file)->setFixed($this->_fixed);
			$model->setImageProcessor($this->_processor);
		}

		try {
			// essaye le fichier source ou le placeholder
			// setWatermarkFile pour avoir une url unique
			// par exemple, en cas de suppression de l'image a.jpg, puis de l'ajout de l'image a.jpg, même nom mais contenu différent
			if (!empty($file) && is_file($file)) {
				$old = $model->getWatermarkFile();
				$model->setWatermarkFile($old.filemtime($file));
				$model->setBaseFile($this->_imageFile);
				$model->setWatermarkFile($old);
			}
			else {
				$model->setBaseFile('/no_selection');
				$this->_imageFile = $model->getBaseFile();
				if (is_object($this->_processor))
					$this->_processor->setFilename($this->_imageFile);
			}
		}
		catch (Throwable $t) {
			$area = Mage::getDesign()->getArea();
			if ($area == 'adminhtml')
				Mage::getDesign()->setArea('frontend');
			// si le placeholder n'existe pas
			// essaye avec le placeholder image
			$model->setDestinationSubdir('image');
			$model->setBaseFile('/no_selection');
			$this->_imageFile = $model->getBaseFile();
			if (is_object($this->_processor))
				$this->_processor->setFilename($this->_imageFile);
			// si ça crash encore
			// c'est éventuellement la faute à un lien symbolique, quelque part dans media, au lieu d'être complètement sur media
			if ($area == 'adminhtml')
				Mage::getDesign()->setArea($area);
		}

		//Mage::log(' setBaseFile '.number_format(microtime(true) - $go, 4), Zend_Log::DEBUG);
		return $this;
	}

	public function cleanUrl(string $url) {

		$url = $this->_cleanUrl ? mb_substr($url, mb_strpos($url, '/', 9)) : $url;

		if ($this->_cacheConfig['apijs/general/remove_store_id'])
			$url = str_replace('/cache/'.$this->_storeId.'/', '/cache/', $url);

		if (!empty($this->_cacheConfig['list_search']))
			$url = str_replace($this->_cacheConfig['list_search'], $this->_cacheConfig['list_replace'], $url);

		return $url;
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
				// oui mais non car il faut supprimer les ../ des chemins et des URLs
				// ../../wysiwyg/abc/xyz.jpg
				// .../media/catalog/product/cache/[0/]wysiwyg/1200x/040ec09b1e35df139433887a97daa66f/../../wysiwyg/abc/xyz.jpg
				// .../media/wysiwyg/cache/[0/]1200x/040ec09b1e35df139433887a97daa66f/wysiwyg/abc/xyz.jpg
				$dir = Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY;
				$fileName = $model->getNewFile();
				$fileName = str_replace(['../', '//'], ['', '/'], $this->_helper->getWysiwygImageDir(true).
					$this->_storeId.'/'.mb_substr($fileName, mb_stripos($fileName, '/'.$dir.'/') + mb_strlen('/'.$dir.'/')));

				if ($this->_cacheConfig['apijs/general/remove_store_id'])
					$fileName = str_replace('/cache/'.$this->_storeId.'/', '/cache/', $fileName);
				if ($this->_webp)
					$fileName = mb_substr($fileName, 0, mb_strrpos($fileName, '.')).'.webp';

				if (array_key_exists($fileName, $this->_cacheUrls)) {
					//Mage::log(' CACHE HIT '.$fileName, Zend_Log::DEBUG); $this->_debugCache++;
					$url = $this->_cacheUrls[$fileName];
				}
				else {
					//Mage::log(' generate '.$fileName, Zend_Log::DEBUG); $this->_debugRenew++;
					if (!is_file($fileName)) {

						if (!empty($this->_scheduleRotate))
							$model->rotate($this->getAngle());
						if (!empty($this->_scheduleResize))
							$model->resize();
						if (!empty($this->getWatermark()))
							$model->setWatermark($this->getWatermark());

						// $url = $model->saveFile()->getUrl();
						// oui mais non car il faut supprimer les ../ des chemins et des URLs
						$model->getImageProcessor()->save($fileName);
					}

					// return $model->getUrl();
					// .../media/catalog/product/cache/[0/]wysiwyg/1200x/040ec09b1e35df139433887a97daa66f/../../wysiwyg/abc/xyz.jpg
					// .../media/wysiwyg/cache/[0/]1200x/040ec09b1e35df139433887a97daa66f/wysiwyg/abc/xyz.jpg
					$url = $model->getUrl();
					$url = str_replace(['catalog/product/cache/'.$dir.'/', 'catalog/product/cache/'.$this->_storeId.'/'.$dir.'/', '../'], [$dir.'/cache/', $dir.'/cache/'.$this->_storeId.'/', ''], $url);
					$url = $this->cleanUrl($url);

					// cache
					$this->_cacheUrls[$fileName] = $url;
				}
			}
			else if ($model->getDestinationSubdir() == 'category') {
				// if ($model->isCached())
				// oui mais non car il faut supprimer les ../ des chemins et des URLs
				// ../category/xyz.jpg
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/../category/xyz.jpg
				// .../media/catalog/category/cache/[0/]1200x/040ec09b1e35df139433887a97daa66f/xyz.jpg
				$fileName = $model->getNewFile();
				$fileName = str_replace(['../', '//', '/category/', '/catalog/product/'], ['', '/', '/', '/catalog/category/'], $fileName);

				if ($this->_cacheConfig['apijs/general/remove_store_id'])
					$fileName = str_replace('/cache/'.$this->_storeId.'/', '/cache/', $fileName);
				if ($this->_webp)
					$fileName = mb_substr($fileName, 0, mb_strrpos($fileName, '.')).'.webp';

				if (array_key_exists($fileName, $this->_cacheUrls)) {
					//Mage::log(' CACHE HIT '.$fileName, Zend_Log::DEBUG); $this->_debugCache++;
					$url = $this->_cacheUrls[$fileName];
				}
				else {
					//Mage::log(' generate '.$fileName, Zend_Log::DEBUG); $this->_debugRenew++;
					if (!is_file($fileName)) {

						if (!empty($this->_scheduleRotate))
							$model->rotate($this->getAngle());
						if (!empty($this->_scheduleResize))
							$model->resize();
						if (!empty($this->getWatermark()))
							$model->setWatermark($this->getWatermark());

						// $url = $model->saveFile()->getUrl();
						// oui mais non car il faut supprimer les ../ des chemins et des URLs
						$model->getImageProcessor()->save($fileName);
					}

					// return $model->getUrl();
					// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/../category/xyz.jpg
					// .../media/catalog/category/cache/[0/]1200x/040ec09b1e35df139433887a97daa66f/xyz.jpg
					$url = $model->getUrl();
					$url = str_replace(['../', '/category/', '/catalog/product/'], ['', '/', '/catalog/category/'], $url);
					$url = $this->cleanUrl($url);

					// cache
					$this->_cacheUrls[$fileName] = $url;
				}
			}
			else {
				// if ($model->isCached())
				// /x/y/xyz.jpg
				// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/x/y/xyz.jpg
				$fileName = $model->getNewFile();

				if ($this->_cacheConfig['apijs/general/remove_store_id'])
					$fileName = str_replace('/cache/'.$this->_storeId.'/', '/cache/', $fileName);
				if ($this->_webp)
					$fileName = mb_substr($fileName, 0, mb_strrpos($fileName, '.')).'.webp';

				if (array_key_exists($fileName, $this->_cacheUrls)) {
					//Mage::log(' CACHE HIT '.$fileName, Zend_Log::DEBUG); $this->_debugCache++;
					$url = $this->_cacheUrls[$fileName];
				}
				else {
					//Mage::log(' generate '.$fileName, Zend_Log::DEBUG); $this->_debugRenew++;
					if (!is_file($fileName)) {

						if (!empty($this->_scheduleRotate))
							$model->rotate($this->getAngle());
						if (!empty($this->_scheduleResize))
							$model->resize();
						if (!empty($this->getWatermark()))
							$model->setWatermark($this->getWatermark());

						// $url = $model->saveFile()->getUrl();
						// oui mais non car il faut supprimer les ../ des chemins et des URLs
						$model->getImageProcessor()->save($fileName);
					}

					// return $model->getUrl();
					// .../media/catalog/product/cache/[0/]category/1200x/040ec09b1e35df139433887a97daa66f/x/y/xyz.jpg
					$url = $this->cleanUrl($model->getUrl());

					// cache
					$this->_cacheUrls[$fileName] = $url;
				}
			}
		}
		catch (Throwable $t) {
			$url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
		}

		//Mage::log(' toString '.number_format(microtime(true) - $go, 4), Zend_Log::DEBUG);
		//Mage::log(' closing file after '.number_format(microtime(true) - $this->_debugStart, 4).' / since first init '.number_format(microtime(true) - $this->_debugBegin, 4).' / total '.$this->_debugCount.' = cache '.$this->_debugCache.' + generate '.$this->_debugRenew, Zend_Log::DEBUG);

		return $this->_webp ? mb_substr($url, 0, mb_strrpos($url, '.')).'.webp' : $url;
	}

	public function __destruct() {

		if (!empty($this->_cacheConfig) && Mage::app()->useCache('config'))
			Mage::app()->saveCache(json_encode($this->_cacheConfig), 'apijs_config',
				[Mage_Core_Model_Config::CACHE_TAG]);

		if (!empty($this->_cacheUrls) && Mage::app()->useCache('block_html'))
			Mage::app()->saveCache(json_encode($this->_cacheUrls), 'apijs_urls',
				[Mage_Core_Model_Config::CACHE_TAG, Mage_Core_Block_Abstract::CACHE_GROUP]);
	}
}