<?php
/**
 * Created S/09/10/2021
 * Updated V/28/04/2023
 *
 * Copyright 2008-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Model_Rewrite_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage {

	public function getDirsCollection($path) {

		$dirs  = parent::getDirsCollection($path);
		$cache = trim(Mage::helper('apijs')->getWysiwygImageDir(true), '/');

		foreach ($dirs as $key => $dir) {
			if ($cache == trim($dir->getFilename(), '/'))
				$dirs->removeItemByKey($key);
		}

		return $dirs;
	}

	public function getAllowedExtensions($type = null) {

		$exts = parent::getAllowedExtensions($type);

		if (!Mage::getStoreConfigFlag('apijs/general/python')) {
			$exts = array_combine($exts, $exts);
			unset($exts['webp'], $exts['svg']);
			$exts = array_values($exts);
		}

		return $exts;
	}
}