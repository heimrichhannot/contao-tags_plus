<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace HeimrichHannot\TagsPlus;
use Contao\TagModel;


/**
 * Class FormSelectMenu
 *
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class FormTags extends \Widget
{

	/**
	 * Submit user input
	 *
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Add a for attribute
	 *
	 * @var boolean
	 */
	protected $blnForAttribute = true;

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'form_select';

	public function __construct($arrAttributes=null)
	{
		parent::__construct($arrAttributes);

		$dca = $GLOBALS['TL_DCA'][$arrAttributes['tagTable']];

		$this->strTable = $dca['fields']['tags']['eval']['optionsTable'] ?:
			($dca['fields']['tags']['eval']['table'] ?: $arrAttributes['tagTable']);

		$arrOptions = $dca['fields']['tags']['options'];
		if ($arrOptions && is_array($arrOptions))
		{
			$this->arrOptions = array();

			if ($dca['fields']['tags']['eval']['includeBlankOption'])
				$this->arrOptions = array(array('value' => '', 'label' => '-'));

			foreach ($arrOptions as $strValue => $strLabel)
			{
				$this->arrOptions[] = array(
					'label' => $strLabel,
					'value' => $strValue,
					'group' => false
				);
			}
		}
		elseif (($objTags = TagModel::findByfrom_table($this->strTable)) !== null)
		{
			$this->arrOptions = array();
			while ($objTags->next())
			{
				if (!in_array(array(
					'label' => $objTags->tag,
					'value' => $objTags->tag,
					'group' => false
				), $this->arrOptions))
				{
					$this->arrOptions[] = array(
						'label' => $objTags->tag,
						'value' => $objTags->tag,
						'group' => false
					);
				}
			}
		}
	}


	/**
	 * Add specific attributes
	 *
	 * @param string $strKey   The attribute name
	 * @param mixed  $varValue The attribute value
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'mandatory':
				if ($varValue)
				{
					$this->arrAttributes['required'] = 'required';
				}
				else
				{
					unset($this->arrAttributes['required']);
				}
				parent::__set($strKey, $varValue);
				break;

			case 'mSize':
				if ($this->multiple)
				{
					$this->arrAttributes['size'] = $varValue;
				}
				break;

			case 'multiple':
				if ($varValue != '')
				{
					$this->arrAttributes['multiple'] = 'multiple';
				}
				break;

			case 'options':
				$this->arrOptions = deserialize($varValue);
				break;

			case 'rgxp':
			case 'minlength':
			case 'maxlength':
				// Ignore
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Check options if the field is mandatory
	 */
	public function validate()
	{
		$mandatory = $this->mandatory;
		$options = $this->getPost($this->strName);

		// Check if there is at least one value
		if ($mandatory && is_array($options))
		{
			foreach ($options as $option)
			{
				if (strlen($option))
				{
					$this->mandatory = false;
					break;
				}
			}
		}

		$varInput = $this->validator($options);

		// Check for a valid option (see #4383)
		if (!empty($varInput) && !$this->isValidOption($varInput))
		{
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalid'], (is_array($varInput) ? implode(', ', $varInput) : $varInput)));
		}

		// Add class "error"
		if ($this->hasErrors())
		{
			$this->class = 'error';
		}
		else
		{
			$this->varValue = $varInput;
		}

		// Reset the property
		if ($mandatory)
		{
			$this->mandatory = true;
		}
	}


	/**
	 * Return a parameter
	 *
	 * @param string $strKey The parameter name
	 *
	 * @return mixed The parameter value
	 */
	public function __get($strKey)
	{
		if ($strKey == 'options')
		{
			return $this->arrOptions;
		}

		return parent::__get($strKey);
	}


	/**
	 * Parse the template file and return it as string
	 *
	 * @param array $arrAttributes An optional attributes array
	 *
	 * @return string The template markup
	 */
	public function parse($arrAttributes=null)
	{
		$strClass = 'select';

		if ($this->multiple)
		{
			$this->strName .= '[]';
			$strClass = 'multiselect';
		}

		// Make sure there are no multiple options in single mode
		elseif (is_array($this->varValue))
		{
			$this->varValue = $this->varValue[0];
		}

		// Chosen
		if ($this->chosen)
		{
			$strClass .= ' tl_chosen';
		}

		// Custom class
		if ($this->strClass != '')
		{
			$strClass .= ' ' . $this->strClass;
		}

		$this->strClass = $strClass;

		return parent::parse($arrAttributes);
	}


	/**
	 * Generate the options
	 *
	 * @return array The options array
	 */
	protected function getOptions()
	{
		$arrOptions = array();
		$blnHasGroups = false;

		// Add empty option (XHTML) if there are none
		if (empty($this->arrOptions))
		{
			$this->arrOptions = array(array('value' => '', 'label' => '-'));
		}

		// Generate options
		foreach ($this->arrOptions as $arrOption)
		{
			if ($arrOption['group'])
			{
				if ($blnHasGroups)
				{
					$arrOptions[] = array
					(
						'type' => 'group_end'
					);
				}

				$arrOptions[] = array
				(
					'type'  => 'group_start',
					'label' => specialchars($arrOption['label'])
				);

				$blnHasGroups = true;
			}
			else
			{
				$arrOptions[] = array
				(
					'type'     => 'option',
					'value'    => $arrOption['value'],
					'selected' => $this->isSelected($arrOption),
					'label'    => $arrOption['label'],
				);
			}
		}

		if ($blnHasGroups)
		{
			$arrOptions[] = array
			(
				'type' => 'group_end'
			);
		}

		return $arrOptions;
	}


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string The widget markup
	 */
	public function generate()
	{
		$strOptions = '';
		$blnHasGroups = false;

		if ($this->multiple)
		{
			$this->strName .= '[]';
		}
		// Make sure there are no multiple options in single mode
		elseif (is_array($this->varValue))
		{
			$this->varValue = $this->varValue[0];
		}

		if ($this->chosen && strpos($this->class, 'tl_chosen') === false)
		{
			$this->class .= ' tl_chosen';
		}

		// Add empty option (XHTML) if there are none
		if (empty($this->arrOptions))
		{
			$this->arrOptions = array(array('value'=>'', 'label'=>'-'));
		}

		foreach ($this->arrOptions as $arrOption)
		{
			if ($arrOption['group'])
			{
				if ($blnHasGroups)
				{
					$strOptions .= '</optgroup>';
				}

				$strOptions .= sprintf('<optgroup label="%s">',
										specialchars($arrOption['label']));

				$blnHasGroups = true;
				continue;
			}

			$strOptions .= sprintf('<option value="%s"%s>%s</option>',
									$arrOption['value'],
									$this->isSelected($arrOption),
									$arrOption['label']);
		}

		if ($blnHasGroups)
		{
			$strOptions .= '</optgroup>';
		}

		return sprintf('<select name="%s" id="ctrl_%s" class="%s"%s>%s</select>',
						$this->strName,
						$this->strId,
						$this->class,
						$this->getAttributes(),
						$strOptions) . $this->addSubmit();
	}

	protected function isSelected($arrOption)
	{
		if (empty($this->varValue) && empty($_POST) && $arrOption['default'])
		{
			return static::optionSelected(1, 1);
		}

		return static::optionSelected(is_array($this->varValue) ? $arrOption['label'] : $arrOption['value'], $this->varValue);
	}
}
