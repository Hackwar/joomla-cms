<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\View\Indexer;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Indexer view class for Finder.
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var Form $form
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $form;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'debug')
		{
			$this->form = $this->get('Form');
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	/**
	 * Method to configure the toolbar for this view.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_FINDER_INDEXER_TOOLBAR_TITLE'), 'search-plus finder');

		$toolbar->back();

		$toolbar->standardButton('index', 'COM_FINDER_INDEX')
			->icon('icon-play')
			->onclick('Joomla.debugIndexing();');
	}
}
