<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['tags_additionalWhereSql'] = ['Zusätzliches WHERE-SQL', 'Geben Sie hier SQL ein, welches dem WHERE-Statement hinzugefügt wird.'];
$arrLang['tags_additionalSql']      = [
    'Zusätzliches SQL',
    'Geben Sie hier SQL ein, welches nach dem SELECT-Statement eingefügt wird (bspw. INNER JOIN tl_tag ON tl_calendar_events.id = tl_tag.tid).'
];