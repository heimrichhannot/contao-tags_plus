<?php


namespace HeimrichHannot\TagsPlus;

class TagFieldExtended extends \Contao\TagField
{

	public function generate()
	{
		// HHFIX
		$taglist = new \Contao\TagList($GLOBALS['TL_DCA'][$this->table]['fields']['tags']['eval']['optionsTable'] ?: $this->table);
		// HHENDFIX
		$taglist->maxtags = $this->intMaxTags;
		$tags = $taglist->getTagList();
		$list = '<div class="tags"><ul class="cloud">';
		foreach ($tags as $tag)
		{
			$list .= '<li class="' . $tag['tag_class'] . '">';
			$list .= '<a href="javascript:Tag.selectedTag(\'' . $tag['tag_name'] . '\', \'ctrl_' . $this->strId . '\');" title="' . $tag['tag_name'] . ' (' . $tag['tag_count'] . ')' . '">' . $tag['tag_name'] . '</a>';
			$list .= '</li> ';
		}
		$list .= '</ul></div>';
		$value = (!$this->blnSubmitInput) ? $this->readTags() : $this->varValue;
		return $list.sprintf('<input type="text" name="%s" id="ctrl_%s" class="tl_text%s" value="%s"%s onfocus="Backend.getScrollOffset();" />',
						$this->strName,
						$this->strId,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						specialchars($value),
						$this->getAttributes());
	}

}

?>