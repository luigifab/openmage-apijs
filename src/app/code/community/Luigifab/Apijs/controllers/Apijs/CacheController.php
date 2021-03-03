<?php
/**
 * Created M/07/01/2020
 * Updated V/12/02/2021
 *
 * Copyright 2008-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2019-2021 | Fabrice Creuzot <fabrice~cellublue~com>
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

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml').'/System/ConfigController.php');

class Luigifab_Apijs_Apijs_CacheController extends Mage_Adminhtml_System_ConfigController {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('system/config/apijs');
	}

	public function getUsedModuleName() {
		return 'Luigifab_Apijs';
	}

	public function clearCacheAction() {

		$type = $this->getRequest()->getParam('type');

 		try {
			if ($type == 'wysiwyg') {

				// thumb
				$dir = trim(Mage::helper('apijs')->getWysiwygImageDir(true, true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				// cache
				$dir = trim(Mage::helper('apijs')->getWysiwygImageDir(true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
			else if ($type == 'product') {

				// cache
				$dir = trim(Mage::helper('apijs')->getCatalogProductImageDir(true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
			else if ($type == 'category') {

				// cache
				$dir = trim(Mage::helper('apijs')->getCatalogCategoryImageDir(true));
				$iof = new Varien_Io_File();
				if (mb_strlen($dir) > 5)
					$iof->rmdir($dir, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
		}
		catch (Throwable $e) {
			$this->_getSession()->addError($e->getMessage());
		}

		// très important car les chemins et les urls sont aussi mis en cache
		Mage::app()->getCacheInstance()->cleanType('block_html');

		$this->_redirect('*/system_config/edit', ['section' => 'apijs']);
	}
}