<?php
/**
 * Created M/07/01/2020
 * Updated M/07/01/2020
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

class Luigifab_Apijs_Model_Source_Type {

	public function toOptionArray() {

		$config  = Mage::getConfig()->getNode('global/models/apijs/adaptators')->asArray();
		$options = [];

		foreach ($config as $code => $key) {
			$options[$key] = ['value' => $key, 'label' => $key];
		}

		ksort($options);
		return $options;
	}
}