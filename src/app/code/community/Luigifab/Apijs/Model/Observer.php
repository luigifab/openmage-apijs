<?php
/**
 * Created S/13/06/2015
 * Updated D/27/07/2025
 *
 * Copyright 2008-2025 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Model_Observer extends Luigifab_Apijs_Helper_Data {

	// EVENT admin_system_config_changed_section_apijs (adminhtml)
	public function clearCache(Varien_Event_Observer $observer) {

		Mage::app()->cleanCache();
		Mage::dispatchEvent('adminhtml_cache_flush_system');

		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The OpenMage cache has been flushed and updates applied.'));
	}

	// EVENT catalog_category_delete_commit_after (global)
	public function removeCategoryImages(Varien_Event_Observer $observer) {

		$category = $observer->getData('category');
		if (is_object($category) && !empty($category->getId())) {

			$database = Mage::getSingleton('core/resource');
			$reader   = $database->getConnection('core_read');
			$table    = $database->getTableName('catalog_category_entity_varchar');

			$entityType = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Category::ENTITY)->getId();
			$attributes = Mage::getResourceModel('eav/entity_attribute_collection')
				->addFieldToFilter('frontend_input', 'image')
				->addFieldToFilter('entity_type_id', $entityType)
				->getItems();

			foreach ($attributes as $attribute) {
				$file = $category->getData($attribute->getData('attribute_code'));
				if (!empty($file) && ($reader->fetchOne('SELECT count(*) FROM '.$table.' WHERE value = ?', [$file]) == 0))
					$this->removeFiles($this->getCatalogCategoryImageDir(), $file); // everywhere (not only in cache dir)
			}
		}
	}

	// EVENT catalog_product_delete_commit_after (global)
	public function removeProductImages(Varien_Event_Observer $observer) {

		$product = $observer->getData('product');
		if (is_object($product) && !empty($product->getId())) {

			$database = Mage::getSingleton('core/resource');
			$reader   = $database->getConnection('core_read');
			$table    = $database->getTableName('catalog_product_entity_media_gallery');

			$entityType = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId();
			$attributes = Mage::getResourceModel('eav/entity_attribute_collection')
				->addFieldToFilter('frontend_input', 'image')
				->addFieldToFilter('entity_type_id', $entityType)
				->getItems();

			foreach ($attributes as $attribute) {
				$file = $product->getData($attribute->getData('attribute_code'));
				if (!empty($file) && ($reader->fetchOne('SELECT count(*) FROM '.$table.' WHERE value = ?', [$file]) == 0))
					$this->removeFiles($this->getCatalogProductImageDir(), basename($file)); // everywhere (not only in cache dir)
			}

			foreach ($product->getMediaGallery('images') as $image) {
				$file = $image['file'];
				if (!empty($file) && ($reader->fetchOne('SELECT count(*) FROM '.$table.' WHERE value = ?', [$file]) == 0))
					$this->removeFiles($this->getCatalogProductImageDir(), basename($file)); // everywhere (not only in cache dir)
			}
		}
	}

	// EVENT controller_action_predispatch_adminhtml_apijs_media_save (adminhtml)
	// EVENT controller_action_predispatch_adminhtml_catalog_product_save (adminhtml)
	public function updatePostForGallery(Varien_Event_Observer $observer) {

		$request   = $observer->getData('controller_action')->getRequest();
		$post      = $request->getPost();
		$productId = $request->getParam('id', 0);
		$storeId   = $request->getParam('store', 0);

		if (!empty($post['apijs'])) {

			$product    = Mage::getModel('catalog/product');
			$attributes = $product->getMediaAttributes();
			$fields     = $product->getResource()->getAttribute('media_gallery')->getBackend()->getAllColumns();
			$gallery    = [];

			// does what core js does, what a mess
			foreach ($post['apijs'] as $imageId => $image) {

				if (!is_array($image) || !array_key_exists('file', $image)) {
					// imageId = image, small_image, thumbnail, image = /a/b/c.xyz
					// ensures that the default value is not copied
					$default = $product->getResource()->getAttributeRawValue($product->getId(), $imageId, 0);
					$gallery['values'][$imageId] = (!empty($storeId) && ($default == $image)) ? false : $image;
				}
				else {
					$values = ['product_id' => $productId, 'value_id' => $imageId, 'file' => $image['file'], 'removed' => 0];
					foreach ($fields as $field) {

						if (mb_stripos($field['Field'], '_id') === false) {

							$value = $image[$field['Field']] ?? null;
							$value = (empty($value) && (mb_stripos($field['Type'], 'varchar') !== false)) ? null : $value;
							if (mb_stripos($field['Type'], 'int(') !== false) {
								$value = ($value == 'on') ? 1 : $value;
								if (empty($value)) {
									// when in eav_attribute, attribute_model = xyz/source_xyz
									// $attribute = Xyz_Xyz_Model_Source_Xyz extends Mage_Catalog_Model_Resource_Eav_Attribute
									$attribute = array_key_exists($field['Field'], $attributes) ? $attributes[$field['Field']] : false;
									if (is_object($attribute) && ($attribute->getIsCheckbox() === true))
										$value = 0;
									else if ($field['Field'] == 'disabled')
										$value = 0;
								}
								else {
									$value = ($value == '$$') ? null : $value;
								}
							}
							else {
								$value = ($value == '$$') ? null : $value;
							}

							if (!empty($value) && ($field['Field'] == 'label')) {
								$values[$field['Field']] = stripslashes($value);
								$values[$field['Field'].'_default'] = stripslashes($value);
								$values[$field['Field'].'_use_default'] = ($value == null); // PR 2481 // null when $$ when checked
							}
							else {
								$values[$field['Field']] = $value;
								$values[$field['Field'].'_default'] = $value;
								if ($field['Field'] == 'position')
									$values[$field['Field'].'_use_default'] = ($value == null); // null when $$ when checked
							}
						}
					}

					ksort($values); // for debug only
					$gallery['images'][] = $values;
					// DELETE FROM catalog_product_entity_varchar WHERE value IS NULL
				}
			}

			if (!empty($gallery)) {

				$post['media_gallery_apijs'] = $gallery; // for Luigifab_Apijs_Apijs_MediaController

				if (array_key_exists('images', $gallery))
					$gallery['images'] = json_encode($gallery['images']);

				if (array_key_exists('values', $gallery)) {
					foreach ($gallery['values'] as $key => $value)
						$post['product'][$key] = $value;
					$gallery['values'] = json_encode($gallery['values']);
				}

				$post['product']['media_gallery'] = $gallery;
				$observer->getData('controller_action')->getRequest()->setPost($post);
			}
		}
	}
}