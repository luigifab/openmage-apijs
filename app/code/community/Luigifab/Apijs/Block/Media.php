<?php
/**
 * Created M/15/01/2013
 * Updated D/19/04/2015
 * Version 14
 *
 * Copyright 2008-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Apijs_Block_Media extends Mage_Catalog_Block_Product_View_Media {

	protected function _construct() {
		$this->setModuleName('Mage_Catalog');
	}

	public function getBaseImage($images, $total) {

		$product = $this->getProduct();
		$showWidth  = intval(Mage::getStoreConfig('apijs/gallery/picture_width'));
		$showHeight = intval(Mage::getStoreConfig('apijs/gallery/picture_height'));

		// <img>
		// l'image de l'image = une miniature en cache
		// uniquement si le produit n'a pas d'image
		if ($total < 1) {
			$ressource = $this->helper('catalog/image')->init($product, 'image', $product->getImage())->resize($showWidth, $showHeight);
			return '<img src="'.$ressource.'" width="'.$showWidth.'" height="'.$showHeight.'" alt="" />';
		}

		// utilise l'image sélectionnée en tant qu'image de base
		$id = 0;
		foreach ($images as $image) {
			if ($image->getFile() === $product->getImage()) {
				$class = ($total > 1) ? 'class="slideshow.0.'.$id.'"' : '';
				$label = $this->htmlEscape($image->getLabel());
				$file  = $image->getPath();
				break;
			}
			$id++;
		}

		// redimensionne l'image si l'image dépasse 1200x900 px
		// ne fait rien dans les autres cas (utilise l'image source)
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile());
		list($width, $height) = getimagesize($file);
		if ($width > 1200)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(1200, null);
		else if ($height > 900)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(null, 900);

		// <a> <img> id=0.999 [class=0.$id si l'image de base n'est pas la première image)
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une miniature en cache (show)
		if ($total > 1) {
			$data  = '<a href="'.$ressource.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false;" '.$class.' id="slideshow.0.999">';
			$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($showWidth, $showHeight);
			$data .=  '<img src="'.$ressource.'" width="'.$showWidth.'" height="'.$showHeight.'" alt="'.$label.'" />';
			$data .= '</a>';
		}
		// <a> <img> <input> id=0.0
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une miniature en cache (show)
		else {
			$data  = '<a href="'.$ressource.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false;" id="slideshow.0.0">';
			$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($showWidth, $showHeight);
			$data .=  '<img src="'.$ressource.'" width="'.$showWidth.'" height="'.$showHeight.'" alt="'.$label.'" />';
			$data .=  '<input type="hidden" value="false|false|'.$label.'" />';
			$data .= '</a>';
		}

		return $data;
	}

	public function getThumbnail($image, $id) {

		$thumbWidth  = intval(Mage::getStoreConfig('apijs/gallery/thumbnail_width'));
		$thumbHeight = intval(Mage::getStoreConfig('apijs/gallery/thumbnail_height'));
		$showWidth   = intval(Mage::getStoreConfig('apijs/gallery/picture_width'));
		$showHeight  = intval(Mage::getStoreConfig('apijs/gallery/picture_height'));

		$product = $this->getProduct();
		$class = ($image->getFile() === $product->getImage()) ? 'class="current"' : '';
		$label = $this->htmlEscape($image->getLabel());
		$file  = $image->getPath();

		// redimensionne l'image si l'image dépasse 1200x900 px
		// ne fait rien dans les autres cas (utilise l'image source)
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile());
		list($width, $height) = getimagesize($file);
		if ($width > 1200)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(1200, null);
		else if ($height > 900)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(null, 900);

		// <a> <img> <input> id=0.$id
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une petite miniature en cache (thumb)
		// l'image de l'input = une miniature en cache (show)
		$data  = '<a href="'.$ressource.'" type="'.mime_content_type($file).'" onclick="return false;" '.$class.' id="slideshow.0.'.$id.'">';
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($thumbWidth, $thumbHeight);
		$data .=  '<img src="'.$ressource.'" width="'.$thumbWidth.'" height="'.$thumbHeight.'" alt="'.$label.'" />';
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($showWidth, $showHeight);
		$data .=  '<input type="hidden" value="'.$ressource.'|false|false|'.$label.'" />';
		$data .= '</a>';

		return $data;
	}
}