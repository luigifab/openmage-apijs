<?php
/**
 * Created D/20/11/2011
 * Updated S/16/12/2023
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

class Luigifab_Apijs_Helper_Data extends Mage_Core_Helper_Abstract {

	// singleton
	protected $_usePython;
	protected $_baseMediaDir;
	protected $_baseMediaPath;
	protected $_baseWysiwygPath;
	protected $_filesToRemove = [];


	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Apijs')->version;
	}

	public function _(string $data, ...$values) {
		$text = $this->__(' '.$data, ...$values);
		return ($text[0] == ' ') ? $this->__($data, ...$values) : $text;
	}

	public function escapeEntities($data, bool $quotes = false) {
		return empty($data) ? $data : htmlspecialchars($data, $quotes ? ENT_SUBSTITUTE | ENT_COMPAT : ENT_SUBSTITUTE | ENT_NOQUOTES);
	}

	public function formatDate($date = null, $format = Zend_Date::DATETIME_LONG, $showTime = false) {
		$object = Mage::getSingleton('core/locale');
		return str_replace($object->date($date)->toString(Zend_Date::TIMEZONE), '', $object->date($date)->toString($format));
	}

	public function getHumanEmailAddress($email) {
		return empty($email) ? '' : $this->escapeEntities(str_replace(['<', '>', ',', '"'], ['(', ')', ', ', ''], $email));
	}

	public function getHumanDuration($start, $end = null) {

		if (is_numeric($start) || (!in_array($start, ['', '0000-00-00 00:00:00', null]) && !in_array($end, ['', '0000-00-00 00:00:00', null]))) {

			$data    = is_numeric($start) ? $start : strtotime($end) - strtotime($start);
			$minutes = (int) ($data / 60);
			$seconds = $data % 60;

			if ($data > 599)
				$data = '<strong>'.(($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds).'</strong>';
			else if ($data > 59)
				$data = '<strong>'.(($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds).'</strong>';
			else if ($data > 1)
				$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
			else
				$data = '⩽&nbsp;1';
		}

		return empty($data) ? '' : $data;
	}

	public function getNumber($value, array $options = []) {
		$options['locale'] = Mage::getSingleton('core/locale')->getLocaleCode();
		return Zend_Locale_Format::toNumber($value, $options);
	}

	public function getNumberToHumanSize($number) {

		$number = (float) $number;
		if ($number < 1) {
			$data = '';
		}
		else if (($number / 1024) < 1024) {
			$data = $number / 1024;
			$data = $this->getNumber($data, ['precision' => 2]);
			$data = $this->__('%s kB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}
		else if (($number / 1024 / 1024) < 1024) {
			$data = $number / 1024 / 1024;
			$data = $this->getNumber($data, ['precision' => 2]);
			$data = $this->__('%s MB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}
		else {
			$data = $number / 1024 / 1024 / 1024;
			$data = $this->getNumber($data, ['precision' => 2]);
			$data = $this->__('%s GB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}

		return $data;
	}

	public function getUsername() {

		$file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$file = array_pop($file);
		$file = array_key_exists('file', $file) ? basename($file['file']) : '';

		// backend
		if ((PHP_SAPI != 'cli') && Mage::app()->getStore()->isAdmin() && Mage::getSingleton('admin/session')->isLoggedIn())
			$user = sprintf('admin %s', Mage::getSingleton('admin/session')->getData('user')->getData('username'));
		// cron
		else if (is_object($cron = Mage::registry('current_cron')))
			$user = sprintf('cron %d - %s', $cron->getId(), $cron->getData('job_code'));
		// xyz.php
		else if ($file != 'index.php')
			$user = $file;
		// full action name
		else if (is_object($action = Mage::app()->getFrontController()->getAction()))
			$user = $action->getFullActionName();
		// frontend
		else
			$user = sprintf('frontend %s', Mage::app()->getStore()->getData('code'));

		return $user;
	}


	public function getCatalogCategoryImageDir(bool $cache = false) {
		return str_replace('/product/', '/category/', $this->getCatalogProductImageDir($cache));
	}

	public function getCatalogProductImageDir(bool $cache = false) {

		if (empty($this->_baseMediaPath))
			$this->_baseMediaPath = rtrim(Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath(), '/');

		return $this->_baseMediaPath.($cache ? '/cache/' : '/');
	}

	public function getWysiwygImageDir(bool $cache = false, bool $old = false) {

		if (empty($this->_baseWysiwygPath))
			$this->_baseWysiwygPath = rtrim(Mage::helper('cms/wysiwyg_images')->getStorageRoot(), '/');

		return $this->_baseWysiwygPath.($cache ? '/'.($old ? Mage_Cms_Model_Wysiwyg_Images_Storage::THUMBS_DIRECTORY_NAME : 'cache').'/' : '/');
	}


	public function resizeImage($product, $type, $path, int $width, int $height, bool $fixed, bool $webp = false) {

		$resource = Mage::helper('catalog/image')->init($product, $type, $path, $fixed, $webp);

		if (!is_bool($this->_usePython))
			$this->_usePython = Mage::getStoreConfigFlag('apijs/general/python');

		if ($this->_usePython)
			$resource->resize($width, $height);
		else if ($fixed)
			$resource->resize($width, $height);
		else if ($resource->getOriginalWidth() > $width)
			$resource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize($width, null);
		else if ($resource->getOriginalHeight() > $height)
			$resource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(null, $height);
		else
			$resource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize($width, $height);

		return (string) $resource;
	}

	public function getBaseImage($product, &$default, array $images, int $total, int $id = 0) {

		$mWidth  = (int) Mage::getStoreConfig('apijs/gallery/picture_width');
		$mHeight = (int) Mage::getStoreConfig('apijs/gallery/picture_height');
		$class   = '';

		// utilise l'image sélectionnée en tant qu'image de base (si l'image existe encore)
		foreach ($images as $image) {
			if ($image->getData('file') == $default) {
				$class = ($id > 0) ? 'class="slideshow.0.'.$id.'"' : '';
				$path  = $image->getData('file');
				$file  = $this->getCatalogProductImageDir().$path;
				$label = $this->escapeEntities($image->getData('label'), true);
				break;
			}
			$id++;
		}

		// sinon utilise la première image existante en tant qu'image de base
		// si l'image sélectionnée en tant qu'image de base n'existe plus
		if (!empty($images) && empty($path)) {
			$image = $images[0];
			$path  = $image->getData('file');
			$file  = $this->getCatalogProductImageDir().$path;
			$label = $this->escapeEntities($image->getData('label'), true);
			$default = $path;
		}

		// <img src srcset>
		// l'image de l'image = une miniature en cache
		// utilise l'image par défaut en tant qu'image de base si aucune image n'existe
		// utilise l'image par défaut en tant qu'image de base si le produit n'a pas d'image
		if (empty($images) || empty($path)) {
			$img1 = $this->resizeImage($product, 'image', $default, $mWidth, $mHeight, true);
			$img2 = $this->resizeImage($product, 'image', $default, $mWidth * 2, $mHeight * 2, true);
			return '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$mWidth.'" height="'.$mHeight.'" alt="" />';
		}

		// <a> <img src srcset> id=0.99999
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une miniature en cache (main)
		if ($total > 1) {
			$img0  = $this->resizeImage($product, 'image', $path, 1200, 900, false);
			$data  = '<a href="'.$img0.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false" '.$class.' id="slideshow.0.99999">';
			$img1  = $this->resizeImage($product, 'image', $path, $mWidth, $mHeight, true);
			$img2  = $this->resizeImage($product, 'image', $path, $mWidth * 2, $mHeight * 2, true);
			$data .=  '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$mWidth.'" height="'.$mHeight.'" alt="'.$label.'" />';
			$data .= '</a>';
		}
		// <a> <img src srcset> <input> id=0.0
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une miniature en cache (main)
		else {
			$img0  = $this->resizeImage($product, 'image', $path, 1200, 900, false);
			$data  = '<a href="'.$img0.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false" id="slideshow.0.0">';
			$img1  = $this->resizeImage($product, 'image', $path, $mWidth, $mHeight, true);
			$img2  = $this->resizeImage($product, 'image', $path, $mWidth * 2, $mHeight * 2, true);
			$data .=  '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$mWidth.'" height="'.$mHeight.'" alt="'.$label.'" />';
			$data .=  '<input type="hidden" value="false|false|'.$label.'" />';
			$data .= '</a>';
		}

		return $data;
	}

	public function getThumbnail($product, $default, object $image, int $id) {

		$tWidth  = (int) Mage::getStoreConfig('apijs/gallery/thumbnail_width');
		$tHeight = (int) Mage::getStoreConfig('apijs/gallery/thumbnail_height');
		$mWidth  = (int) Mage::getStoreConfig('apijs/gallery/picture_width');
		$mHeight = (int) Mage::getStoreConfig('apijs/gallery/picture_height');

		$path  = $image->getData('file');
		$file  = $this->getCatalogProductImageDir().$path;
		$label = $this->escapeEntities($image->getData('label'), true);
		$class = ($path == $default) ? 'class="current"' : '';

		// <a> <img src srcset> <input> id=0.$id
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une petite miniature en cache (thumb)
		// l'image de l'input = une miniature en cache (main)
		$img0  = $this->resizeImage($product, 'image', $path, 1200, 900, false);
		$data  = '<a href="'.$img0.'" type="'.mime_content_type($file).'" onclick="return false" '.$class.' id="slideshow.0.'.$id.'">';
		$img1  = $this->resizeImage($product, 'thumbnail', $path, $tWidth, $tHeight, true);
		$img2  = $this->resizeImage($product, 'thumbnail', $path, $tWidth * 2, $tHeight * 2, true);
		$data .=  '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$tWidth.'" height="'.$tHeight.'" alt="'.$label.'" />';
		$img1  = $this->resizeImage($product, 'image', $path, $mWidth, $mHeight, true);
		$img2  = $this->resizeImage($product, 'image', $path, $mWidth * 2, $mHeight * 2, true);
		$data .=  '<input type="hidden" value="'.$img1.';'.$img2.' 2x|false|false|'.$label.'" />';
		$data .= '</a>';

		return $data;
	}


	public function getMaxSizes(bool $dump = false) {

		// return "one file, all files"
		// config admise en Mo
		if ($dump) {
			return [
				'config.xml one_max_size' => (int) Mage::getStoreConfig('apijs/general/one_max_size'),
				'config.xml all_max_size' => (int) Mage::getStoreConfig('apijs/general/all_max_size'),
				'php upload_max_filesize' => (int) ini_get('upload_max_filesize'),
				'php post_max_size'       => (int) ini_get('post_max_size'),
			];
		}

		return min(
			(int) Mage::getStoreConfig('apijs/general/one_max_size'),
			(int) ini_get('upload_max_filesize'),
			(int) ini_get('post_max_size')
		).', '.min(
			(int) Mage::getStoreConfig('apijs/general/all_max_size'),
			(int) ini_get('upload_max_filesize'),
			(int) ini_get('post_max_size')
		);
	}

	public function getTabName($product = null) {

		$product = is_object($product) ? $product : Mage::registry('current_product');
		$groups  = Mage::getResourceModel('eav/entity_attribute_group_collection')
			->setAttributeSetFilter($product->getData('attribute_set_id'))
			->load();

		foreach ($groups as $group) {
			$attributes = $product->getAttributes($group->getId(), true);
			foreach ($attributes as $attribute) {
				if (in_array($attribute->getData('attribute_code'), ['media_gallery', 'gallery']))
					return 'group_'.$group->getId();
			}
		}

		return null;
	}

	public function renderGalleryBlock(object $product) {

		Mage::register('current_product', $product);

		$block = Mage::getBlockSingleton('apijs/adminhtml_rewrite_gallery');
		$block->setElement($block);

		return $block->toHtml();
	}


	protected function searchAndRemoveFiles(string $directory, string $file, array $filesCache = []) {

		// supprime les fichiers
		if (!empty($filesCache)) {

			// for apijs-clean-images.php
			$file = '/'.trim($file, '/');
			foreach ($filesCache as $fileCache) {
				if ((mb_stripos($fileCache, $file) !== false) && (mb_stripos($fileCache, $directory) !== false)) {
					echo '  ',$fileCache,"\n";
					unlink($fileCache);
				}
			}
		}
		else {
			if (mb_stripos($file, '/') === false)
				$cmd = 'find '.escapeshellarg($directory).' -name '.escapeshellarg($file).' -type f -delete';
			else
				$cmd = 'find '.escapeshellarg($directory).' -wholename '.escapeshellarg('*/'.trim($file, '/')).' -type f -delete';

			Mage::log($cmd, Zend_Log::DEBUG, 'apijs.log');
			exec($cmd);
		}

		// supprime aussi les éventuels fichiers webp
		// mais uniquement dans le dossier cache
		$webp = str_ireplace(['.jpg', '.jpeg', '.png', '.gif'], '.webp', $file);
		if ($file != $webp) {

			if (mb_stripos($directory, '/cache') === false)
				$directory .= '/cache';

			if (is_dir($directory)) {

				if (!empty($filesCache)) {

					// for apijs-clean-images.php
					foreach ($filesCache as $fileCache) {
						if ((mb_stripos($fileCache, $webp) !== false) && (mb_stripos($fileCache, $directory) !== false)) {
							echo '  ',$fileCache,"\n";
							unlink($fileCache);
						}
					}
				}
				else {
					if (mb_stripos($webp, '/') === false)
						$cmd = 'find '.escapeshellarg($directory).' -name '.escapeshellarg($webp).' -type f -delete';
					else
						$cmd = 'find '.escapeshellarg($directory).' -wholename '.escapeshellarg('*/'.trim($webp, '/')).' -type f -delete';

					Mage::log($cmd, Zend_Log::DEBUG, 'apijs.log');
					exec($cmd);
				}
			}
		}
	}

	public function removeFiles(string $directory, string $file, bool $now = false, array $filesCache = []) {

		if (empty($this->_baseMediaDir))
			$this->_baseMediaDir = realpath(Mage::getBaseDir('media'));

		// when $filesCache is set (from apijs-clean-images.php) $directory is already realpath()
		$directory = empty($filesCache) ? realpath($directory) : $directory;

		// search and remove all files with find command
		// if the file name contains simple characters and only in the media folder
		if (
			($directory != $this->_baseMediaDir) &&
			!empty($directory) && is_dir($directory) &&
			(mb_stripos($directory, $this->_baseMediaDir) === 0) &&
			(preg_match('#[\w\-]+\.\w+$#', $file) === 1)
		) {
			if ($now)
				$this->searchAndRemoveFiles($directory, $file, $filesCache);
			else
				$this->_filesToRemove[] = [$directory => $file];
		}
	}

	public function __destruct() {

		if (!empty($this->_filesToRemove)) {
			foreach ($this->_filesToRemove as $files) {
				foreach ($files as $directory => $file)
					$this->searchAndRemoveFiles($directory, $file);
			}
		}
	}
}