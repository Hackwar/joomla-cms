<?php
/**
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('checkboxes');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		3.0
 */
class JFormFieldSEFRules extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	3.0
	 */
	protected $type = 'SEF Rules';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	3.0
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		$rules = array();
		$app = JFactory::getApplication();
		$event = $app->triggerEvent('onRouterRules');
		foreach ($event as $ruleset)
		{
			$rules = array_merge($rules, (array) $ruleset);
		}
		foreach ($rules as $rule)
		{
			$options[] = JHtml::_('select.option', $rule, 'COM_CONFIG_FIELD_SEF_RULES_'.strtoupper($rule).'_LABEL', 'value', 'text');
		}

		reset($options);

		return $options;
	}
}
