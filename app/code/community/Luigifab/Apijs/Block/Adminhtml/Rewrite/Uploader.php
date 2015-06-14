<?php
/**
 * Created V/22/05/2015
 * Updated V/22/05/2015
 * Version 1
 *
 * Copyright 2008-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Uploader extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content_Uploader {

	protected function _construct() {
		$this->setModuleName('Mage_Adminhtml');
	}

	public function _toHtml() {

		if (Mage::getStoreConfig('apijs/general/backend') === '1')
			return '<button type="button" class="scalable add" onclick="apijsSendFile(\''.$this->getAddUrl().'\', '.$this->getMaxSize().');">'.$this->__('Add an image').'</button>';
		else
			return parent::_toHtml();
	}

	private function getMaxSize() {
		$max = max(intval(ini_get('upload_max_filesize')), intval(ini_get('post_max_size')));
		return ($max > 9) ? 9 : $max;
	}

	private function getAddUrl() {
		return $this->helper('adminhtml')->getUrl('*/apijs_media/uploadWidget', array(
			'form_key' => Mage::getSingleton('core/session')->getFormKey()));
	}
}