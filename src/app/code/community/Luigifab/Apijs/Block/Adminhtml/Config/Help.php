<?php
/**
 * Created V/23/05/2014
 * Updated D/26/11/2023
 *
 * Copyright 2008-2025 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$msg = $this->checkRewrites();
		if ($msg !== true)
			return sprintf('<p class="box">%s %s <span class="right">Stop russian war. <b>🇺🇦 Free Ukraine!</b> | <a href="https://github.com/luigifab/%3$s">github.com</a> | <a href="https://www.%4$s">%4$s</a> - <a href="https://www.%5$s">%5$s</a> - ⚠ IPv6</span></p><p class="box" style="margin-top:-5px; color:white; background-color:#E60000;"><strong>%6$s</strong><br />%7$s</p>',
				'Luigifab/Apijs', $this->helper('apijs')->getVersion(), 'openmage-apijs', 'luigifab.fr/openmage/apijs', 'luigifab.fr/apijs',
				$this->__('INCOMPLETE MODULE INSTALLATION'),
				$this->__('There is conflict (<em>%s</em>).', $msg));

		return sprintf('<p class="box">%s %s <span class="right">Stop russian war. <b>🇺🇦 Free Ukraine!</b> | <a href="https://github.com/luigifab/%3$s">github.com</a> | <a href="https://www.%4$s">%4$s</a> - <a href="https://www.%5$s">%5$s</a> - ⚠ IPv6</span></p>',
			'Luigifab/Apijs', $this->helper('apijs')->getVersion(), 'openmage-apijs', 'luigifab.fr/openmage/apijs', 'luigifab.fr/apijs');
	}

	protected function checkRewrites() {

		$rewrites = [
			['block' => 'adminhtml/cache_additional'],
			['block' => 'adminhtml/catalog_category_helper_image'],
			['block' => 'adminhtml/catalog_product_helper_form_gallery_content'],
			['block' => 'adminhtml/catalog_product_helper_form_image'],
			['block' => 'adminhtml/system_config_form_field_image'],
			['helper' => 'catalog/image'],
			['model' => 'adminhtml/system_config_backend_image'],
			['model' => 'adminhtml/system_config_backend_image_pdf'],
			['model' => 'catalog/category_attribute_backend_image'],
			['model' => 'catalog/product_attribute_backend_media'],
			['model' => 'catalog_resource/product_attribute_backend_image'],
			['model' => 'catalog_resource/product_attribute_backend_media'],
			['model' => 'cms/wysiwyg_images_storage'],
			['model' => 'core/file_validator_image'],
			['model' => 'varien/image'],
		];

		foreach ($rewrites as $rewrite) {
			foreach ($rewrite as $type => $class) {
				if (($type == 'model') && (mb_stripos(Mage::getConfig()->getModelClassName($class), 'luigifab') === false))
					return $class;
				if (($type == 'block') && (mb_stripos(Mage::getConfig()->getBlockClassName($class), 'luigifab') === false))
					return $class;
				if (($type == 'helper') && (mb_stripos(Mage::getConfig()->getHelperClassName($class), 'luigifab') === false))
					return $class;
			}
		}

		return true;
	}
}