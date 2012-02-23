<?php
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

 /* Newsfeeds Component Route Helper
 *
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
 * @since 1.6
 */

defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Newsfeed Router
 * 
 * 
 *
 */
class NewsfeedRouter extends JComponentRouter
{
	function __construct()
	{
		$this->register('categories', 'categories');
		$this->register('category', 'category', 'id', 'categories', '', true);
		$this->register('newsfeed', 'newsfeed', 'id', 'category', 'catid');
		parent::__construct();
	}
}