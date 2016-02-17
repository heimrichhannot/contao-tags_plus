<?php

namespace HeimrichHannot\TagsPlus;


class TagsPlusNewsModel extends \Contao\TagsNewsModel
{

	/**
	 * Find published news items by their parent ID
	 * 
	 * @param array   $arrPids     An array of news archive IDs
	 * @param boolean $blnFeatured If true, return only featured news, if false, return only unfeatured news
	 * @param integer $intLimit    An optional limit
	 * @param integer $intOffset   An optional offset
	 * 
	 * @return \Model\Collection|null A collection of models or null if there are no news
	 */
	public static function findPublishedByPidsAndIdsCustomOrder($arrPids, $arrIds, $blnFeatured=null, $intLimit=0, $intOffset=0, $strOrder='')
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");
		$arrColumns[] = "$t.id IN(" . implode(',', array_map('intval', $arrIds)) . ")";

		if ($blnFeatured === true)
		{
			$arrColumns[] = "$t.featured=1";
		}
		elseif ($blnFeatured === false)
		{
			$arrColumns[] = "$t.featured=''";
		}

		// Never return unpublished elements in the back end, so they don't end up in the RSS feed
		if (!BE_USER_LOGGED_IN || TL_MODE == 'BE')
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		$arrOptions = array
		(
			'order'  => ($strOrder ?: "$t.date DESC"),
			'limit'  => $intLimit,
			'offset' => $intOffset
		);

		return static::findBy($arrColumns, null, $arrOptions);
	}
}
