<?php
/**
 * Created L/26/10/2015
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Field extends Mage_Adminhtml_Block_System_Config_Form_Field_Image {

	protected function _construct() {
		$this->setModuleName('Mage_Adminhtml');
	}

	public function getElementHtml() {

		if (Mage::getStoreConfigFlag('apijs/general/backend')) {

			$html = '';

			if ($this->getValue()) {

				$url = $this->_getUrl();
				if (!preg_match('/^http\:\/\/|https\:\/\//', $url))
					$url = Mage::getBaseUrl('media').$url;

				$html .= '<a href="'.$url.'" onclick="apijs.dialog.dialogPhoto(this.href, this.firstChild.getAttribute(\'alt\'), \'false\', \'\'); return false;"><img src="'.$url.'" id="'.$this->getHtmlId().'_image" width="22" height="22" alt="'.$this->getValue().'" class="small-image-preview v-middle" /></a> ';
			}

			$this->setClass('input-file');

			$html .= Varien_Data_Form_Element_Abstract::getElementHtml();
			$html .= $this->_getDeleteCheckbox();

			return $html;
		}
		else {
			return parent::getElementHtml();
		}
	}
}