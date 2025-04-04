<?php
/**
 * Created J/27/05/2021
 * Updated S/30/12/2023
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

class Luigifab_Apijs_Model_Rewrite_Productimg extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Image {

	public function getAllowedExtensions():array {
		// PR 3301
		return Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions('image');
	}

	// attribute backend model: catalog_resource/product_attribute_backend_image
	//  frontend_input: image, backend_type: varchar
	public function beforeSave($object) {

		$name  = $this->getAttribute()->getName();
		$value = $object->getData($name);

		if (is_array($value) && !empty($value['value'])) {

			$database = Mage::getSingleton('core/resource');
			$reader   = $database->getConnection('core_read');
			$table    = $database->getTableName('catalog_product_entity_varchar');

			$file  = $value['value'];
			$count = $reader->fetchOne('SELECT count(*) FROM '.$table.' WHERE value = ?', [$file]);

			if ($count == 1) {
				$helper = Mage::helper('apijs');
				if (!empty($_FILES[$name]['tmp_name'])) {
					$helper->removeFiles($helper->getCatalogProductImageDir(), basename($file), true); // everywhere (not only in cache dir)
					// allow to delete and upload
					unset($value['delete']);
					$object->setData($name, $value);
				}
				else if (!empty($value['delete'])) {
					$helper->removeFiles($helper->getCatalogProductImageDir(), basename($file)); // everywhere (not only in cache dir)
				}
			}
		}

		return parent::beforeSave($object);
	}

	public function afterSave($object) {

		$name   = $this->getAttribute()->getName();
		$result = $this;

		try {
			$before = $object->getData($name);
			$result = parent::afterSave($object);
			$after  = $object->getData($name);

			// @deprecated (exception is hidden with 20.3.0)
			if (!empty($_FILES[$name]['tmp_name']) && ($before == $after)) {
				$object->setData($name, null);
                    $this->getAttribute()->getEntity()->saveAttribute($object, $name);
				if (Mage::app()->getStore()->isAdmin()) {
					Mage::getSingleton('adminhtml/session')->addError(sprintf(
						'Warning: image (<em>%s</em>) for attribute <em>%s</em> was not saved!',
						$_FILES[$name]['name'],
						$name
					));
				}
			}
		}
		catch (Throwable $t) {
			if (!empty($_FILES[$name]['tmp_name'])) {
				$object->setData($name, null);
                    $this->getAttribute()->getEntity()->saveAttribute($object, $name);
				if (Mage::app()->getStore()->isAdmin()) {
					Mage::getSingleton('adminhtml/session')->addError(sprintf(
						'Warning: image (<em>%s</em>) for attribute <em>%s</em> was not saved: %s',
						$_FILES[$name]['name'],
						$name,
						$t->getMessage()
					));
				}
			}
		}

		return $result;
	}
}