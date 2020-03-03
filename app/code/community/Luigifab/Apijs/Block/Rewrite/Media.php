<?php
/**
 * Created M/15/01/2013
 * Updated D/26/01/2020
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

class Luigifab_Apijs_Block_Rewrite_Media extends Mage_Catalog_Block_Product_View_Media {

	protected function _construct() {
		$this->setModuleName('Mage_Catalog');
	}

	public function resizeImage(...$args) {
		return $this->helper('apijs')->resizeImage($this->getProduct(), ...$args);
	}

	public function getBaseImage($images, $total, $id = 0) {

		$this->setData('default_file', $this->getProduct()->getData('image'));

		$bWidth  = (int) Mage::getStoreConfig('apijs/gallery/picture_width');
		$bHeight = (int) Mage::getStoreConfig('apijs/gallery/picture_height');
		$class   = '';

		// utilise l'image sélectionnée en tant qu'image de base (si l'image existe encore)
		foreach ($images as $image) {
			if ($image->getData('file') == $this->getData('default_file')) {
				$class = ($id > 0) ? 'class="slideshow.0.'.$id.'"' : '';
				$path  = $image->getData('file');
				$file  = $this->helper('apijs')->getCatalogProductImageDir().$path;
				$label = $this->helper('apijs')->escapeEntities($image->getData('label'), true);
				break;
			}
			$id++;
		}

		// utilise la première image existante en tant qu'image de base
		// si l'image sélectionnée en tant qu'image de base n'existe plus
		if (!empty($images) && empty($path)) {
			$image = $images[0];
			$path  = $image->getData('file');
			$file  = $this->helper('apijs')->getCatalogProductImageDir().$path;
			$label = $this->helper('apijs')->escapeEntities($image->getData('label'), true);
			$this->setData('default_file', $path);
		}

		// <img src srcset>
		// l'image de l'image = une miniature en cache
		// utilise l'image par défaut en tant qu'image de base si aucune image n'existe
		// utilise l'image par défaut en tant qu'image de base si le produit n'a pas d'image
		if (empty($images) || empty($path)) {
			$img1 = $this->resizeImage('image', $this->getData('default_file'), $bWidth, $bHeight);
			$img2 = $this->resizeImage('image', $this->getData('default_file'), $bWidth * 2, $bHeight * 2);
			return '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$bWidth.'" height="'.$bHeight.'" alt="" />';
		}

		// <a> <img src srcset> id=0.99999
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une miniature en cache (main)
		if ($total > 1) {
			$img0  = $this->resizeImage('image', $path, 1200, 900, true);
			$data  = '<a href="'.$img0.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false" '.$class.' id="slideshow.0.99999">';
			$img1  = $this->resizeImage('image', $path, $bWidth, $bHeight);
			$img2  = $this->resizeImage('image', $path, $bWidth * 2, $bHeight * 2);
			$data .=  '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$bWidth.'" height="'.$bHeight.'" alt="'.$label.'" />';
			$data .= '</a>';
		}
		// <a> <img src srcset> <input> id=0.0
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une miniature en cache (main)
		else {
			$img0  = $this->resizeImage('image', $path, 1200, 900, true);
			$data  = '<a href="'.$img0.'" type="'.mime_content_type($file).'" title="'.$label.'" onclick="return false" id="slideshow.0.0">';
			$img1  = $this->resizeImage('image', $path, $bWidth, $bHeight);
			$img2  = $this->resizeImage('image', $path, $bWidth * 2, $bHeight * 2);
			$data .=  '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$bWidth.'" height="'.$bHeight.'" alt="'.$label.'" />';
			$data .=  '<input type="hidden" value="false|false|'.$label.'" />';
			$data .= '</a>';
		}

		return $data;
	}

	public function getThumbnail($image, $id) {

		$tWidth  = (int) Mage::getStoreConfig('apijs/gallery/thumbnail_width');
		$tHeight = (int) Mage::getStoreConfig('apijs/gallery/thumbnail_height');
		$bWidth  = (int) Mage::getStoreConfig('apijs/gallery/picture_width');
		$bHeight = (int) Mage::getStoreConfig('apijs/gallery/picture_height');

		$path  = $image->getData('file');
		$file  = $this->helper('apijs')->getCatalogProductImageDir().$path;
		$label = $this->helper('apijs')->escapeEntities($image->getData('label'), true);
		$class = ($path == $this->getData('default_file')) ? 'class="current"' : '';

		// <a> <img src srcset> <input> id=0.$id
		// l'image du lien = une image redimensionnée en cache
		// l'image de l'image = une petite miniature en cache (thumb)
		// l'image de l'input = une miniature en cache (main)
		$img0  = $this->resizeImage('image', $path, 1200, 900, true);
		$data  = '<a href="'.$img0.'" type="'.mime_content_type($file).'" onclick="return false" '.$class.' id="slideshow.0.'.$id.'">';
		$img1  = $this->resizeImage('thumbnail', $path, $tWidth, $tHeight);
		$img2  = $this->resizeImage('thumbnail', $path, $tWidth * 2, $tHeight * 2);
		$data .=  '<img src="'.$img1.'" srcset="'.$img2.' 2x" width="'.$tWidth.'" height="'.$tHeight.'" alt="'.$label.'" />';
		$img1  = $this->resizeImage('image', $path, $bWidth, $bHeight);
		$img2  = $this->resizeImage('image', $path, $bWidth * 2, $bHeight * 2);
		$data .=  '<input type="hidden" value="'.$img1.';'.$img2.' 2x|false|false|'.$label.'" />';
		$data .= '</a>';

		return $data;
	}
}