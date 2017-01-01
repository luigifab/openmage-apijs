<?php
/**
 * Created S/13/06/2015
 * Updated M/08/11/2016
 *
 * Copyright 2008-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Apijs_Model_Observer {

	public function deleteProductFiles($observer) {

		// EVENT catalog_product_delete_after_done
		$product = $observer->getEvent()->getProduct();

		if (Mage::getStoreConfigFlag('apijs/general/delete_cache') && is_object($product) && ($product->getId() > 0) &&
		    is_object($product->getMediaGalleryImages())) {

			foreach ($product->getMediaGalleryImages() as $image) {

				$filename = basename($image->getFile());

				// suppression des fichiers
				// recherche tous les fichiers avec la commande find
				// uniquement si le nom du fichier contient des caractères simples (et au mois 5 caractères x.xyz)
				// preg_match() retourne 1 si le pattern fourni correspond, 0 s'il ne correspond pas, ou FALSE si une erreur survient
				if (preg_match('#^[a-z0-9_\-]+\.[a-z0-9]{3,5}$#i', $filename) === 1) {
					Mage::log(sprintf('Removing all %s images with exec(find...) for OLD product %d', $filename, $product->getId()),
						Zend_Log::INFO, 'apijs.log');
					exec('find '.Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath().' -name '.$filename.' | xargs rm');
				}
			}
		}
	}
}