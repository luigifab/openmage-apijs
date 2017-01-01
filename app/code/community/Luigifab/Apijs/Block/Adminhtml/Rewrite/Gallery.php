<?php
/**
 * Created S/04/10/2014
 * Updated M/08/11/2016
 *
 * Copyright 2008-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/apijs
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
		$id = Mage::registry('current_product')->getId();

		if (Mage::getStoreConfigFlag('apijs/general/backend') && ($id > 0) && ($this->getRequest()->getParam('old', false) === false))
			$this->setTemplate('luigifab/apijs/gallery.phtml');
	}

	public function _toHtml() {

		$id = Mage::registry('current_product')->getId();

		if (Mage::getStoreConfigFlag('apijs/general/backend') && ($id > 0)) {

			if ($this->getRequest()->getParam('old', false) === false) {
				$url = $this->helper('apijs')->getDirectTabLink($id, true);
				$text = $this->__('Back to Magento default gallery');
			}
			else {
				$url = $this->helper('apijs')->getDirectTabLink($id);
				$text = $this->__('See the apijs gallery');
			}

			return '<a href="'.$url.'" class="apijslink">'.$text.'</a>'.parent::_toHtml();
		}
		else {
			return parent::_toHtml();
		}
	}

	public function getMaxSize() {
		$max = min(intval(ini_get('upload_max_filesize')), intval(ini_get('post_max_size')));
		return ($max > 9) ? 9 : $max;
	}

	public function getAddUrl($productId, $storeId) {
		$key = Mage::getSingleton('core/session')->getFormKey();
		return $this->getUrl('*/apijs_media/uploadProduct', array('product' => $productId, 'store' => $storeId, 'form_key' => $key));
	}

	public function getSaveUrl($productId, $storeId, $imageId) {
		$key = Mage::getSingleton('core/session')->getFormKey();
		return $this->getUrl('*/apijs_media/save', array('product' => $productId, 'image' => $imageId, 'store' => $storeId, 'form_key' => $key));
	}

	public function getDownloadUrl($productId, $imageId) {
		return $this->getUrl('*/apijs_media/download', array('product' => $productId, 'image' => $imageId));
	}

	public function getDeleteUrl($productId, $imageId) {
		return $this->getUrl('*/apijs_media/delete', array('product' => $productId, 'image' => $imageId));
	}

	public function getScopeLabel($attribute) {

		if ($attribute->isScopeGlobal())
			return $this->__('[GLOBAL]');
		else if ($attribute->isScopeWebsite())
			return $this->__('[WEBSITE]');
		else
			return $this->__('[STORE VIEW]');
	}
}