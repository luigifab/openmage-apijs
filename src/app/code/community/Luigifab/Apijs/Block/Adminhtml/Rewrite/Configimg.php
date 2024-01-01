<?php
/**
 * Created L/26/10/2015
 * Updated J/19/10/2023
 *
 * Copyright 2008-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Configimg extends Mage_Adminhtml_Block_System_Config_Form_Field_Image {

	protected function _construct() {
		$this->setModuleName('Mage_Adminhtml');
	}

	public function getAllowedExtensions() {

		$exts = Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions('image');

		$model = (string) $this->getFieldConfig()->descend('backend_model');
		if (!empty($model))
			return array_intersect($exts, Mage::getModel($model)->getAllowedExtensions());

		return $exts;
	}

	public function getElementHtml() {

		if (Mage::getStoreConfigFlag('apijs/general/backend')) {

			$this->setData('class', 'input-file');
			$html = '<div class="image preview">'.Varien_Data_Form_Element_Abstract::getElementHtml();

			if ($this->getValue()) {
				$link  = $this->_getUrl();
				$link  = str_replace('wysiwyg//', 'wysiwyg/', (mb_stripos($link, 'http') === 0) ? $link : Mage::getBaseUrl('media').$link);
				$html .= sprintf(' <a href="%s" onclick="apijs.dialog.dialogPhoto(this.href, \'false\', \'false\', \'%s\'); return false;" id="%s_image">%s (%s)</a> ', $link, addslashes($this->getValue()), $this->getHtmlId(), Mage::helper('apijs')->__('Preview'), $this->getValue()); // pas de $this->helper ici
			}

			return sprintf('%s <em>(%s)</em> %s</div>', $html, implode(', ', $this->getAllowedExtensions()), $this->_getDeleteCheckbox());
		}

		return parent::getElementHtml();
	}
}