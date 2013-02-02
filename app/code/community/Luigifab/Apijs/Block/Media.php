<?php
/**
 * Created M/15/01/2013
 * Updated S/02/02/2013
 * Version 3
 *
 * Copyright 2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

	private $number = 0;

	public function getPhoto() {

		$product = $this->getProduct();
		$label = $this->htmlEscape($this->getImageLabel());

		// config
		$showWidth = Mage::getStoreConfig('apijs/gallery/picture_width');
		$showHeight = Mage::getStoreConfig('apijs/gallery/picture_height');

		// image source
		$ressource = $this->helper('catalog/image')->init($product, 'image');
		list($sourceWidth, $sourceHeight, $mime) = getimagesize($ressource);

		// <a> <img> <input>
		$data  = '<a href="'.$ressource.'" type="'.image_type_to_mime_type($mime).'" title="'.$label.'" onclick="return false;" id="diaporama.0.999">';
		$data .=  '<img src="'.$this->helper('catalog/image')->init($product, 'image')->resize($showWidth, $showHeight).'"';
		$data .=  ' width="'.$showWidth.'" height="'.$showHeight.'" alt="'.$label.'" />';
		$data .=  '<input type="hidden" value="'.$sourceWidth.'|'.$sourceHeight.'|false|false|'.$label.'" />';
		$data .= '</a>';

		return $data;
	}

	public function getThumbnail($image, $label) {

		$product = $this->getProduct();
		$label = (strlen($label) > 0) ? $label : $this->getImageLabel();
		$label = $this->htmlEscape($label);

		// config
		$showWidth = Mage::getStoreConfig('apijs/gallery/picture_width');
		$showHeight = Mage::getStoreConfig('apijs/gallery/picture_height');
		$thumbWidth = Mage::getStoreConfig('apijs/gallery/thumbnail_width');
		$thumbHeight = Mage::getStoreConfig('apijs/gallery/thumbnail_height');

		// image source
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile());
		list($sourceWidth, $sourceHeight, $mime) = getimagesize($image->getPath());

		// <a> <img> <input>
		$data  = '<a href="'.$ressource.'" type="'.image_type_to_mime_type($mime).'" onclick="return false;" id="diaporama.0.'.$this->number.'">';

		// class actif
		if ($this->number < 1) {
			$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($thumbWidth, $thumbHeight);
			$data .= '<img src="'.$ressource.'" width="'.$thumbWidth.'" height="'.$thumbHeight.'" alt="'.$label.'" class="actif" />';
		}
		else {
			$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($thumbWidth, $thumbHeight);
			$data .= '<img src="'.$ressource.'" width="'.$thumbWidth.'" height="'.$thumbHeight.'" alt="'.$label.'" />';
		}

		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($showWidth, $showHeight);
		$data .=  '<input type="hidden" value="'.$ressource.'|'.$sourceWidth.'|'.$sourceHeight.'|false|false|'.$label.'" />';

		$data .= '</a>';
		$this->number++;

		return $data;
	}
}