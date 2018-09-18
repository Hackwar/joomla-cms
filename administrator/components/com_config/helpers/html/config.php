<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Mailtags HTML helper class.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlConfig
{
	/**
	 * Display a clickable list of tags for a mail template
	 *
	 * @param   string  $mail       Identifier of the mail template.
	 * @param   string  $fieldname  Name of the target field.
	 *
	 * @return  string  List of tags that can be inserted into a field.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function mailtags($mail, $fieldname)
	{
		$app = Factory::getApplication();
		Factory::getApplication()->triggerEvent('onMailBeforeTagsRendering', array($mail->mail_id, &$mail));

		if (!isset($mail->params['tags']) || !count($mail->params['tags']))
		{
			return '';
		}

		$html = '<ul class="list-group">';

		foreach ($mail->params['tags'] as $tag)
		{
			$html .= '<li class="list-group-item">'
				. '<a href="#" onclick="Joomla.editors.instances[\'jform_' . $fieldname . '\'].replaceSelection(\'{' . strtoupper($tag) . '}\');'
					. 'return false;" title="' . $tag . '">' . $tag . '</a>'
				. '</li>';
		}

		$html .= '</ul>';

		return $html;
	}
}
