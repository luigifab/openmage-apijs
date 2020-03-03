<?php
/**
 * Created V/11/10/2019
 * Updated V/11/10/2019
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

class Luigifab_Apijs_Block_Browser extends Mage_Core_Block_Template {

	public function getBrowserData() {
		return Mage::getSingleton('apijs/useragentparser')->parse();
	}

	public function getCacheKeyInfo() {
		return null;
	}

	public function getCacheKey() {
		return null;
	}

	public function getCacheTags() {
		return null;
	}

	public function getCacheLifetime() {
		return null;
	}
}