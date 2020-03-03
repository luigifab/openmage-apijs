<?php
/**
 * Created L/26/10/2015
 * Updated M/21/01/2020
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Config extends Mage_Adminhtml_Block_System_Config_Form_Field_Image {

	protected function _construct() {
		$this->setModuleName('Mage_Adminhtml');
	}

	public function getElementHtml() {

		if (Mage::getStoreConfigFlag('apijs/general/backend')) {

			$this->setData('class', 'input-file');
			$html = '';

			if ($this->getValue()) {
				$url   = $this->_getUrl();
				$url   = (mb_stripos($url, 'http') === 0) ? $url : Mage::getBaseUrl('media').$url;
				$html .= sprintf(' <a href="%s" onclick="apijs.dialog.dialogPhoto(this.href, \'false\', \'false\', \'%s\'); return false;" id="%s_image">%s</a> ', $url, addslashes($this->getValue()), $this->getHtmlId(), $this->helper('apijs')->__('Preview'));
			}

			$html .= Varien_Data_Form_Element_Abstract::getElementHtml();
			$html .= $this->_getDeleteCheckbox();

			return $html;
		}

		return parent::getElementHtml();
	}
}