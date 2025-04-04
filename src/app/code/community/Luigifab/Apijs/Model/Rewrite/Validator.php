<?php
/**
 * Created S/09/05/2020
 * Updated S/30/12/2023
 *
 * Copyright 2008-2025 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Apijs_Model_Rewrite_Validator extends Mage_Core_Model_File_Validator_Image {

	/**
	 * @return null (not true or false)
	 * @throws Mage_Core_Exception
	 */
	public function validate($path) {

		if (!is_file($path))
			Mage::throwException('Invalid image: file not found.');

		if (!Mage::getStoreConfigFlag('apijs/general/python'))
			return parent::validate($path);

		// detect mime type
		$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($fileInfo, $path);
		finfo_close($fileInfo);
		if (!in_array($mimeType, Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions('image', true)))
			Mage::throwException(sprintf('Invalid image: file type not authorized (%s).', $mimeType));

		// replace tmp image with re-sampled copy to exclude images with malicious data
		$processor = Mage::getSingleton('apijs/python');
		$processor->setFilename($path);
		$processor->quality(100);
		$processor->resize($processor->getOriginalWidth(), $processor->getOriginalHeight());
		$processor->save($path, null, true);

		if (!is_file($path))
			Mage::throwException('Invalid image: error during image processing.');

		return null;
	}
}