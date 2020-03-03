<?php
/**
 * Created D/20/11/2011
 * Updated J/23/01/2020
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
			$data = $this->__('%s kB', str_replace(['.00', ',00'], '', $data));
		}
		else if (($number / 1024 / 1024) < 1024) {
			$data = $number / 1024 / 1024;
			$data = Zend_Locale_Format::toNumber($data, ['precision' => 2]);
			$data = $this->__('%s MB', str_replace(['.00', ',00'], '', $data));
		}
		else {
			$data = $number / 1024 / 1024 / 1024;
			$data = Zend_Locale_Format::toNumber($data, ['precision' => 2]);
			$data = $this->__('%s GB', str_replace(['.00', ',00'], '', $data));
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


	public function resizeImage($product, $type, $path, $width, $height = null, bool $min = false) {

		$resource = Mage::helper('catalog/image')->init($product, is_object($product) ? $type : 'wysiwyg', $path);

		if (!$min)
			$resource->resize($width, $height);
		else if ($resource->getOriginalWidth() > $width)
			$resource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize($width, null);
		else if ($resource->getOriginalHeight() > $height)
			$resource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(null, $height);

		return (string) $resource;
	}

	public function deletedFiles($dir, $filename, $txt) {

		// recherche tous les fichiers avec la commande find
		// uniquement si le nom du fichier contient des caractères simples
		if (preg_match('#[\w\-]+\.\w+$#', $filename) === 1) {

			Mage::log($txt, Zend_Log::INFO, 'apijs.log');

			if (mb_stripos($filename, '/') === false)
				exec('find '.$dir.' -name '.escapeshellarg($filename).' | xargs rm');
			else
				exec('find '.$dir.' -wholename '.escapeshellarg('*/'.$filename).' | xargs rm');
		}
	}

	public function getMaxSizes() {

		// config admise en Mo, maxsize et multiplemaxsize
		return min(20, (int) ini_get('upload_max_filesize'), (int) ini_get('post_max_size')).', '.
			min((int) ini_get('upload_max_filesize'), (int) ini_get('post_max_size'));
	}

	public function getTabName($product = null) {

		$product = is_object($product) ? $product : Mage::registry('current_product');
		$groups  = Mage::getResourceModel('eav/entity_attribute_group_collection')
			->setAttributeSetFilter($product->getData('attribute_set_id'))
			->load();

		foreach ($groups as $group) {
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