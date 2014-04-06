<?php
/**
 * Created M/15/01/2013
 * Updated L/24/03/2014
 * Version 5
 *
 * Copyright 2013-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

		$ressource = $this->helper('catalog/image')->init($product, 'image');
		$filepath = str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), Mage::getBaseDir('media').'/', $ressource);

		// configuration
		$showWidth = Mage::getStoreConfig('apijs/gallery/picture_width');
		$showHeight = Mage::getStoreConfig('apijs/gallery/picture_height');

		// <a> <img> <input>
		$data  = '<a href="'.$ressource.'" type="'.mime_content_type($filepath).'" title="'.$label.'" onclick="return false;" id="slideshow.0.999">';
		$ressource = $this->helper('catalog/image')->init($product, 'image')->resize($showWidth, $showHeight);
		$data .=  '<img src="'.$ressource.'" width="'.$showWidth.'" height="'.$showHeight.'" alt="'.$label.'" />';
		$data .=  '<input type="hidden" value="null|false|false|'.$label.'" />';
		$data .= '</a>';

		return $data;
	}

	public function getThumbnail($image, $label) {

		$product = $this->getProduct();
		$label = $this->htmlEscape((strlen($label) > 0) ? $label : $this->getImageLabel());

		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile());
		$filepath = $image->getPath();

		// configuration
		$showWidth = Mage::getStoreConfig('apijs/gallery/picture_width');
		$showHeight = Mage::getStoreConfig('apijs/gallery/picture_height');
		$thumbWidth = Mage::getStoreConfig('apijs/gallery/thumbnail_width');
		$thumbHeight = Mage::getStoreConfig('apijs/gallery/thumbnail_height');

		// <a> <img> <input>
		$data  = '<a href="'.$ressource.'" type="'.mime_content_type($filepath).'" onclick="return false;" id="slideshow.0.'.$this->number.'">';
		if ($this->number < 1) {
			$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($thumbWidth, $thumbHeight);
			$data .=  '<img src="'.$ressource.'" width="'.$thumbWidth.'" height="'.$thumbHeight.'" alt="'.$label.'" class="current" />';
		}
		else {
			$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($thumbWidth, $thumbHeight);
			$data .=  '<img src="'.$ressource.'" width="'.$thumbWidth.'" height="'.$thumbHeight.'" alt="'.$label.'" />';
		}
		$ressource = $this->helper('catalog/image')->init($product, 'image', $image->getFile())->resize($showWidth, $showHeight);
		$data .=  '<input type="hidden" value="'.$ressource.'|false|false|'.$label.'" />';
		$data .= '</a>';

		$this->number++;

		return $data;
	}
}