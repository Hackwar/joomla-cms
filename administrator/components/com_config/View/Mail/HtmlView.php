<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\View\Mail;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a mail.
 *
 * @since  DEPLOY_VERSION
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var  Joomla\CMS\Form\Form
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->master = $this->get('Master');
		$this->form = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		list($component, $mail_id) = explode('.', $this->item->mail_id, 2);
		$fields = array('subject', 'body', 'htmlbody');
		$this->templateData = array();
		$language = Factory::getLanguage();
		$language->load($component, JPATH_ADMINISTRATOR, $this->item->language, true);

		foreach ($fields as $field)
		{
			$this->templateData[$field] = (object) ['master' => $this->master->$field, 'translated' => Text::_($this->master->$field)];

			if (is_null($this->item->$field)
				|| $this->item->$field == ''
				|| $this->item->$field == $this->master->$field)
			{
				$this->item->$field = $this->master->$field;
				$this->form->setFieldAttribute($field, 'disabled', 'true');
				$this->form->setValue($field, null, $this->master->$field);
			}
			else
			{
				$this->form->setValue($field . '_switcher', null, 1);
			}
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * 
	 * @since   DEPLOY_VERSION
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title(
			Text::_('COM_CONFIG_PAGE_EDIT_MAIL'),
			'pencil-2 article-add'
		);

		$saveGroup = $toolbar->dropdownButton('save-group');

		$saveGroup->configure(
			function (Toolbar $childBar)
			{
				$childBar->apply('mail.apply');
				$childBar->save('mail.save');
			}
		);

		$toolbar->cancel('mail.cancel', 'JTOOLBAR_CLOSE');

		$toolbar->divider();
		$toolbar->help('JHELP_CONFIG_MAIL_MANAGER_EDIT');
	}
}
