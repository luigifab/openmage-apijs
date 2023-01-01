<?php
/**
 * Created J/20/10/2022
 * Updated D/11/12/2022
 *
 * Copyright 2008-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

if (Mage::getStoreConfigFlag('apijs/general/python')) {
	// this allow to use OpenMage without PHP-GD (with PR 2666)
	class Luigifab_Apijs_Model_Rewrite_Varienimg extends Luigifab_Apijs_Model_Python {
		protected $_isVarienRewrite = true;
	}
}
else {
	class Luigifab_Apijs_Model_Rewrite_Varienimg extends Varien_Image {
		protected $_isVarienRewrite = false;
	}
}