<?php
/**
 * Created J/05/09/2019
 * Updated V/18/06/2021
 *
 * Copyright 2008-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2019-2021 | Fabrice Creuzot <fabrice~cellublue~com>
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

// de manière à empécher de lancer cette procédure plusieurs fois
$lock = Mage::getModel('index/process')->setId('apijs_setup');
if ($lock->isLocked())
	Mage::throwException('Please wait, install is already in progress...');

$lock->lockAndBlock();
$this->startSetup();

// de manière à continuer quoi qu'il arrive
ignore_user_abort(true);
set_time_limit(0);

try {
	$read   = $this->getConnection();
	$fields = $read->fetchAll('SHOW COLUMNS FROM '.$this->getTable('catalog_product_entity_media_gallery_value'));

	foreach ($fields as $field) {
		if ((empty($field['Null']) || ($field['Null'] != 'YES')) && (mb_stripos($field['Field'], '_id') === false)) {
			$this->run('ALTER TABLE '.$this->getTable('catalog_product_entity_media_gallery_value').
				' MODIFY COLUMN '.$field['Field'].' '.$field['Type'].' NULL DEFAULT '.(($field['Default'] != '') ? $field['Default'] : 'NULL'));
		}
	}
}
catch (Throwable $t) {
	$lock->unlock();
	throw $t;
}

$this->endSetup();
$lock->unlock();