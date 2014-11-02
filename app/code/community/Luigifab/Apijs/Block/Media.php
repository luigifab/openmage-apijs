<?php
/**
 * Created M/15/01/2013
 * Updated D/02/11/2014
 * Version 12
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

class Luigifab_Apijs_Block_Media extends Mage_Catalog_Block_Product_View_Media {

	protected function _construct() {
		$this->setModuleName('Mage_Catalog');
	}

	public function getPhoto() {

		// oui c'est pas jolie, mais c'est mieux qu'une erreur
		if ($this->getProduct()->getImage() == 'no_selection')
			return '';

		$showWidth  = Mage::getStoreConfig('apijs/gallery/picture_width');
		$showHeight = Mage::getStoreConfig('apijs/gallery/picture_height');

		$label     = $this->htmlEscape($this->getImageLabel());
		$product   = $this->getProduct();
		$ressource = $this->helper('catalog/image')->init($product, 'image', $product->getImage());
		$file      = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath().$product->getImage();

		// redimensionne l'image si l'image dépasse 1200x900 px
		// ne fait rien dans les autres cas (utilise l'image source)
		$sizes = getimagesize($file);
		if ($sizes[0] > 1200)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(1200, null);
		else if ($sizes[1] > 900)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(null, 900);

		// <a> <img> [<input> si une seule image]
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une miniature en cache (show)
		if (count($this->getGalleryImages()) > 1) {
			$data  = '<a href="'.$ressource.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false;" id="slideshow.0.999">';
			$ressource = $this->helper('catalog/image')->init($product, 'image')->resize($showWidth, $showHeight);
			$data .=  '<img src="'.$ressource.'" width="'.$showWidth.'" height="'.$showHeight.'" alt="'.$label.'" />';
			$data .= '</a>';
		}
		else {
			$data  = '<a href="'.$ressource.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false;" id="slideshow.0.0">';
			$ressource = $this->helper('catalog/image')->init($product, 'image')->resize($showWidth, $showHeight);
			$data .=  '<img src="'.$ressource.'" width="'.$showWidth.'" height="'.$showHeight.'" alt="'.$label.'" />';
			$data .=  '<input type="hidden" value="false|false|'.$label.'" />';
			$data .= '</a>';
		}

		return $data;
	}

	public function getThumbnail($image, $number) {

		$thumbWidth  = Mage::getStoreConfig('apijs/gallery/thumbnail_width');
		$thumbHeight = Mage::getStoreConfig('apijs/gallery/thumbnail_height');
		$showWidth   = Mage::getStoreConfig('apijs/gallery/picture_width');
		$showHeight  = Mage::getStoreConfig('apijs/gallery/picture_height');

		$class     = ($number < 1) ? 'class="current"' : '';
		$label     = $this->htmlEscape($image->getLabel());
		$product   = $this->getProduct();
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile());
		$file      = $image->getPath();

		// redimensionne l'image si l'image dépasse 1200x900 px
		// ne fait rien dans les autres cas (utilise l'image source)
		$sizes = getimagesize($file);
		if ($sizes[0] > 1200)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(1200, null);
		else if ($sizes[1] > 900)
			$ressource->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->resize(null, 900);

		// <a> <img> <input>
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une petite miniature en cache (thumb)
		// l'image de l'input = une miniature en cache (show)
		$data  = '<a href="'.$ressource.'" type="'.mime_content_type($file).'" onclick="return false;" '.$class.' id="slideshow.0.'.$number.'">';
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($thumbWidth, $thumbHeight);
		$data .=  '<img src="'.$ressource.'" width="'.$thumbWidth.'" height="'.$thumbHeight.'" alt="'.$label.'" />';
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($showWidth, $showHeight);
		$data .=  '<input type="hidden" value="'.$ressource.'|false|false|'.$label.'" />';
		$data .= '</a>';

		return $data;
	}
}