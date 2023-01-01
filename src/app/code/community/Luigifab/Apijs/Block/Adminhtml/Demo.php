<?php
/**
 * Created D/20/11/2011
 * Updated S/03/12/2022
 *
 * Copyright 2008-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Block_Adminhtml_Demo extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	protected $_template = 'luigifab/apijs/demo.phtml';

	public function render(Varien_Data_Form_Element_Abstract $element) {
		// getPath PR 2774
		$isDefault = !$element->getCanUseWebsiteValue() && !$element->getCanUseDefaultValue();
		return '<tr><td colspan="'.(empty($element->getPath()) ? ($isDefault ? 4 : 5) : ($isDefault ? 5 : 6)).'">'.$this->toHtml().'</td></tr>';
	}
}