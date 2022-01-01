<?php
/**
 * Created S/09/05/2020
 * Updated J/04/11/2021
 *
 * Copyright 2008-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/apijs
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

class Luigifab_Apijs_Model_Rewrite_Validator extends Mage_Core_Model_File_Validator_Image {

	public function validate($path) {

		if (is_file($path) && in_array(mime_content_type($path), ['image/svg', 'image/svg+xml', 'application/pdf']))
			return true;

		if (!Mage::getStoreConfigFlag('apijs/general/python'))
			return parent::validate($path);

		// replace tmp image with re-sampled copy to exclude images with malicious data
		try {
			$processor = Mage::getSingleton('apijs/python');
			$processor->setFilename($path)->resize($processor->getOriginalWidth(), $processor->getOriginalHeight())->save($path);
		}
		catch (Throwable $t) {
			Mage::throwException('Invalid image: '.$t->getMessage());
		}

		return true;
	}
}