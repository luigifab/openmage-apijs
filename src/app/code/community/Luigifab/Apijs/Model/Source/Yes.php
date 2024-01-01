<?php
/**
 * Created J/19/10/2023
 * Updated J/19/10/2023
 *
 * Copyright 2008-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Model_Source_Yes {

	protected $_options;

	public function toOptionArray() {

		if (empty($this->_options)) {
			$this->_options = [
				['value' => 1, 'label' => Mage::helper('adminhtml')->__('Yes').' *'],
			];
		}

		return $this->_options;
	}
}