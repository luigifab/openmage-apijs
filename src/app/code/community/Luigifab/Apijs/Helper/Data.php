<?php
/**
 * Created D/20/11/2011
 * Updated D/20/09/2020
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

class Luigifab_Apijs_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Apijs')->version;
	}

	public function _(string $data, ...$values) {
		return (mb_stripos($txt = $this->__(' '.$data, ...$values), ' ') === 0) ? $this->__($data, ...$values) : $txt;
	}

	public function escapeEntities($data, bool $quotes = false) {
		return htmlspecialchars($data, $quotes ? ENT_SUBSTITUTE | ENT_COMPAT : ENT_SUBSTITUTE | ENT_NOQUOTES);
	}

	public function getNumberToHumanSize(int $number) {

		if ($number < 1) {
			$data = '';
		}
		else if (($number / 1024) < 1024) {
			$data = $number / 1024;
			$data = Zend_Locale_Format::toNumber($data, ['precision' => 2]);
			$data = $this->__('%s kB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}
		else if (($number / 1024 / 1024) < 1024) {
			$data = $number / 1024 / 1024;
			$data = Zend_Locale_Format::toNumber($data, ['precision' => 2]);
			$data = $this->__('%s MB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}
		else {
			$data = $number / 1024 / 1024 / 1024;
			$data = Zend_Locale_Format::toNumber($data, ['precision' => 2]);
			$data = $this->__('%s GB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}

		return $data;
	}


	public function getCatalogProductImageDir(bool $cache = false) {
		return rtrim(Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath(), '/').($cache ? '/cache/' : '/');
	}

	public function getCatalogCategoryImageDir(bool $cache = false) {
		return str_replace('/product/', '/category/', $this->getCatalogProductImageDir($cache));
	}

	public function getWysiwygImageDir(bool $cache = false, bool $old = false) {
		$dir = $old ? Mage_Cms_Model_Wysiwyg_Images_Storage::THUMBS_DIRECTORY_NAME : 'cache';
		return rtrim(Mage::helper('cms/wysiwyg_images')->getStorageRoot(), '/').($cache ? '/'.$dir.'/' : '/');
	}


	public function resizeImage($product, $type, $path, $width, $height, $fixed) {

		$resource = Mage::helper('catalog/image')->init($product, $type, $path, $fixed);

		if ($fixed)
			$resource->resize($width, $height);
		else if ($resource->getOriginalWidth() > $width)
			$resource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize($width, null);
		else if ($resource->getOriginalHeight() > $height)
			$resource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(null, $height);
		else
			$resource->resize($width, $height);

		return (string) $resource;
	}

	public function getBaseImage($product, &$default, $images, $total, $id = 0) {

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

	public function getThumbnail($product, $default, $image, $id) {

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

	public function removeFiles(string $dir, string $file) {

		// recherche tous les fichiers avec la commande find
		// si le nom du fichier contient des caractères simples
		if (Mage::getStoreConfigFlag('apijs/general/remove_cache') && (preg_match('#[\w\-]+\.\w+$#', $file) === 1)) {

			Mage::log(sprintf('Remove all %s images with exec(find) in %s', $file, $dir), Zend_Log::INFO, 'apijs.log');

			if (mb_stripos($file, '/') === false)
				exec('find '.escapeshellarg($dir).' -name '.escapeshellarg($file).' | xargs rm');
			else
				exec('find '.escapeshellarg($dir).' -wholename '.escapeshellarg('*/'.$file).' | xargs rm');
		}
	}

	public function getMaxSizes(bool $dump = false) {

		// config admise en Mo, "one file, all files"
		if ($dump === true) {
			return [
				'config.xml one_max_size' => (int) Mage::getStoreConfig('apijs/general/one_max_size'),
				'config.xml all_max_size' => (int) Mage::getStoreConfig('apijs/general/all_max_size'),
				'php upload_max_filesize' => (int) ini_get('upload_max_filesize'),
				'php post_max_size'       => (int) ini_get('post_max_size')
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
		$egroups = Mage::getResourceModel('eav/entity_attribute_group_collection')
			->setAttributeSetFilter($product->getData('attribute_set_id'))
			->load();

		foreach ($egroups as $group) {
			$attributes = $product->getAttributes($group->getId(), true);
			foreach ($attributes as $key => $attribute) {
				if (in_array($attribute->getData('attribute_code'), ['media_gallery', 'gallery']))
					return 'group_'.$group->getId();
			}
		}

		return null;
	}

	public function renderGalleryBlock(Mage_Catalog_Model_Product $product) {

		Mage::register('current_product', $product);

		$block = Mage::getBlockSingleton('apijs/adminhtml_rewrite_gallery');
		$block->setElement($block);

		return $block->toHtml();
	}
}