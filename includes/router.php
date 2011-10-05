<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Class to create and parse routes for the site application
 *
 * @package		Joomla.Site
 * @subpackage	Application
 * @since		1.5
 */
class JRouterSite extends JRouter
{
	protected $componentRouters = array();

	protected $routerMethod;

	protected $routerClass;

	public function getComponentRouter($component, $functionName = 'build')
	{
		if(!isset($this->componentRouters[$component])) {
			$compname = ucfirst(substr($component, 4));
			if(!class_exists($compname.'Router')) {
				// Use the component routing handler if it exists
				$path = JPATH_SITE.'/components/'.$component.'/router.php';

				// Use the custom routing handler if it exists
				if (file_exists($path)) {
					require_once $path;
				}
			}
			$name = $compname.'Router';
			if(class_exists($name) && is_subclass_of($name, 'JComponentRouter')) {
				// Component uses a routing class
				$this->componentRouters[$component] = new $name();
			} elseif (function_exists($compname.'BuildRoute') && function_exists($compname.'ParseRoute')) {
				// Component uses routing functions
				$this->componentRouters[$component] = $compname;
			} else {
				// Component doesn't have a routing handler
				$this->componentRouters[$component] = 'JDefault';
			}
		}

		// Return routing handler
		if(is_string($this->componentRouters[$component])) {
			// Legacy or default handler
			return $this->componentRouters[$component].$functionName.'Route';
		}
		return array($this->componentRouters[$component], $functionName);
	}

	public function setComponentRouter($component, $router)
	{
		$this->componentRouters[$component] = $router;
	}
}

function JDefaultBuildRoute(&$query)
{
	return array();
}

function JDefaultParseRoute($segments)
{
	return array();
}