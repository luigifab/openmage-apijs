<?php
/**
 * Created S/13/06/2015
 * Updated S/01/02/2020
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

class Luigifab_Apijs_Model_Observer extends Luigifab_Apijs_Helper_Data {

	// EVENT catalog_product_delete_after_done (adminhtml)
	public function deleteProductFiles(Varien_Event_Observer $observer) {

		$product = $observer->getData('product');
		if (is_object($product) && !empty($product->getId()) && Mage::getStoreConfigFlag('apijs/general/delete_cache')) {

			foreach ($product->getMediaGallery('images') as $image) {
				$filename = basename($image['file']);
				$this->deletedFiles(
					Mage::helper('apijs')->getCatalogProductImageDir(), $filename, // pas uniquement dans le cache
					sprintf('Remove %s images with exec(find) for product %d', $filename, $product->getId()));
			}
		}
	}

	// EVENT controller_action_predispatch_adminhtml_apijs_media_save (adminhtml)
	// EVENT controller_action_predispatch_adminhtml_catalog_product_save (adminhtml)
	public function updatePostForGallery(Varien_Event_Observer $observer) {

		$post      = $observer->getData('controller_action')->getRequest()->getPost();
		$productId = $observer->getData('controller_action')->getRequest()->getParam('id', 0);
		$storeId   = $observer->getData('controller_action')->getRequest()->getParam('store', 0);
		$gallery   = [];

		if (!empty($post['apijs'])) {

			$product    = Mage::getModel('catalog/product');
			$attributes = $product->getMediaAttributes();
			$fields     = $product->getResource()->getAttribute('media_gallery')->getBackend()->getAllColumns();

			// simule ce que fait le js de Magento, quel bordel
			foreach ($post['apijs'] as $imageId => $image) {

				if (!is_array($image) || !array_key_exists('file', $image)) {
					// imageId = image, small_image, thumbnail, image = /a/b/c.xyz
					// fait en sorte de ne pas copier la valeur par défaut
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
									// si dans eav_attribute, attribute_model = xyz/source_xyz
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
							}
							else {
								$values[$field['Field']] = $value;
								$values[$field['Field'].'_default'] = $value;
							}
						}
					}

					ksort($values); // pour debug uniquement
					$gallery['images'][] = $values;
					// DELETE FROM catalog_product_entity_varchar WHERE value IS NULL
				}
			}

			if (!empty($gallery)) {

				$post['media_gallery_apijs'] = $gallery; // pour Luigifab_Apijs_Apijs_MediaController

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