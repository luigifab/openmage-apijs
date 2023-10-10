<?php
/**
 * Forked from https://gist.github.com/peterjaap/5547654
 * Updated J/21/09/2023
 *
 * fdupes is required
 *
 * This script deletes duplicate images and imagerows from the database of which the images are not present in the filesystem.
 * It also removes images that are exact copies of another image for the same product.
 * And lastly, it looks for images that are on the filesystem but not in the database (orphaned images).
 *
 * This is NOT the original version.
 * USE IT AT YOUR OWN RISK!
 */

chdir(dirname($argv[0], 2)); // root
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (PHP_SAPI != 'cli')
	exit(-1);
if (is_file('maintenance.flag') || is_file('upgrade.flag'))
	exit(0);
if (is_file('app/bootstrap.php'))
	require_once('app/bootstrap.php');

$dev = !empty($_SERVER['MAGE_IS_DEVELOPER_MODE']) || !empty($_ENV['MAGE_IS_DEVELOPER_MODE']) || in_array('--dev', $argv);
require_once('app/Mage.php');

Mage::app('admin')->setUseSessionInUrl(false);
Mage::app()->addEventArea('crontab');
Mage::setIsDeveloperMode($dev);

$directory = realpath(Mage::getBaseDir('media').DS.'catalog'.DS.'product');
$resource  = Mage::getSingleton('core/resource');
$db = $resource->getConnection('core_write');


$eavAttribute = new Mage_Eav_Model_Resource_Entity_Attribute();
$thumbnailAttrId  = $eavAttribute->getIdByCode('catalog_product', 'thumbnail');
$smallImageAttrId = $eavAttribute->getIdByCode('catalog_product', 'small_image');
$imageAttrId      = $eavAttribute->getIdByCode('catalog_product', 'image');

// original config
$countProductWithoutImages = false;
$cleanUpDuplicates = false;
$cleanUpOrphans = false;
$cleanUpTableRowsMediaGallery = false;
$cleanUpTableRowsVarchar = false;
// custom config
$countProductWithoutImages = true;
$cleanUpDuplicates = true;
$cleanUpOrphans = true;
$cleanUpTableRowsMediaGallery = true;
$cleanUpTableRowsVarchar = true;
// end config


// display number of products without images
if ($countProductWithoutImages) {

	$result = $db->fetchCol('
		SELECT entitytable.entity_id FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' AS mediagallery
		RIGHT OUTER JOIN '.$resource->getTableName('catalog_product_entity').' AS entitytable ON entitytable.entity_id = mediagallery.entity_id
		WHERE mediagallery.value is NULL
	');
	echo count($result),' products without images',"\n";

	$products = Mage::getResourceModel('catalog/product_collection')->addFieldToFilter('entity_id', ['in' => $result]);
	echo "\033[36m"; // terminal color
	foreach ($products as $pid => $product)
		echo ' no images for product ',$product->getSku(),' (',$product->getId(),')',"\n";
	echo "\033[0m";

	echo "\n";
}

// clean up duplicates images @todo
if ($cleanUpDuplicates) {

	echo '=== clean up duplicates images ===',"\n";

	$output = exec('find '.$directory.' -type d -exec fdupes -n {} \;');
	$before = substr(exec('find '.$directory.' -type f | wc -l'), 0, -1); // not mb_substr

	// count files for difference calculation
	$total = exec('du -h '.$directory);
	$total = explode("\n",$total);
	array_pop($total);
	$total = array_pop($total);
	$total = explode("\t",$total);
	$total = (int) array_shift($total);
	$totalBefore = $total;
	$chunks = explode("\n\n", $output);

	// Run through duplicates and replace database rows
	// @todo
    foreach ($chunks as $chunk) {
        $files = explode("\n",$chunk);
        $original = array_shift($files);
        foreach ($files as $file) {
            // update database where filename=file set filename=original
            $original = DS.implode(DS,array_slice(explode(DS,$original), -3));
            $file = DS.implode(DS,array_slice(explode(DS,$file), -3));
            $oldFileOnServer = $directory.$file;
            $newFileOnServer = $directory.$original;
            if (file_exists($newFileOnServer) && file_exists($oldFileOnServer)) {
                $db->beginTransaction();
                $resultVarchar = $db->update('catalog_product_entity_varchar', ['value'=>$original], $db->quoteInto('value =?',$file));
                $resultGallery = $db->update('catalog_product_entity_media_gallery', ['value'=>$original], $db->quoteInto('value =?',$file));
                $db->commit();
                echo 'Replaced '.$file.' with '.$original.' ('.$resultVarchar.'/'.$resultGallery.')'."\n";
                unlink($oldFileOnServer);
                if (file_exists($oldFileOnServer)) {
                    die('File '.$oldFileOnServer.' not deleted; permissions issue?');
                }
            } else {
                if (!file_exists($oldFileOnServer)) {
                    echo 'File '.$oldFileOnServer.' does not exist.'."\n";
                }
                if (!file_exists($newFileOnServer)) {
                    echo 'File '.$newFileOnServer.' does not exist.'."\n";
                }
            }
        }
    }

	// calculate difference
	$after = substr(exec('find '.$directory.' -type f | wc -l'), 0, -1); // not mb_substr
	$total = exec('du -h '.$directory);
	$total = explode("\n",$total);
	array_pop($total);
	$total = array_pop($total);
	$total = explode("\t",$total);
	$total = (int) array_shift($total);
	$totalAfter = $total;

	echo '  in directory ',$directory,' the script has deleted ',($before - $after),' files, files before: ',$totalBefore,' files after:',$totalAfter,"\n";
	echo "\n";
}

// clean up orphaned images
if ($cleanUpOrphans) {

	echo '=== clean up orphaned images ===',"\n";

	$deleted = 0;
	$helper  = Mage::helper('core')->isModuleEnabled('Luigifab_Apijs') ? Mage::helper('apijs') : null;
	$files   = glob($directory.DS.'[A-z0-9]'.DS.'[A-z0-9]'.DS.'*');

	$cacheFiles = [];
	if (!empty($helper))
		exec('find '.$directory.' -type f', $cacheFiles);

	foreach ($files as $file) {

		if (!is_file($file))
			continue;

		$filename = DS.implode(DS,array_slice(explode(DS,$file),-3));
		//echo ' debug ',$filename,"\n";

		$results = $db->fetchAll('SELECT * FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' WHERE value = ?', [$filename]);
		if (count($results) == 0) {
			echo ' deleting orphaned image ',$filename,' as ',$file,"\n";
			if (!empty($helper)) {
				echo "\033[36m"; // terminal color
				$helper->removeFiles($directory, $filename, true, $cacheFiles); // everywhere (not only in cache dir)
				echo "\033[0m";
			}
			else {
				unlink($file);
			}
			$deleted++;
		}
	}

	echo ' deleted ',$deleted,"\n";
	echo "\n";
}

// clean up images from media gallery tables
if ($cleanUpTableRowsMediaGallery) {

	echo '=== clean up images from media gallery tables ===',"\n";

	$deleted   = 0;
	$images    = $db->fetchAll('SELECT value,value_id FROM '.$resource->getTableName('catalog_product_entity_media_gallery'));

	foreach ($images as $image) {
		if (!file_exists($directory.$image['value'])) {
			echo ' deleting ',$image['value'],' (database)',"\n";
			$db->query('DELETE FROM '.$resource->getTableName('catalog_product_entity_media_gallery').' WHERE value_id = ?', [$image['value_id']]);
			$db->query('DELETE FROM '.$resource->getTableName('catalog_product_entity_media_gallery_value').' WHERE value_id = ?', [$image['value_id']]);
			$deleted++;
		}
	}

	echo ' deleted ',$deleted,"\n";
	echo "\n";
}

// clean up images from varchar table
if ($cleanUpTableRowsVarchar) {

	echo '=== clean up images from varchar table === ',"\n";

	$deleted   = 0;
	$images    = $db->fetchAll('
		SELECT value, value_id FROM '.$resource->getTableName('catalog_product_entity_varchar').'
		WHERE (attribute_id = ? OR attribute_id = ? OR attribute_id = ?) AND value != "no_selection"
	', [$thumbnailAttrId, $smallImageAttrId,$imageAttrId]);

	foreach ($images as $image) {
		if (!file_exists($directory.$image['value'])) {
			echo ' deleting ',$image['value'],' (database)',"\n";
			$db->query('DELETE FROM '.$resource->getTableName('catalog_product_entity_varchar').' WHERE value_id = ?', [$image['value_id']]);
			$deleted++;
		}
	}

	echo ' deleted ',$deleted,"\n";
	echo "\n";
}

exit(0);