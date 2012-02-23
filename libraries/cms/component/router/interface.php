<?php
/**
 * @package		Joomla.Libraries
 * @subpackage	Component
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * 
 *
 * @package		Joomla.Libraries
 * @subpackage	Component
 * @since		3.0
 */
interface JComponentRouterInterface {
	/**
	 * Build method for URLs
	 * 
	 * @param array $query Array of query elements
	 * 
	 * @return array Array of URL segments
	 *
	 * @since		3.0
	 */
	function build(&$query);
	
	/**
	 * Parse method for URLs
	 * 
	 * @param array $segments Array of URL string-segments
	 * 
	 * @return array Associative array of query values
	 *
	 * @since		3.0
	 */
	function parse(&$segments);
}
