<?php
/**
 * Created J/29/08/2019
 * Updated D/20/10/2019
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

class Luigifab_Apijs_Model_Rewrite_Media extends Mage_Catalog_Model_Product_Attribute_Backend_Media {

	public function getAllColumns() {
		return $this->_getResource()->getAllColumns();
	}

	public function afterLoad($object) {

		$attrCode = $this->getAttribute()->getAttributeCode();
		$value    = ['images' => [], 'values' => []];
		$localAttributes = ['label', 'position', 'disabled'];

		// traite toutes les colonnes
		$fields = $this->getAllColumns();
		foreach ($fields as $field) {
			if ((mb_stripos($field['Field'], '_id') === false) && !in_array($field['Field'], $localAttributes))
				$localAttributes[] = $field['Field'];
		}

		foreach ($this->_getResource()->loadGallery($object, $this) as $image) {
			foreach ($localAttributes as $localAttribute) {
				if ($image[$localAttribute] === null) {
					$image[$localAttribute] = $this->_getDefaultValue($localAttribute, $image);
					$image[$localAttribute.'_global'] = true;
				}
			}
			$value['images'][] = $image;
		}

		$object->setData($attrCode, $value);
	}

	public function afterSave($object) {

		if ($object->getIsDuplicate() == true) {
			$this->duplicate($object);
			return;
		}

		$attrCode = $this->getAttribute()->getAttributeCode();
		$value    = $object->getData($attrCode);
		if (!is_array($value) || !isset($value['images']) || $object->isLockedAttribute($attrCode))
			return;

		$storeId  = (int) $object->getStoreId();
		$storeIds = $object->getStoreIds();
		$storeIds[] = Mage_Core_Model_App::ADMIN_STORE_ID;

		// remove current storeId
		$storeIds = array_flip($storeIds);
		unset($storeIds[$storeId]);
		$storeIds = array_keys($storeIds);

		$images = Mage::getResourceModel('catalog/product')->getAssignedImages($object, $storeIds);

		$picturesInOtherStores = [];
		foreach ($images as $image)
			$picturesInOtherStores[$image['filepath']] = true;

		$toDelete = [];
		foreach ($value['images'] as $image) {

			if (!empty($image['removed'])) {
				if (isset($image['value_id']) && !isset($picturesInOtherStores[$image['file']]))
					$toDelete[] = $image['value_id'];
				continue;
			}

			// global
			if (!isset($image['value_id'])) {
				$image['value_id'] = $this->_getResource()->insertGallery([
					'entity_id'    => $object->getId(),
					'attribute_id' => $this->getAttribute()->getId(),
					'value'        => $image['file']
				]);
			}

			$this->_getResource()->deleteGalleryValueInStore($image['value_id'], $storeId);

			// par vue magasin
			// remet toutes les colonnes
			$data   = ['value_id' => $image['value_id'], 'store_id' => $storeId];
			$fields = $this->_getResource()->getAllColumns();
			foreach ($fields as $field) {
				if (!array_key_exists($field['Field'], $data) && array_key_exists($field['Field'], $image))
					$data[$field['Field']] = array_key_exists($field['Field'].'_global', $image) ? null : $image[$field['Field']];
			}

			$this->_getResource()->insertGalleryValueInStore($data);
		}

		$this->_getResource()->deleteGallery($toDelete);
	}
}