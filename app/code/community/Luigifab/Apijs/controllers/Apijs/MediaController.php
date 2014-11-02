<?php
/**
 * Created S/04/10/2014
 * Updated D/02/11/2014
 * Version 6
 *
 * Copyright 2008-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/apijs
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

class Luigifab_Apijs_Apijs_MediaController extends Mage_Adminhtml_Controller_Action {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
	}

	public function uploadAction() {

		$productId = intval($this->getRequest()->getParam('product', 0));

		// désactivation des tampons
		// en Ajax uniquement cat cela permet d'afficher 100% dans la barre de prgression, voir http://stackoverflow.com/a/25835968
		if ($this->getRequest()->getParam('isAjax', false) && !$this->getRequest()->getParam('noAjax', false)) {

			header('Content-Encoding: chunked', true);
			header('Content-Type: text/plain; charset=utf-8', true);
			header('Cache-Control: no-cache, must-revalidate', true);
			header('Pragma: no-cache', true);

			ini_set('output_buffering', false);
			ini_set('implicit_flush', true);
			ob_implicit_flush(true);
			sleep(2);

			try {
				for ($i = 0; $i < ob_get_level(); $i++)
					ob_end_clean();
			}
			catch (Exception $e) { }
			echo ' ';
		}

		// traitement du fichier
		// la classe à l'international
		try {
			if ($productId < 1)
				Mage::throwException('Invalid product or attachment id!');

			// sauvegarde du fichier
			$uploader = new Varien_File_Uploader('myimage');
			$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
			$uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateaction');
			$uploader->setAllowRenameFiles(true);
			$uploader->setFilesDispersion(true);

			$file = $uploader->save(Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath());
			$file = array_pop($file); // Array ( => 20100724-152008.jpg => image/jpeg => /tmp/php1EUOZr => 0 => 1141633 => /media/documents/internet/www/sites/14/web/media/catalog/product => /2/0/20100724-152008.jpg )

			// FUCK OFF! no model = direct sql
			// enregistre le fichier dans la base de données
			$resource = Mage::getSingleton('core/resource');
			$write = $resource->getConnection('core_write');

			$write->query('
				INSERT INTO '.$resource->getTableName('catalog_product_entity_media_gallery').' (attribute_id, entity_id, value)
				VALUES ('.intval(Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'media_gallery')).',
				        '.$productId.', "'.$file.'")
			');

			$write->query('
				INSERT INTO '.$resource->getTableName('catalog_product_entity_media_gallery_value').' (value_id, store_id, position, disabled)
				VALUES ('.$write->lastInsertId().', 0, (
					SELECT COUNT(*) AS nb FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' WHERE entity_id = '.$productId.'
				), 0)
			');

			// attribution de l'image par défaut
			// utilise le nouveau fichier si aucune image n'est sélectionnée
			$product = Mage::getModel('catalog/product')->load($productId);
			foreach ($product->getMediaAttributes() as $attribute) {
				if (in_array($product->getData($attribute->getAttributeCode()), array('no_selection', '')))
					$product->setData($attribute->getAttributeCode(), $file);
			}
			$product->save();

			// à partir de Magento 1.8
			// rafraichi le cache des blocs HTML
			if (version_compare(Mage::getVersion(), '1.8.0', '>='))
				Mage::app()->getCacheInstance()->cleanType('block_html');

			$result = trim(Mage::helper('apijs')->renderBlock($product));
		}
		catch (Exception $e) {
			$result = $e->getMessage();
		}

		// texte en Ajax (avec exit(0) sinon HEADERS ALREADY SENT)
		// ou redirection vers le bon onglet
		if ($this->getRequest()->getParam('isAjax', false) && !$this->getRequest()->getParam('noAjax', false)) {
			echo $result;
			exit(0);
		}
		else {
			if (strpos($result, 'success-') !== 0)
				Mage::getSingleton('adminhtml/session')->addError($result);
			$this->_redirectUrl(Mage::helper('apijs')->createDirectTabLink($productId));
		}
	}

	public function saveAction() {

		$productId = intval($this->getRequest()->getParam('product', 0));
		$storeId = intval($this->getRequest()->getParam('store', 0));
		$imageId = intval($this->getRequest()->getParam('image', 0));

		$this->getResponse()->setHeader('Content-Type', 'text/plain; charset=utf-8', true);
		$this->getResponse()->setHeader('Cache-Control', 'no-cache, must-revalidate', true);
		$this->getResponse()->setHeader('Pragma', 'no-cache', true);

		try {
			// FUCK OFF! no model = direct sql
			// met à jour la description, la position et l'état du fichier
			$resource = Mage::getSingleton('core/resource');
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');

			if ($storeId > 0) {
				$write->query('INSERT IGNORE INTO '.$resource->getTableName('catalog_product_entity_media_gallery_value').'
					VALUES ('.$imageId.', '.$storeId.', "", 0, 0)');

				$write->query('UPDATE '.$resource->getTableName('catalog_product_entity_media_gallery_value').'
					SET label = "'.$this->getRequest()->getPost('label', '').'",
					    position = '.intval($this->getRequest()->getPost('position', 0)).',
					    disabled = '.intval($this->getRequest()->getPost('disabled', 0)).'
					WHERE value_id = '.$imageId.' AND store_id = '.$storeId);

				// CECI NE FONCTIONNE PAS (Magento 1.4 et 1.9)
				// en effet, le product->save un peu plus loin réajoute les valeurs, même pour les autres images du produit
				// suppression des valeurs en double (lorsque global = store courant)
				//$default = $read->fetchRow('SELECT label, position, disabled
				//	FROM '.$resource->getTableName('catalog_product_entity_media_gallery_value').'
				//	WHERE value_id = '.$imageId.' AND store_id = 0');
				//$current = $read->fetchRow('SELECT label, position, disabled
				//	FROM '.$resource->getTableName('catalog_product_entity_media_gallery_value').'
				//	WHERE value_id = '.$imageId.' AND store_id = '.$storeId);
				//
				//if (implode($default) == implode($current))
				//	$write->query('DELETE FROM '.$resource->getTableName('catalog_product_entity_media_gallery_value').'
				//		WHERE value_id = '.$imageId.' AND store_id = '.$storeId);
			}
			else {
				$write->query('UPDATE '.$resource->getTableName('catalog_product_entity_media_gallery_value').'
					SET label = "'.$this->getRequest()->getPost('label', '').'",
					    position = '.intval($this->getRequest()->getPost('position', 0)).',
					    disabled = '.intval($this->getRequest()->getPost('disabled', 0)).'
					WHERE value_id = '.$imageId.' AND store_id = 0');
			}

			// attribution de l'image par défaut
			// commence par rechercher le nom du fichier
			// input type radio coché = true via encodeURIComponent(elems[elem].checked)=true/false
			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
			$new = $read->fetchOne('SELECT value FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' WHERE value_id = '.$imageId);
			foreach ($product->getMediaAttributes() as $attribute) {
				if ($this->getRequest()->getPost($attribute->getAttributeCode(), 'false') == 'true')
					$product->setData($attribute->getAttributeCode(), $new);
			}
			$product->save();

			// à partir de Magento 1.8
			// rafraichi le cache des blocs HTML
			if (version_compare(Mage::getVersion(), '1.8.0', '>='))
				Mage::app()->getCacheInstance()->cleanType('block_html');

			$this->getResponse()->setBody('success-'.$imageId);
		}
		catch (Exception $e) {
			$this->getResponse()->setBody($e->getMessage());
		}
	}

	public function downloadAction() {

		$productId = intval($this->getRequest()->getParam('product', 0));
		$imageId = intval($this->getRequest()->getParam('image', 0));

		try {
			if (($productId < 1) || ($imageId < 1))
				Mage::throwException('Invalid product or attachment id!');

			// FUCK OFF! no model = direct sql
			// recherche le nom du fichier avant de le supprimer dans la base de données
			$resource = Mage::getSingleton('core/resource');
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');

			$file = $read->fetchOne('SELECT value FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' WHERE value_id = '.$imageId);
			$file = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath().$file;

			if (!is_file($file))
				Mage::throwException('File does not exist!');

			$this->_prepareDownloadResponse(basename($file), file_get_contents($file), mime_content_type($file));
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirectUrl(Mage::helper('apijs')->createDirectTabLink($productId));
		}
	}

	public function deleteAction() {

		$productId = intval($this->getRequest()->getParam('product', 0));
		$imageId = intval($this->getRequest()->getParam('image', 0));

		$this->getResponse()->setHeader('Content-Type', 'text/plain; charset=utf-8', true);
		$this->getResponse()->setHeader('Cache-Control', 'no-cache, must-revalidate', true);
		$this->getResponse()->setHeader('Pragma', 'no-cache', true);

		try {
			if (($productId < 1) || ($imageId < 1))
				Mage::throwException('Invalid product or attachment id!');

			// FUCK OFF! no model = direct sql
			// recherche le nom du fichier avant de le supprimer dans la base de données
			$resource = Mage::getSingleton('core/resource');
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');

			$file = $read->fetchOne('SELECT value FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' WHERE value_id = '.$imageId);
			$filename = basename($file);

			if ((strlen($file) < 2) || (strlen($filename) < 2))
				Mage::throwException('File does not exist!');

			$write->query('DELETE FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' WHERE value_id = '.$imageId);

			// vérification de l'image par défaut
			// utilise la première image du produit si le fichier supprimé est l'image par défaut
			// s'il n'y a plus d'image utilise no_selection
			$product = Mage::getModel('catalog/product')->load($productId);
			$new = (count($product->getMediaGalleryImages()) > 0) ? $product->getMediaGalleryImages()->getFirstItem()->getFile() : 'no_selection';
			foreach ($product->getMediaAttributes() as $attribute) {
				if ($product->getData($attribute->getAttributeCode()) == $file)
					$product->setData($attribute->getAttributeCode(), $new);
			}
			$product->save();

			// suppression des fichiers
			// recherche tous les fichiers avec la commande find
			// uniquement si le nom du fichier contient des caractères simples
			// preg_match() retourne 1 si le pattern fourni correspond, 0 s'il ne correspond pas, ou FALSE si une erreur survient
			if (preg_match('#^[a-z0-9_\-]+\.[a-z0-9]{3,5}$#i', $filename) === 1) {
				Mage::log('Removing all '.$filename.' images with system(find...) for product '.$productId, Zend_Log::INFO);
				system('find '.Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath().' -name '.$filename.' | xargs rm');
			}

			// à partir de Magento 1.8
			// rafraichi le cache des blocs HTML
			if (version_compare(Mage::getVersion(), '1.8.0', '>='))
				Mage::app()->getCacheInstance()->cleanType('block_html');

			$this->getResponse()->setBody(trim(Mage::helper('apijs')->renderBlock($product)));
		}
		catch (Exception $e) {
			$this->getResponse()->setBody($e->getMessage());
		}
	}
}