<?php
/**
 * Created J/27/05/2021
 * Updated M/26/10/2021
 *
 * Copyright 2008-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2019-2022 | Fabrice Creuzot <fabrice~cellublue~com>
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

class Luigifab_Apijs_Model_Rewrite_Categoryimg extends Mage_Catalog_Model_Category_Attribute_Backend_Image {

	public function beforeSave($object) {

		$name  = $this->getAttribute()->getName();
		$value = $object->getData($name);

		if (is_array($value)) {

			$database = Mage::getSingleton('core/resource');
			$reader   = $database->getConnection('core_read');
			$table    = $database->getTableName('catalog_category_entity_varchar');

			if (!empty($value['value'])) {
				$file  = $value['value'];
				$count = $reader->fetchOne('SELECT count(*) FROM '.$table.' WHERE value = ?', [$file]);
				if ($count == 1) {
					$help = Mage::helper('apijs');
					if (!empty($value['delete']))
						$help->removeFiles($help->getCatalogCategoryImageDir(), basename($file)); // pas uniquement dans le cache
					else if (!empty($_FILES[$name]['size']))
						$help->removeFiles($help->getCatalogCategoryImageDir(), basename($file), true); // pas uniquement dans le cache
				}
			}
		}

		return parent::beforeSave($object);
	}
}