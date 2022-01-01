<?php
/**
 * Created S/04/10/2014
 * Updated V/29/10/2021
 *
 * Copyright 2008-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Gallery extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content {

	protected function _construct() {
		$this->setModuleName('Mage_Adminhtml');
	}

	public function __construct() {

		parent::__construct();
		$product = Mage::registry('current_product');

		if (!empty($product) && !empty($product->getId()) && Mage::getStoreConfigFlag('apijs/general/backend'))
			$this->setTemplate('luigifab/apijs/gallery.phtml'); // catalog/product/helper/gallery.phtml
	}

	public function getImages(bool $sortByStore) {

		$product    = Mage::registry('current_product');
		$productId  = $product->getId();
		$storeId    = $product->getStoreId();
		$attributes = $product->getMediaAttributes();

		$defaultValues = [];
		$globalValues  = [];
		$storeValues   = [];

		$values = $product->getMediaGallery('images');
		$values = empty($values) ? [] : $values;
		$images = [];
		$counts = [];

		foreach ($values as $image) {

			$image = is_object($image) ? $image : new Varien_Object($image);
			$images[$image->getData('file')] = $image;

			if (empty($storeId))
				$image->setData('position', (int) $image->getData('position'));
			else if ($image->getData('position') != $image->getData('position_default'))
				$image->setData('position', (int) $image->getData('position'));
			else
				$image->setData('position', (int) $image->getData('position_default'));

			$image->setData('apijs_group', $sortByStore ? (int) ($image->getData('position') / 100) * 100 : 0);

			if (array_key_exists($image->getData('apijs_group'), $counts))
				$counts[$image->getData('apijs_group')]++;
			else
				$counts[$image->getData('apijs_group')] = 1;
		}

		$ids = [];
		foreach ($attributes as $code => $attribute) {
			if (($attribute->getIsText() !== true) && ($attribute->getIsCheckbox() !== true)) {
				$ids[] = $attribute->getId();
				$globalValues[$code] = $product->getResource()->getAttributeRawValue($productId, $code, 0);
				$storeValues[$code]  = $product->getResource()->getAttributeRawValue($productId, $code, $storeId);
			}
		}

		$database = Mage::getSingleton('core/resource');
		$reader   = $database->getConnection('core_read');
		$table    = $database->getTableName('catalog_product_entity_varchar');
		$values   = $reader->fetchAll('SELECT store_id, attribute_id, value FROM '.$table.' WHERE entity_id = '.$productId.' AND attribute_id IN ('.implode(',', $ids).')');

		foreach ($values as $value) {
			if (!empty($images[$value['value']])) {
				$image = $images[$value['value']];
				if (floor($image->getData('position') / 100) == $value['store_id'])
					$defaultValues[$value['attribute_id']][$value['store_id']] = $value['value'];
			}
		}

		return ['images' => $images, 'counts' => $counts, 'defaultValues' => $defaultValues, 'storeValues' => $storeValues, 'globalValues' => $globalValues];
	}

	public function getScopeLabel(object $attribute) {

		if ($attribute->isScopeGlobal())
			return $this->__('[GLOBAL]');
		if ($attribute->isScopeWebsite())
			return $this->__('[WEBSITE]');

		return $this->__('[STORE VIEW]');
	}

	public function isUseGlobal(object $image, string $field, string $value) {
		return empty($image->getData($field.'_global')) ? '' : $value.'="'.$value.'"';
	}

	public function getAddUrl() {
		$product = Mage::registry('current_product');
		return $this->getUrl('*/apijs_media/uploadProduct',
			['product' => $product->getId(), 'store' => $product->getStoreId(), 'form_key' => $this->getFormKey()]);
	}

	public function getSaveUrl() {
		$product = Mage::registry('current_product');
		return $this->getUrl('*/apijs_media/save',
			['product' => $product->getId(), 'store' => $product->getStoreId(), 'form_key' => $this->getFormKey()]);
	}

	public function getRemoveUrl($imageId) {
		$product = Mage::registry('current_product');
		return $this->getUrl('*/apijs_media/remove',
			['product' => $product->getId(), 'store' => $product->getStoreId(), 'image' => $imageId]);
	}
}