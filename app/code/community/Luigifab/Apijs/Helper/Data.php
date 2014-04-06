<?php
/**
 * Created D/20/11/2011
 * Updated J/20/03/2014
 * Version 3
 *
 * Copyright 2011-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Apijs_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Apijs')->version;
	}

	public function getStatus($type = 'frontend') {
		return (Mage::getStoreConfig('apijs/general/'.$type) === '1') ? $this->__('Enabled') : $this->__('Disabled');
	}
}