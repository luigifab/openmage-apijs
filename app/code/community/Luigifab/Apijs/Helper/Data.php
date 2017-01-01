<?php
/**
 * Created D/20/11/2011
 * Updated D/27/11/2016
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

class Luigifab_Apijs_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Apijs')->version;
	}

	public function getStatus($type = 'frontend') {
		return (Mage::getStoreConfigFlag('apijs/general/'.$type)) ? $this->__('Enabled') : $this->__('Disabled');
	}

	public function getDirectTabLink($id, $old = null) {

		$tab = null;
		$groups = Mage::getResourceModel('eav/entity_attribute_group_collection')->load();

		foreach ($groups as $group) {
			if ($group->getAttributeGroupName() === 'Images') {
				$tab = 'product_info_tabs_group_'.$group->getId();
				break;
			}
		}

		return Mage::helper('adminhtml')->getUrl('*/catalog_product/edit', array('id' => $id, 'tab' => $tab, 'old' => $old));
	}

	public function renderGalleryBlock($product) {

		$product->load($product->getId());
		Mage::register('current_product', $product);

		$block = Mage::getBlockSingleton('apijs/adminhtml_rewrite_gallery');
		$block->setElement($block);

		return 'success-'.$block->toHtml();
	}

	public function getNumberToHumanSize($number) {

		if ($number < 1) {
			return '';
		}
		else if (($number / 1024) < 1024) {
			$size = $number / 1024;
			$size = Zend_Locale_Format::toNumber($size, array('precision' => 2));
			return $this->__('%s kB', str_replace(array('.00',',00'), '', $size));
		}
		else if (($number / 1024 / 1024) < 1024) {
			$size = $number / 1024 / 1024;
			$size = Zend_Locale_Format::toNumber($size, array('precision' => 2));
			return $this->__('%s MB', str_replace(array('.00',',00'), '', $size));
		}
		else {
			$size = $number / 1024 / 1024 / 1024;
			$size = Zend_Locale_Format::toNumber($size, array('precision' => 2));
			return $this->__('%s GB', str_replace(array('.00',',00'), '', $size));
		}
	}
}