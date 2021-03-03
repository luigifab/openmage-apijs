<?php
/**
 * Created S/04/10/2014
 * Updated L/06/07/2020
 *
 * Copyright 2008-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

	public function getScopeLabel($attribute) {

		if ($attribute->isScopeGlobal())
			return $this->__('[GLOBAL]');
		else if ($attribute->isScopeWebsite())
			return $this->__('[WEBSITE]');
		else
			return $this->__('[STORE VIEW]');
	}

	public function isUseGlobal($image, $field, $value) {
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