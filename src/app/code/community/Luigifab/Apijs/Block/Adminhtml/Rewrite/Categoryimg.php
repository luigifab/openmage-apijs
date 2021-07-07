<?php
/**
 * Created L/30/03/2020
 * Updated D/30/05/2021
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Categoryimg extends Mage_Adminhtml_Block_Catalog_Category_Helper_Image {

	protected function _construct() {
		$this->setModuleName('Mage_Adminhtml');
	}

	public function getElementHtml() {

		if (Mage::getStoreConfigFlag('apijs/general/backend')) {

			$this->setData('class', 'input-file');
			$html = '<div class="image preview">'.Varien_Data_Form_Element_Abstract::getElementHtml();

			if ($this->getValue()) {
				$link  = $this->_getUrl();
				$link  = (mb_stripos($link, 'http') === 0) ? $link : Mage::getBaseUrl('media').$link;
				$html .= sprintf(' <a href="%s" onclick="apijs.dialog.dialogPhoto(this.href, \'false\', \'false\', \'%s\'); return false;" id="%s_image">%s (%s)</a> ', $link, addslashes($this->getValue()), $this->getHtmlId(), Mage::helper('apijs')->__('Preview'), $this->getValue()); // pas de this->helper ici
			}

			return $html.$this->_getDeleteCheckbox().'</div>';
		}

		return parent::getElementHtml();
	}
}