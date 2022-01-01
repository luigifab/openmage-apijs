<?php
/**
 * Created M/06/10/2020
 * Updated M/06/10/2020
 *
 * Copyright 2008-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Additional extends Mage_Adminhtml_Block_Cache_Additional {

	protected function _construct() {
		$this->setModuleName('Mage_Adminhtml');
	}

	public function getCleanImagesUrl() {
		return $this->getUrl('*/system_config/edit', ['section' => 'apijs']);
	}
}