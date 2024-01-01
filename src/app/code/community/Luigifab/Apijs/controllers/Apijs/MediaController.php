<?php
/**
 * Created S/04/10/2014
 * Updated D/03/12/2023
 *
 * Copyright 2008-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml').'/Catalog/ProductController.php');

class Luigifab_Apijs_Apijs_MediaController extends Mage_Adminhtml_Catalog_ProductController {

	protected function disableAllBuffer() {

		// désactivation des tampons (sauf si zlib.output_compression)
		// cela permet d'afficher 100% dans la barre de progression
		// @see https://stackoverflow.com/a/25835968
		header('Content-Encoding: chunked');
		header('Connection: Keep-Alive');
		header('Content-Type: text/plain; charset=utf-8');
		header('Cache-Control: no-cache, must-revalidate');
		ini_set('output_buffering', (PHP_VERSION_ID < 80100) ? '0' : 0);
		ini_set('implicit_flush', (PHP_VERSION_ID < 80100) ? '1' : 1);
		ob_implicit_flush();
		ignore_user_abort(true);

		try {
			while (ob_get_level() > 0)
				ob_end_clean();
		}
		catch (Throwable $t) { }

		echo ' ';
		return $this;
	}

	protected function formatResult($success, $errors, $data) {

		$result = ['html' => $data];

		if (!empty($errors) && empty($success)) {
			$result['bbcode'] = sprintf('[p]%s[/p][ul][li]%s[/li][/ul]',
				$this->__('[strong]Warning[/strong], no files were saved:'),
				implode('[/li][li]', $errors));
			$result['bbcode'] = str_replace('Disalollowed file format.', 'Exceeded maximum width/height.', $result['bbcode']);
		}
		else if (!empty($errors)) {
			$result['bbcode'] = sprintf('[p]%s[/p][ul][li]%s[/li][/ul][p]%s[/p][ul][li]%s[/li][/ul]',
				(count($errors) > 1) ? $this->__('[strong]Warning[/strong], the following files were not saved:') :
					$this->__('[strong]Warning[/strong], the following file was not saved:'),
				implode('[/li][li]', $errors),
				(count($success) > 1) ? $this->__('[strong]However[/strong], the following files were successfully saved:') :
					$this->__('[strong]However[/strong], the following file was successfully saved:'),
				implode('[/li][li]', $success));
			$result['bbcode'] = str_replace('Disalollowed file format.', 'Exceeded maximum width/height.', $result['bbcode']);
		}

		return 'success-'.json_encode($result);
	}

	public function getUsedModuleName() {
		return 'Luigifab_Apijs';
	}

	public function uploadWidgetAction() {

		$this->disableAllBuffer();

		$storage = Mage::getSingleton('cms/wysiwyg_images_storage');
		$success = [];
		$errors  = [];

		try {
			if (empty($_FILES))
				Mage::throwException('No files uploaded.');

			$exts = $storage->getAllowedExtensions('image');
			$exts[] = 'pdf';

			// sauvegarde du ou des fichiers
			$keys = array_keys($_FILES['myimage']['name']);
			foreach ($keys as $key) {

				try {
					$uploader = new Varien_File_Uploader([
						'name'     => $_FILES['myimage']['name'][$key],
						'type'     => $_FILES['myimage']['type'][$key],
						'tmp_name' => $_FILES['myimage']['tmp_name'][$key],
						'error'    => $_FILES['myimage']['error'][$key],
						'size'     => $_FILES['myimage']['size'][$key],
					]);

					$uploader->setAllowedExtensions($exts);
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(false);
					$uploader->addValidateCallback(Mage_Core_Model_File_Validator_Image::NAME,
						Mage::getModel('core/file_validator_image'), 'validate');

					$filepath = $uploader->save($storage->getSession()->getCurrentPath());
					$filepath = array_pop($filepath);

					$success[] = $filepath;
				}
				catch (Throwable $t) {
					$errors[] = $t->getMessage();
				}
			}

			// retour
			$result = $this->formatResult($success, $errors, 'ok');
		}
		catch (Throwable $t) {
			$result = $t->getMessage();
		}

		sleep(1);
		echo $result;
		exit(0);
	}

	public function uploadProductAction() {

		$this->disableAllBuffer();

		$attribute = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'media_gallery');
		$productId = (int) $this->getRequest()->getParam('product', 0);
		$storeId   = (int) $this->getRequest()->getParam('store', 0);

		$database  = Mage::getSingleton('core/resource');
		$reader    = $database->getConnection('core_read');
		$writer    = $database->getConnection('core_write');
		$cpemg     = $database->getTableName('catalog_product_entity_media_gallery');
		$cpemv     = $database->getTableName('catalog_product_entity_media_gallery_value');

		$success = [];
		$errors  = [];

		try {
			$helper = Mage::helper('apijs');
			if (empty($productId))
				Mage::throwException('Invalid product id.');
			if (empty($_FILES))
				Mage::throwException('No files uploaded.');

			// sauvegarde du ou des fichiers
			$exts = Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions('image');
			$keys = array_keys($_FILES['myimage']['name']);
			foreach ($keys as $key) {

				try {
					$uploader = new Varien_File_Uploader([
						'name'     => $_FILES['myimage']['name'][$key],
						'type'     => $_FILES['myimage']['type'][$key],
						'tmp_name' => $_FILES['myimage']['tmp_name'][$key],
						'error'    => $_FILES['myimage']['error'][$key],
						'size'     => $_FILES['myimage']['size'][$key],
					]);

					$uploader->setAllowedExtensions($exts);
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(true);
					$uploader->addValidateCallback(Mage_Core_Model_File_Validator_Image::NAME,
						Mage::getModel('core/file_validator_image'), 'validate');
					$uploader->addValidateCallback('catalog_product_image',
						Mage::helper('catalog/image'), 'validateUploadFile');

					$filepath = $uploader->save($helper->getCatalogProductImageDir());
					Mage::dispatchEvent('catalog_product_gallery_upload_image_after', ['result' => $filepath, 'action' => $this]);
					$filepath = array_pop($filepath);

					$writer->query(
						'INSERT INTO '.$cpemg.' (attribute_id, entity_id, value) VALUES (?, ?, ?)',
						[$attribute, $productId, $filepath]
					);

					$valueId = $writer->lastInsertId();
					if (($storeId > 0) && Mage::getStoreConfigFlag('apijs/general/sort_by_store')) {
						$nb = max($storeId * 100 + 1, (int) $reader->fetchOne(
							'SELECT MAX(position) FROM '.$cpemg.' cpemg
							 LEFT JOIN '.$cpemv.' cpemv
								ON cpemg.value_id = cpemv.value_id
							 WHERE entity_id = ? AND CAST(position / 100 AS UNSIGNED) = ?',
							[$productId, $storeId]
						) + 1);
						$writer->query(
							'INSERT INTO '.$cpemv.'
								(value_id, store_id, position, disabled) VALUES (?, 0, ?, ?)',
							[$valueId, $nb, empty($this->getRequest()->getParam('exclude')) ? 0 : 1]
						);
					}
					else {
						$nb = max(1, (int) $reader->fetchOne(
							'SELECT MAX(position) FROM '.$cpemg.' cpemg
							 LEFT JOIN '.$cpemv.' cpemv
								ON cpemg.value_id = cpemv.value_id
							 WHERE entity_id = ? AND store_id = 0 AND LENGTH(position) < 3',
							[$productId]
						) + 1, $reader->fetchOne('SELECT COUNT(entity_id) AS nb FROM '.$cpemg.' WHERE entity_id = ?', [$productId]));
						$writer->query(
							'INSERT INTO '.$cpemv.'
								(value_id, store_id, position, disabled) VALUES (?, 0, ?, ?)',
							[$valueId, $nb, empty($this->getRequest()->getParam('exclude')) ? 0 : 1]
						);
					}

					if (($storeId > 0) && !empty($this->getRequest()->getParam('exclude'))) {
						$writer->query(
							'INSERT INTO '.$cpemv.'
								(value_id, store_id, position, disabled) VALUES (?, ?, NULL, 0)',
							[$valueId, $storeId]
						);
					}

					$success[] = $filepath;
				}
				catch (Throwable $t) {
					$errors[] = $t->getMessage();
				}
			}

			// OM 21.1.0-rc3+
			$_FILES = [];

			// image par défaut (global uniquement)
			$product = Mage::getModel('catalog/product')->load($productId);

			if (!empty($success) && !empty($product->getMediaGallery('images'))) {

				$attributes = $product->getMediaAttributes();
				foreach ($attributes as $code => $attribute) {
					$value = $product->getData($code);
					// si dans eav_attribute, attribute_model = xyz/source_xyz
					// $attribute = Xyz_Xyz_Model_Source_Xyz extends Mage_Catalog_Model_Resource_Eav_Attribute
					if ((empty($value) || ($value == 'no_selection')) && ($attribute->getIsCheckbox() !== true))
						$product->setData($code, $success[0]);
				}

				if ($product->hasDataChanges())
					$product->save();

				// reload
				$product->setStoreId($storeId)->load($product->getId());
			}

			// très important car les chemins et les URLs sont aussi mis en cache
			Mage::app()->getCacheInstance()->cleanType('block_html');
			// html
			$result = $this->formatResult($success, $errors, $helper->renderGalleryBlock($product));
		}
		catch (Throwable $t) {
			$result = $t->getMessage();
		}

		sleep(1);
		echo $result;
		exit(0);
	}

	public function saveAction() {

		$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Content-Type', 'text/plain; charset=utf-8', true)
			->setHeader('Cache-Control', 'no-cache, must-revalidate', true);

		$productId = (int) $this->getRequest()->getParam('product', 0);
		$storeId   = (int) $this->getRequest()->getParam('store', 0);

		try {
			if (empty($productId))
				Mage::throwException('Invalid product id.');
			if (empty($gallery = $this->getRequest()->getPost('media_gallery_apijs')) || !is_array($gallery))
				Mage::throwException('No data sent.');

			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);

			if (!empty($gallery)) {

				$product->setData('media_gallery', $gallery);
				if (!empty($gallery['values']) && is_array($gallery['values'])) {
					foreach ($gallery['values'] as $code => $value)
						$product->setData($code, $value);
				}
				else {
					$gallery['values'] = [];
				}

				// s'assure que l'image sélectionnée existe bien
				// sinon resélectionne no_selection
				if (!empty($product->getMediaGallery('images'))) {
					$attributes = $product->getMediaAttributes();
					foreach ($attributes as $code => $attribute) {
						// si dans eav_attribute, attribute_model = xyz/source_xyz
						// $attribute = Xyz_Xyz_Model_Source_Xyz extends Mage_Catalog_Model_Resource_Eav_Attribute
						if ($attribute->getIsCheckbox() !== true) {
							$value = $product->getData($code);
							if (empty($value) || (($value != 'no_selection') && !in_array($value, $gallery['values'])))
								$product->setData($code, 'no_selection');
						}
					}
				}

				if ($product->hasDataChanges())
					$product->save();

				// reload
				$product->setStoreId($storeId)->load($product->getId());
			}

			// très important car les chemins et les URLs sont aussi mis en cache
			Mage::app()->getCacheInstance()->cleanType('block_html');
			// html
			$result = $this->formatResult(null, null, Mage::helper('apijs')->renderGalleryBlock($product));
		}
		catch (Throwable $t) {
			$result = $t->getMessage();
		}

		$this->getResponse()->setBody($result);
	}

	public function removeAction() {

		$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Content-Type', 'text/plain; charset=utf-8', true)
			->setHeader('Cache-Control', 'no-cache, must-revalidate', true);

		$productId = (int) $this->getRequest()->getParam('product', 0);
		$imageId   = (int) $this->getRequest()->getParam('image', 0);
		$storeId   = (int) $this->getRequest()->getParam('store', 0);

		$database = Mage::getSingleton('core/resource');
		$reader   = $database->getConnection('core_read');
		$writer   = $database->getConnection('core_write');
		$cpemg    = $database->getTableName('catalog_product_entity_media_gallery');
		$cpev     = $database->getTableName('catalog_product_entity_varchar');

		if (empty($imageId) && ($this->getRequest()->getParam('image') == 'all'))
			$imageId = true;

		try {
			$helper = Mage::helper('apijs');
			if (empty($productId) || empty($imageId))
				Mage::throwException('Invalid product/image ids ('.$productId.'/'.$imageId.')');

			// trouve les ids
			if ($imageId === true)
				$ids = array_filter(explode(',', $reader->fetchOne('SELECT GROUP_CONCAT(value_id) AS ids FROM '.$cpemg.' WHERE entity_id = ? GROUP BY entity_id', [$productId])));
			else
				$ids = [$imageId];

			// supprime les images
			$paths = [];
			foreach ($ids as $id) {

				// recherche et supprime le nom du fichier
				$filepath = $reader->fetchOne('SELECT value FROM '.$cpemg.' WHERE value_id = '.$id);
				$filename = basename($filepath);
				$paths[]  = $filepath;

				if (!empty($filepath) && !empty($filename)) {

					$writer->query('DELETE FROM '.$cpemg.' WHERE value_id = ?', $id);
					$writer->query('DELETE FROM '.$cpev.' WHERE entity_id = ? AND value = ?', [$productId, $filepath]); // si par défaut

					// supprime enfin les fichiers s'ils ne sont pas utilisés dans d'autres produits
					if ($reader->fetchOne('SELECT count(*) FROM '.$cpemg.' WHERE value = ?', [$filepath]) == 0)
						$helper->removeFiles($helper->getCatalogProductImageDir(), $filename); // everywhere (not only in cache dir)
				}
			}

			// image par défaut (global uniquement)
			// et s'il n'y a plus d'image supprime la config des images sur toutes les vues
			$product    = Mage::getModel('catalog/product')->load($productId);
			$attributes = $product->getMediaAttributes();

			if (empty($product->getMediaGallery('images'))) {
				foreach ($attributes as $attribute) {
					$writer->query('DELETE FROM '.str_replace('_varchar', '_'.$attribute->getData('backend_type'), $cpev).
						' WHERE entity_id = ? AND attribute_id = ?', [$productId, $attribute->getId()]);
				}
			}
			else {
				$newvalue = $product->getMediaGallery('images')[0]['file'];
				foreach ($attributes as $code => $attribute) {
					$value = $product->getData($code);
					// si dans eav_attribute, attribute_model = xyz/source_xyz
					// $attribute = Xyz_Xyz_Model_Source_Xyz extends Mage_Catalog_Model_Resource_Eav_Attribute
					if ((empty($value) || ($value == 'no_selection') || in_array($value, $paths)) && ($attribute->getIsCheckbox() !== true))
						$product->setData($code, $newvalue);
				}

				if ($product->hasDataChanges())
					$product->save();
			}

			// reload
			$product->setStoreId($storeId)->load($product->getId());
			// très important car les chemins et les URLs sont aussi mis en cache
			Mage::app()->getCacheInstance()->cleanType('block_html');
			// html
			$result = $this->formatResult(null, null, $helper->renderGalleryBlock($product));
		}
		catch (Throwable $t) {
			$result = $t->getMessage();
		}

		$this->getResponse()->setBody($result);
	}
}