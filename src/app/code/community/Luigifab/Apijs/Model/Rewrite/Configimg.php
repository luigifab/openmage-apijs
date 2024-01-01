<?php
/**
 * Created J/19/10/2023
 * Updated S/30/12/2023
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

class Luigifab_Apijs_Model_Rewrite_Configimg extends Mage_Adminhtml_Model_System_Config_Backend_Image {

	public function getAllowedExtensions() {
		return Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions('image');
	}

	public function _getAllowedExtensions() {
		return $this->getAllowedExtensions();
	}

	protected function _beforeSave() {

		$dest   = $this->_getUploadDir();
		$orig   = $this->getOldValue();
		$result = $this;

		if (!empty($dest) && !empty($orig)) {

			$helper = Mage::helper('apijs');
			$value  = $this->getValue();

			if (!empty($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value']))
				$helper->removeFiles($dest, basename($orig), true); // everywhere (not only in cache dir)
			else if (!empty($value['delete']))
				$helper->removeFiles($dest, basename($orig)); // everywhere (not only in cache dir)
		}

		try {
			$result = parent::_beforeSave();
		}
		catch (Throwable $t) {
			if (!empty($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
				$this->setValue(null);
				Mage::getSingleton('adminhtml/session')->addError(sprintf(
					'Warning: image (<em>%s</em>) for configuration <em>%s</em> was not saved: %s',
					$_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'],
					$this->getPath(),
					$t->getMessage()
				));
			}
		}

		return $result;
	}
}