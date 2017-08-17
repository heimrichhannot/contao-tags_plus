<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$arrDca['palettes']['tagcloud'] = str_replace('tag_tagfield', 'tag_tagfield,tags_additionalSql,tags_additionalWhereSql', $arrDca['palettes']['tagcloud']);

/**
 * Fields
 */
$arrFields = [
    'tags_additionalWhereSql'              => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['tags_additionalWhereSql'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 255, 'tl_class' => 'long clr', 'decodeEntities' => true],
        'sql'       => "varchar(255) NOT NULL default ''"
    ],
    'tags_additionalSql'                   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['tags_additionalSql'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 255, 'tl_class' => 'long clr', 'decodeEntities' => true],
        'sql'       => "varchar(255) NOT NULL default ''"
    ]
];

$arrDca['fields'] = array_merge($arrDca['fields'], $arrFields);