<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Forms
	'HeimrichHannot\TagsPlus\FormTags'           => 'system/modules/tags_plus/forms/FormTags.php',

	// Modules
	'HeimrichHannot\TagsPlus\ModuleTagCloudPlus' => 'system/modules/tags_plus/modules/ModuleTagCloudPlus.php',

	// Classes
	'HeimrichHannot\TagsPlus\TagsPlus'           => 'system/modules/tags_plus/classes/TagsPlus.php',

	// Models
	'HeimrichHannot\TagsPlus\TagsPlusNewsModel'  => 'system/modules/tags_plus/models/TagsPlusNewsModel.php',
));
