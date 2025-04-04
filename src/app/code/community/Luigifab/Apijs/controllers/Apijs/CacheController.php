<?php
/**
 * Created M/07/01/2020
 * Updated S/11/11/2023
 *
 * Copyright 2008-2025 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml').'/System/ConfigController.php');

class Luigifab_Apijs_Apijs_CacheController extends Mage_Adminhtml_System_ConfigController {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('system/config/apijs');
	}

	public function getUsedModuleName() {
		return 'Luigifab_Apijs';
	}

	public function clearCacheAction() {

		$helper = Mage::helper('apijs');
		$type   = $this->getRequest()->getParam('type');

 		try {
			if ($type == 'wysiwyg') {

				// thumb
				$dir = trim($helper->getWysiwygImageDir(true, true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				// cache
				$dir = trim($helper->getWysiwygImageDir(true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
			else if ($type == 'product') {

				// cache
				$dir = trim($helper->getCatalogProductImageDir(true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
			else if ($type == 'category') {

				// cache
				$dir = trim($helper->getCatalogCategoryImageDir(true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
		}
		catch (Throwable $t) {
			$this->_getSession()->addError($t->getMessage());
		}

		// très important car les chemins et les URLs sont aussi mis en cache
		Mage::app()->getCacheInstance()->cleanType('block_html');

		$this->_redirect('*/system_config/edit', ['section' => 'apijs']);
	}
}