<?php
/**
 * Created J/27/05/2021
 * Updated J/21/09/2023
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

class Luigifab_Apijs_Model_Rewrite_Categoryimg extends Mage_Catalog_Model_Category_Attribute_Backend_Image {

	public function getAllowedExtensions():array {
		// PR 3301
		return Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions('image');
	}

	public function beforeSave($object) {

		$name  = $this->getAttribute()->getName();
		$value = $object->getData($name);

		if (is_array($value) && !empty($value['value'])) {

			$database = Mage::getSingleton('core/resource');
			$reader   = $database->getConnection('core_read');
			$table    = $database->getTableName('catalog_category_entity_varchar');

			$file  = $value['value'];
			$count = $reader->fetchOne('SELECT count(*) FROM '.$table.' WHERE value = ?', [$file]);
			if ($count == 1) {
				$help = Mage::helper('apijs');
				if (!empty($value['delete']))
					$help->removeFiles($help->getCatalogCategoryImageDir(), basename($file)); // everywhere (not only in cache dir)
				else if (!empty($_FILES[$name]['size']))
					$help->removeFiles($help->getCatalogCategoryImageDir(), basename($file), true); // everywhere (not only in cache dir)
			}
		}

		return parent::beforeSave($object);
	}
}