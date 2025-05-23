<?php
/**
 * Created M/10/09/2019
 * Updated D/12/11/2023
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

		$storage = $this->getStorage();

		try {
			$helper = Mage::helper('apijs');
			$base   = $helper->getWysiwygImageDir();
			$path   = $storage->getSession()->getCurrentPath();
			$files  = $storage->getFilesCollection($path);
			$cache  = trim('wysiwyg/'.str_replace([$base, Mage::getBaseDir('media').'/', '//'], ['', '', '/'], $path), '/');

			// s'assure que le dossier à supprimer est bien dans le dossier media/wysiwyg
			if (!empty($path) && (mb_stripos($path, $base) === 0) && (trim($path, '/') != trim($base, '/'))) {

				// supprime les images en cache
				foreach ($files as $file) {
					if ($storage->isImage($file->getName()))
						$helper->removeFiles($helper->getWysiwygImageDir(true), $cache.'/'.$file->getName()); // only in cache dir
				}

				// supprime le dossier
				$storage->deleteDirectory($path);
			}
		}
		catch (Throwable $t) {

			if (Mage::getIsDeveloperMode())
				$result = ['error' => true, 'message' => $t->getMessage().'  in '.$t->getFile().' on line '.$t->getLine()];
			else
				$result = ['error' => true, 'message' => $t->getMessage()];

			$this->getResponse()->setBody(json_encode($result));
		}

		// très important car les chemins et les URLs sont en cache
		Mage::app()->getCacheInstance()->cleanType('block_html');
	}

	public function deleteFilesAction() {

		$storage = $this->getStorage();
		if (empty($files = $this->getRequest()->getPost('files')) && !empty($file = $this->getRequest()->getPost('file')))
			$this->getRequest()->setPost('files', json_encode([$file]));

		try {
			$helper = Mage::helper('apijs');
			$base   = $helper->getWysiwygImageDir();
			$path   = $storage->getSession()->getCurrentPath();
			$files  = empty($files) ? [$file] : Mage::helper('core')->jsonDecode($files);
			$cache  = trim('wysiwyg/'.str_replace([$base, Mage::getBaseDir('media').'/', '//'], ['', '', '/'], $path), '/');

			// s'assure que les fichiers à supprimer sont bien dans le dossier media/wysiwyg
			if (!empty($path) && (mb_stripos($path, $base) === 0)) {

				foreach ($files as $file) {

					$file = Mage::helper('cms/wysiwyg_images')->idDecode($file);

					// supprime les images en cache
					if ($storage->isImage($file))
						$helper->removeFiles($helper->getWysiwygImageDir(true), $cache.'/'.$file); // only in cache dir

					// supprime le fichier
					$storage->deleteFile($path.'/'.$file);
				}
			}
		}
		catch (Throwable $t) {

			if (Mage::getIsDeveloperMode())
				$result = ['error' => true, 'message' => $t->getMessage().'  in '.$t->getFile().' on line '.$t->getLine()];
			else
				$result = ['error' => true, 'message' => $t->getMessage()];

			$this->getResponse()->setBody(json_encode($result));
		}

		// très important car les chemins et les URLs sont en cache
		Mage::app()->getCacheInstance()->cleanType('block_html');
	}

	public function renameFileAction() {

		$storage = $this->getStorage();

		try {
			if (!empty($file = $this->getRequest()->getPost('file')) && !empty($name = $this->getRequest()->getPost('name'))) {

				$helper  = Mage::helper('apijs');
				$base    = $helper->getWysiwygImageDir();
				$path    = $storage->getSession()->getCurrentPath();
				$file    = Mage::helper('cms/wysiwyg_images')->idDecode($file);
				$oldfile = '/'.trim($path, '/').'/'.$file;
				$newfile = '/'.trim($path, '/').'/'.trim($name);
				$newfile = str_replace(['\\', '/./', '//', '//', '\\'], ['', '/', '/', '/', ''], $newfile);

				// vérifie que le nouveau fichier n'existe pas
				if (is_file($newfile)) {
					$html = str_replace('/'.basename($newfile), '/[b]'.basename($newfile).'[/b]', '[br][em]'.$newfile.'[/em]');
					Mage::throwException($helper->__('The new file already exists.').$html);
				}

				// vérifie que l'extension du fichier ne change pas trop
				if (mb_strtolower(pathinfo($newfile, PATHINFO_EXTENSION)) != mb_strtolower(pathinfo($oldfile, PATHINFO_EXTENSION))) {
					$html = preg_replace('#\.([a-zA-Z\d.]+)#', '.[b]$1[/b]', basename($oldfile).' ~ '.basename($newfile));
					Mage::throwException($helper->__('The file extension can not be changed.').'[br][em]'.$html.'[/em]');
				}

				// s'il faut déplacer le fichier dans un autre dossier
				if (str_contains($name, '/')) {

					// supprime le dossier parent lorsque l'enfant est .. tant qu'il y en a
					while (mb_stripos($newfile, '/../') !== false)
						$newfile = preg_replace('#/[^/]*/\.\./#', '/', $newfile, 1);

					// vérifie qu'on est toujours dans le dossier media/wysiwyg
					if (mb_stripos($newfile, $base) === false) {
						$html = '[br][em]'.$newfile.'[/em]';
						Mage::throwException($helper->__('The new directory must be inside the [b]%s[/b] directory.', 'wysiwyg').$html);
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
								Mage::throwException($helper->__('The new directory does not exist.').$html);
							}
						}
					}
				}

				// supprime les images en cache
				if ($storage->isImage($oldfile)) {
					$cache = trim('wysiwyg/'.str_replace([$base, Mage::getBaseDir('media').'/', '//'], ['', '', '/'], $path), '/');
					$helper->removeFiles($helper->getWysiwygImageDir(true), $cache.'/'.$file); // only in cache dir
				}

				// renomme
				rename($oldfile, $newfile);
			}
		}
		catch (Throwable $t) {

			if (Mage::getIsDeveloperMode())
				$result = ['error' => true, 'message' => $t->getMessage().'  in '.$t->getFile().' on line '.$t->getLine()];
			else
				$result = ['error' => true, 'message' => $t->getMessage()];

			$this->getResponse()->setBody(json_encode($result));
		}

		// très important car les chemins et les URLs sont en cache
		Mage::app()->getCacheInstance()->cleanType('block_html');
	}
}