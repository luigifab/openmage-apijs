<?php
/**
 * Created J/29/08/2019
 * Updated J/12/12/2019
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

class Luigifab_Apijs_Model_Rewrite_Mediares extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media {

	public function getAllColumns() {
		return $this->_getReadAdapter()->fetchAll('SHOW COLUMNS FROM '.$this->getTable(self::GALLERY_VALUE_TABLE));
	}

	protected function _getLoadGallerySelect($productIds, $storeId, $attributeId) {

		$adapter = $this->_getReadAdapter();
		$positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');

		// récupère toutes les colonnes
		$values = [];
		$fields = $this->getAllColumns();
		foreach ($fields as $field) {
			if (mb_stripos($field['Field'], '_id') === false)
				$values[$field['Field'].'_default'] = $field['Field'];
		}

		// select gallery images for product
		return $adapter->select()
			->from(
				['main' => $this->getMainTable()],
				['value_id', 'value AS file', 'product_id' => 'entity_id']
			)
			->joinLeft(
				['value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
				$adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int) $storeId),
				array_values($values) // ['label', 'position', 'disabled']
			)
			->joinLeft( // Joining default values
				['default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
				'main.value_id = default_value.value_id AND default_value.store_id = 0',
				$values // ['label_default' => 'label', 'position_default' => 'position', 'disabled_default' => 'disabled']
			)
			->where('main.attribute_id = ?', $attributeId)
			->where('main.entity_id in (?)', $productIds)
			->order($positionCheckSql.' '.Varien_Db_Select::SQL_ASC);
	}
}