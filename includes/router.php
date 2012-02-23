<?php
/**
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
	/**
	 * Component-router objects
	 * 
	 * @var array
	 */
	protected $componentRouters = array();

	/**
	 * Get component router
	 * 
	 * @param string $component Name of the component including com_ prefix
	 * 
	 * @return object The router of the component
	 */
	public function getComponentRouter($component)
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
			if(class_exists($name)) {
				$reflection = new ReflectionClass($name);
				if(in_array('JComponentRouterInterface', $reflection->getInterfaceNames())) {
					$this->componentRouters[$component] = new $name();
				}
			}
			if(!isset($this->componentRouters[$component])) {
				$this->componentRouters[$component] = new JDefaultRouter($compname);
			}
		}

		return $this->componentRouters[$component];
	}

	/**
	 * Set a router for a component
	 * 
	 * @param string $component Componentname with com_ prefix
	 * @param object $router Componentrouter
	 * 
	 * @return boolean True if the router was accepted, false if not
	 */
	public function setComponentRouter($component, $router)
	{
		$reflection = new ReflectionClass($router);
		if(in_array('JComponentRouterInterface', $reflection->getInterfaceNames())) {
			$this->componentRouters[$component] = $router;
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Defaultrouter for missing or legacy component routers
 *
 * @since 2.5
 */
class JDefaultRouter implements JComponentRouterInterface
{
	/**
	 * Name of the component
	 * 
	 * @var string
	 */
	protected $component;
	
	/**
	 * Constructor of JDefaultRouter
	 * 
	 * @param string $component Componentname without the com_ prefix this router should react upon
	 */
	function __construct($component)
	{
		$this->component = $component;
	}
	
	/**
	 * Generic build function for missing or legacy component router
	 * 
	 * @param array $query Query-elements of the URL
	 * 
	 * @return array Array of segments of the URL
	 */
	function build(&$query)
	{
		$function = $this->component.'BuildRoute';
		if(function_exists($function)) {
			$segments = $function($query);
			$total = count($segments);
			for ($i=0; $i<$total; $i++) {
				$segments[$i] = str_replace(':', '-', $segments[$i]);
			}
			return $segments;
		}
		return array();
	}

	/**
	 * Generic parse function for missing or legacy component router
	 * 
	 * @param array $segments Array of URL segments to parse
	 * 
	 * @return array Array of query elements
	 */
	function parse(&$segments)
	{
		$function = $this->component.'ParseRoute';
		if(function_exists($function)) {
			$total = count($segments);
			for ($i=0; $i<$total; $i++)  {
				$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
			}

			return $function($segments);
		}
		return array();
	}
}
