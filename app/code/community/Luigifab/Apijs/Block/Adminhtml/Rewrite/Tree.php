<?php
/**
 * Created S/04/01/2020
 * Updated M/21/01/2020
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

class Luigifab_Apijs_Block_Adminhtml_Rewrite_Tree extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Tree {

	public function getTreeJson() {

		$helper = $this->helper('cms/wysiwyg_images');
		$helper->getStorageRoot(); // très important

		$collection = Mage::registry('storage')->getDirsCollection($helper->getCurrentPath());
		$items = [];

		foreach ($collection as $item) {
			if ($item->getBasename() != 'cache') {
				$items[] = [
					'text' => $helper->getShortFilename($item->getBasename(), 20),
					'id'   => $helper->convertPathToId($item->getFilename()),
					'cls'  => 'folder'
				];
			}
		}

		return Zend_Json::encode($items);
	}
}