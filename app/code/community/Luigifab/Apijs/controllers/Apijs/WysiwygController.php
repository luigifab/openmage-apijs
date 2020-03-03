<?php
/**
 * Created M/10/09/2019
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

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml').'/Cms/Wysiwyg/ImagesController.php');

class Luigifab_Apijs_Apijs_WysiwygController extends Mage_Adminhtml_Cms_Wysiwyg_ImagesController {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/apijs');
	}

	public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true) {
		return parent::loadLayout(($ids === false) ? null : str_replace('apijs_wysiwyg', 'cms_wysiwyg_images', $this->getFullActionName()), $generateBlocks, $generateXml);
	}

	public function indexAction() {

		$dir = Mage::helper('apijs')->getWysiwygImageDir();
		if (!is_dir($dir))
			@mkdir($dir, 0755);

		$this->_initAction()->loadLayout(false)->_setActiveMenu('tools/apijs');
		$this->getLayout()->getBlock('root')->setContainerCssClass('popup-window');
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->renderLayout();
	}

	public function deleteFolderAction() {

		$files = $this->getStorage()->getFilesCollection($dir = $this->getStorage()->getSession()->getCurrentPath());
		$path  = str_replace([Mage::getBaseDir('media').'/', '//'], ['', '/'], $dir);

		foreach ($files as $file) {

			$filename = trim($path, '/').'/'.$file->getName();
			Mage::helper('apijs')->deletedFiles(
				Mage::helper('apijs')->getWysiwygImageDir(true), $filename,
				sprintf('Remove all %s images with exec(find)', $filename));
		}

		parent::deleteFolderAction();

		if (is_dir($dir))
			unlink($dir);
	}

	public function deleteFilesAction() {

		parent::deleteFilesAction();

		if (!empty($files = $this->getRequest()->getParam('files'))) {

			$files = Mage::helper('core')->jsonDecode($files);
			$path  = str_replace([Mage::getBaseDir('media').'/', '//'], ['', '/'], $this->getStorage()->getSession()->getCurrentPath());

			foreach ($files as $file) {
				$filename = trim($path, '/').'/'.Mage::helper('cms/wysiwyg_images')->idDecode($file);
				Mage::helper('apijs')->deletedFiles(
					Mage::helper('apijs')->getWysiwygImageDir(true), $filename,
					sprintf('Remove all %s images with exec(find)', $filename));
			}
		}
	}
}