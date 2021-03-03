<?php
/**
 * Created M/10/09/2019
 * Updated M/02/02/2021
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

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml').'/Cms/Wysiwyg/ImagesController.php');

class Luigifab_Apijs_Apijs_WysiwygController extends Mage_Adminhtml_Cms_Wysiwyg_ImagesController {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/apijs');
	}

	public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true) {
		return parent::loadLayout(($ids === false) ? null : str_replace('apijs_wysiwyg', 'cms_wysiwyg_images', $this->getFullActionName()), $generateBlocks, $generateXml);
	}

	public function getUsedModuleName() {
		return 'Luigifab_Apijs';
	}

	public function indexAction() {

		$dir = Mage::helper('apijs')->getWysiwygImageDir();
		if (!is_dir($dir))
			@mkdir($dir, 0755);

		$this->_title($this->__('Tools'))->_title($this->__('Media Storage'));
		$this->_initAction()->loadLayout(false)->_setActiveMenu('tools/apijs');
		$this->getLayout()->getBlock('root')->setContainerCssClass('popup-window');
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->renderLayout();
	}

	public function deleteFolderAction() {

		$files = $this->getStorage()->getFilesCollection($dir = $this->getStorage()->getSession()->getCurrentPath());
		$path  = 'wysiwyg/'.str_replace([Mage::helper('apijs')->getWysiwygImageDir(), Mage::getBaseDir('media').'/', '//'], ['', '', '/'], $dir);

		foreach ($files as $file) {
			$oldfile = trim($path, '/').'/'.$file->getName();
			Mage::helper('apijs')->removeFiles(Mage::helper('apijs')->getWysiwygImageDir(true), $oldfile);
		}

		parent::deleteFolderAction();

		if (is_dir($dir))
			unlink($dir);

		// très important car les chemins et les urls sont aussi mis en cache
		Mage::app()->getCacheInstance()->cleanType('block_html');
	}

	public function deleteFilesAction() {

		parent::deleteFilesAction();

		if (!empty($files = $this->getRequest()->getParam('files'))) {

			$files = Mage::helper('core')->jsonDecode($files);
			$path  = 'wysiwyg/'.str_replace([Mage::helper('apijs')->getWysiwygImageDir(), Mage::getBaseDir('media').'/', '//'], ['', '', '/'], $this->getStorage()->getSession()->getCurrentPath());

			foreach ($files as $file) {
				$oldfile = trim($path, '/').'/'.Mage::helper('cms/wysiwyg_images')->idDecode($file);
				Mage::helper('apijs')->removeFiles(Mage::helper('apijs')->getWysiwygImageDir(true), $oldfile);
			}
		}

		// très important car les chemins et les urls sont aussi mis en cache
		Mage::app()->getCacheInstance()->cleanType('block_html');
	}

	public function renameFileAction() {

		try {
			if (!empty($file = $this->getRequest()->getParam('file')) && !empty($name = $this->getRequest()->getParam('name'))) {

				$help    = Mage::helper('apijs');
				$path    = $this->getStorage()->getSession()->getCurrentPath();
				$oldfile = '/'.trim($path, '/').'/'.Mage::helper('cms/wysiwyg_images')->idDecode($file);
				$newfile = '/'.trim($path, '/').'/'.trim($name);
				$newfile = str_replace(['\\', '/./', '//', '//', '\\'], ['', '/', '/', '/', ''], $newfile);

				// vérifie que le nouveau fichier n'existe pas
				if (is_file($newfile)) {
					$html = str_replace('/'.basename($newfile), '/[b]'.basename($newfile).'[/b]', '[br][em]'.$newfile.'[/em]');
					Mage::throwException($help->__('The new file already exists.').$html);
				}

				// vérifie que l'extension du fichier ne change pas trop
				if (mb_strtolower(pathinfo($newfile, PATHINFO_EXTENSION)) != mb_strtolower(pathinfo($oldfile, PATHINFO_EXTENSION))) {
					$html = preg_replace('#\.([0-9a-zA-Z.]+)#', '.[b]$1[/b]', basename($oldfile).' ~ '.basename($newfile));
					Mage::throwException($help->__('The file extension can not be changed.').'[br][em]'.$html.'[/em]');
				}

				if (mb_strpos($name, '/') !== false) {

					// supprime le dossier parent lorsque l'enfant est .. tant qu'il y en a
					while (mb_stripos($newfile, '/../') !== false)
						$newfile = preg_replace('#/[^/]*/\.\./#', '/', $newfile, 1);

					// vérifie qu'on est toujours dans le dossier .../media/wysiwyg/
					if (mb_stripos($newfile, $help->getWysiwygImageDir()) === false) {
						$html = '[br][em]'.$newfile.'[/em]';
						Mage::throwException($help->__('The new directory must be inside the [b]%s[/b] directory.', 'wysiwyg').$html);
					}

					// vérifie que les dossiers existent
					$dirs = array_filter(explode('/', dirname($newfile)));
					$test = '/';
					foreach ($dirs as $dir) {
						if (($dir == 'wysiwyg') || (mb_stripos($test, 'wysiwyg') === false)) {
							$test .= $dir.'/';
						}
						else {
							$test .= $dir.'/';
							if (!is_dir($test)) {
								$html = str_replace('/'.$dir.'/', '/[b]'.$dir.'[/b]/', '[br][em]'.$newfile.'[/em]');
								Mage::throwException($help->__('The new directory does not exist.').$html);
							}
						}
					}
				}

				// renomme
				rename($oldfile, $newfile);

				// images en cache
				$path = 'wysiwyg/'.str_replace([$help->getWysiwygImageDir(), Mage::getBaseDir('media').'/', '//'], ['', '', '/'], $this->getStorage()->getSession()->getCurrentPath());
				$oldfile = trim($path, '/').'/'.Mage::helper('cms/wysiwyg_images')->idDecode($file);
				$help->removeFiles($help->getWysiwygImageDir(true), $oldfile);

				// très important car les chemins et les urls sont aussi mis en cache
				Mage::app()->getCacheInstance()->cleanType('block_html');
			}
		}
		catch (Throwable $e) {
			$result = ['error' => true, 'message' => $e->getMessage()];
			$this->getResponse()->setBody(json_encode($result));
		}
	}
}