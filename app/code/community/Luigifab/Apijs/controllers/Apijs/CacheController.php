<?php
/**
 * Created M/07/01/2020
 * Updated M/07/01/2020
 *
 * Copyright 2008-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2019      | Fabrice Creuzot <fabrice~cellublue~com>
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

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml').'/System/ConfigController.php');

class Luigifab_Apijs_Apijs_CacheController extends Mage_Adminhtml_System_ConfigController {

	public function clearCacheAction() {

		$this->setUsedModuleName('Luigifab_Apijs');
		$type = $this->getRequest()->getParam('type');

 		try {
			if ($type == 'wysiwyg') {

				// thumb
				$directory = trim(Mage::helper('apijs')->getWysiwygImageDir(true, true));
				$io = new Varien_Io_File();
				if (mb_strlen($directory) > 5)
					$io->rmdir($directory, true);

				// cache
				$directory = trim(Mage::helper('apijs')->getWysiwygImageDir(true));
				$io = new Varien_Io_File();
				if (mb_strlen($directory) > 5)
					$io->rmdir($directory, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
			else if ($type == 'product') {

				// cache
				$directory = trim(Mage::helper('apijs')->getCatalogProductImageDir(true));
				$io = new Varien_Io_File();
				if (mb_strlen($directory) > 5)
					$io->rmdir($directory, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
			else if ($type == 'category') {

				// cache
				$directory = trim(Mage::helper('apijs')->getCatalogCategoryImageDir(true));
				$io = new Varien_Io_File();
				if (mb_strlen($directory) > 5)
					$io->rmdir($directory, true);

				$this->_getSession()->addSuccess($this->__('The image cache was cleaned.'));
			}
		}
		catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}

		$this->_redirect('*/system_config/edit', ['section' => 'apijs']);
	}
}