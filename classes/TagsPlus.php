<?php

namespace HeimrichHannot\TagsPlus;


/**
 * Class FormSelectMenu
 *
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class TagsPlus extends \Controller
{
	public static function saveTags($strTable, $intId, $varValue)
	{
		$objDatabase = \Database::getInstance();
		$strTable = $GLOBALS['TL_DCA'][$strTable]['fields']['tags']['eval']['table'] ?: $strTable;

		$objDatabase->prepare("DELETE FROM tl_tag WHERE from_table = ? AND tid = ?")
			->execute($strTable, $intId);

		foreach (deserialize($varValue, true) as $strTag)
		{
			$objDatabase->prepare("INSERT INTO tl_tag (tid, tag, from_table) VALUES (?, ?, ?)")
				->execute($intId, $strTag, $strTable);
		}
	}

	public static function loadTags($strTable, $intId=null)
	{
		if ($intId)
		{
			if (($objTag = \Contao\TagModel::findByIdAndTable($intId, $GLOBALS['TL_DCA'][$strTable]['fields']['tags']['eval']['table'] ?: $strTable)) !== null)
			{
				return array_values($objTag->fetchEach('tag'));
			}
		}
		else
		{
			if (($objTag = \Contao\TagModel::findBy('from_table', $GLOBALS['TL_DCA'][$strTable]['fields']['tags']['eval']['table'] ?: $strTable)) !== null)
			{
				return array_values($objTag->fetchEach('tag'));
			}
		}
	}

}