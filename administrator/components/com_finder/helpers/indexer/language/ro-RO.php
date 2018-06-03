<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Romanian language support class for the Finder indexer package.
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderIndexerLanguagero_RO extends FinderIndexerLanguage
{
	/**
	 * The romanian stemmer object.
	 *
	 * @var    \Wamania\Snowball\Romanian
	 * @since  __DEPLOY_VERSION__
	 */
	protected $stemmer = null;

	/**
	 * Method to construct the language object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->stemmer = new \Wamania\Snowball\Romanian;
	}

	/**
	 * Method to stem a token.
	 *
	 * @param   string  $token  The token to stem.
	 *
	 * @return  string  The stemmed token.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stem($word)
	{
		return $this->stemmer->stem($word);
	}
}
