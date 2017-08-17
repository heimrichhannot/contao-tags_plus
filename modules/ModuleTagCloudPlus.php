<?php

namespace HeimrichHannot\TagsPlus;

use Contao\ModuleTagCloud;

class ModuleTagCloudPlus extends ModuleTagCloud
{
    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### TAGCLOUD ###';

            return $objTemplate->parse();
        }

        $this->strTemplate = (strlen($this->cloud_template)) ? $this->cloud_template : $this->strTemplate;

        $taglist = new \Contao\TagList();
        $taglist->addNamedClass = $this->tag_named_class;
        if (strlen($this->tag_tagtable)) $taglist->tagtable = $this->tag_tagtable;
        if (strlen($this->tag_tagfield)) $taglist->tagfield = $this->tag_tagfield;
        if (strlen($this->tag_sourcetables)) $taglist->fortable = deserialize($this->tag_sourcetables, TRUE);
        if (strlen($this->tag_topten_number) && $this->tag_topten_number > 0) $taglist->topnumber = $this->tag_topten_number;
        if (strlen($this->tag_maxtags)) $taglist->maxtags = $this->tag_maxtags;
        if (strlen($this->tag_buckets) && $this->tag_buckets > 0) $taglist->buckets = $this->tag_buckets;
        if (strlen($this->pagesource)) $taglist->pagesource = deserialize($this->pagesource, TRUE);
        if ($this->tags_additionalSql) $taglist->tags_additionalSql = $this->tags_additionalSql;
        if ($this->tags_additionalWhereSql) $taglist->tags_additionalWhereSql = $this->tags_additionalWhereSql;
        $this->arrTags = $taglist->getTagList();
        if ($this->tag_topten) $this->arrTopTenTags = $taglist->getTopTenTagList();
        if (strlen(\Input::get('tag')) && $this->tag_related)
        {
            $relatedlist = (strlen(\Input::get('related'))) ? preg_split("/,/", \Input::get('related')) : array();
            $this->arrRelated = $taglist->getRelatedTagList(array_merge(array(\Input::get('tag')), $relatedlist));
        }
        if (count($this->arrTags) < 1)
        {
            return '';
        }
        $this->toggleTagCloud();
        return \Module::generate();
    }

    protected function showTags()
	{
		$this->loadLanguageFile('tl_module');
		$strUrl = ampersand(\Environment::get('request'), ENCODE_AMPERSANDS);
		// Get target page
		$objPageObject = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
			->limit(1)
			->execute($this->tag_jumpTo);
		global $objPage;
		$default = ($objPage != null) ? $objPage->row() : array();
		$pageArr = ($objPageObject->numRows) ? $objPageObject->fetchAssoc() : $default;
		$strParams = '';
		if ($this->keep_url_params)
		{
			$strParams = \TagHelper::getSavedURLParams($this->Input);
		}
		foreach ($this->arrTags as $idx => $tag)
		{
			if (count($pageArr))
			{
				if ($tag['tag_name'] != \Input::get('tag') && $tag['tag_name'] != str_replace('|slash|', '/', \Input::get('tag')))
					$strUrl = ampersand($this->generateFrontendUrl($pageArr, '/tag/' . str_replace('/', '|slash|', \System::urlencode($tag['tag_name']))));
				else
					$strUrl = ampersand($this->generateFrontendUrl($pageArr));
				if (strlen($strParams))
				{
					if (strpos($strUrl, '?') !== false)
					{
						$strUrl .= '&amp;' . $strParams;
					}
					else
					{
						$strUrl .= '?' . $strParams;
					}
				}
			}
			$this->arrTags[$idx]['tag_url'] = $strUrl;
			if ($tag['tag_name'] == \Input::get('tag') || $tag['tag_name'] == str_replace('|slash|', '/', \Input::get('tag')))
			{
				$this->arrTags[$idx]['tag_class'] .= ' active';
			}
			if ($this->checkForArticleOnPage)
			{
				global $objPage;
				// get articles on page
				$arrArticles = $this->Database->prepare("SELECT id FROM tl_article WHERE pid = ?")
					->execute($objPage->id)->fetchEach('id');
				$arrTagIds = $this->Database->prepare("SELECT tid FROM " . $this->tag_tagtable . " WHERE from_table = ? AND tag = ?")
					->execute('tl_article', $tag['tag_name'])->fetchEach('tid');
				if (count(array_intersect($arrArticles, $arrTagIds)))
				{
					$this->arrTags[$idx]['tag_class'] .= ' here';
				}
			}
			if ($this->checkForContentElementOnPage)
			{
				global $objPage;
				// get articles on page
				$arrArticles = $this->Database->prepare("SELECT id FROM tl_article WHERE pid = ?")
					->execute($objPage->id)->fetchEach('id');
				if (count($arrArticles))
				{
					$arrCE = $this->Database->prepare("SELECT id FROM tl_content WHERE pid IN (" . implode(",", $arrArticles) . ")")
						->execute()->fetchEach('id');
					$arrTagIds = $this->Database->prepare("SELECT tid FROM " . $this->tag_tagtable . " WHERE from_table = ? AND tag = ?")
						->execute('tl_content', $tag['tag_name'])->fetchEach('tid');
					if (count(array_intersect($arrCE, $arrTagIds)))
					{
						$this->arrTags[$idx]['tag_class'] .= ' here';
					}
				}
			}
		}
		$relatedlist = (strlen(\Input::get('related'))) ? preg_split("/,/", \Input::get('related')) : array();
		foreach ($this->arrRelated as $idx => $tag)
		{
			if (count($pageArr))
			{
				if ($tag['tag_name'] != \Input::get('tag'))
					$strUrl = ampersand($this->generateFrontendUrl($pageArr, '/tag/' . str_replace('/', '|slash|', \System::urlencode(\Input::get('tag'))) . '/related/' . str_replace('/', '|slash|', \System::urlencode(join(array_merge($relatedlist, array($tag['tag_name'])), ',')))));
				else
					$strUrl = ampersand($this->generateFrontendUrl($pageArr));

			}
			$this->arrRelated[$idx]['tag_url'] = $strUrl;
		}
		$this->Template->pageID = $this->id;
		$this->Template->tags = $this->arrTags;
		$this->Template->jumpTo = $this->jumpTo;
		$this->Template->relatedtags = $this->arrRelated;
		$this->Template->strRelatedTags = $GLOBALS['TL_LANG']['tl_module']['tag_relatedtags'];
		$this->Template->strAllTags = $GLOBALS['TL_LANG']['tl_module']['tag_alltags'];
		$this->Template->strTopTenTags = sprintf($GLOBALS['TL_LANG']['tl_module']['top_tags'], $this->tag_topten_number);
		$this->Template->tagcount = count($this->arrTags);
		$this->Template->selectedtags = (strlen(\Input::get('tag'))) ? (count($this->arrRelated)+1) : 0;
		if ($this->tag_show_reset)
		{
			$strEmptyUrl = ampersand($this->generateFrontendUrl($pageArr, ''));
			if (strlen($strParams))
			{
				if (strpos($strUrl, '?') !== false)
				{
					$strEmptyUrl .= '&amp;' . $strParams;
				}
				else
				{
					$strEmptyUrl .= '?' . $strParams;
				}
			}
			$this->Template->empty_url = $strEmptyUrl;
			$this->Template->lngEmpty = $GLOBALS['TL_LANG']['tl_module']['tag_clear_tags'];
		}
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/tags/assets/tagcloud.js';
		if (count($pageArr))
		{
			$this->Template->topten = $this->tag_topten;
			if ($this->tag_topten)
			{
				foreach ($this->arrTopTenTags as $idx => $tag)
				{
					if (count($pageArr))
					{
						if ($tag['tag_name'] != \Input::get('tag'))
							$strUrl = ampersand($this->generateFrontendUrl($pageArr, '/tag/' . str_replace('/', '|slash|', \System::urlencode($tag['tag_name']))));
						else
							$strUrl = ampersand($this->generateFrontendUrl($pageArr));
						if (strlen($strParams))
						{
							if (strpos($strUrl, '?') !== false)
							{
								$strUrl .= '&amp;' . $strParams;
							}
							else
							{
								$strUrl .= '?' . $strParams;
							}
						}
					}
					if ($this->arrTopTenTags[$idx]['tag_name'] == str_replace('|slash|', '/', \Input::get('tag')))
					{
						$this->arrTopTenTags[$idx]['tag_class'] .= ' active';
					}
					$this->arrTopTenTags[$idx]['tag_url'] = $strUrl;
				}
				$ts = deserialize(\Input::cookie('tagcloud_states'), true);
//				$ts = $this->Session->get('tagcloud_states');
				$this->Template->expandedTopTen = (strlen($ts[$this->id]['topten'])) ? ((strcmp($ts[$this->id]['topten'], 'none') == 0) ? 0 : 1) : $this->tag_topten_expanded;
				$this->Template->expandedAll = (strlen($ts[$this->id]['alltags'])) ? ((strcmp($ts[$this->id]['alltags'], 'none') == 0) ? 0 : 1) : $this->tag_all_expanded;
				$this->Template->expandedRelated = (strlen($ts[$this->id]['related'])) ? ((strcmp($ts[$this->id]['related'], 'none') == 0) ? 0 : 1) : 1;
				$this->Template->toptentags = $this->arrTopTenTags;
			}
		}
	}
}

?>