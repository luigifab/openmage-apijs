<?php
/**
 * Created S/09/10/2021
 * Updated S/09/10/2021
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
		return (empty($type) && Mage::getStoreConfigFlag('apijs/general/backend')) ? [] : parent::getAllowedExtensions($type);
	}
}