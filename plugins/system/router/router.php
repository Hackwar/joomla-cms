<?php
/**
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Router Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.router
 */
class plgSystemRouter extends JPlugin
{
	/**
	 * Cached menu to improve performance
	 *
	 * @var JMenu
	 * @since 3.0
	 */
	static protected $menu = null;

	function onAfterInitialise()
	{
		$app = JFactory::getApplication();
		if ($app->isSite()) {
			self::$menu	= $app->getMenu();
			$router = $app->getRouter();
			$router->attachBuildRule(array('plgSystemRouter', 'processItemID'));

			if (in_array('force_ssl', $app->getCfg('sef_rules', array()))) {
				$router->attachParseRule(array('plgSystemRouter', 'forceSSL'));
			}
			$router->attachParseRule(array('plgSystemRouter', 'cleanupPath'));

			if (in_array('sef', $app->getCfg('sef_rules', array()))) {
				$router->attachBuildRule(array('plgSystemRouter', 'buildSEF'));
				$router->attachParseRule(array('plgSystemRouter', 'parseSEF'));
			}
			$router->attachParseRule(array('plgSystemRouter', 'parseRAW'));
		}
	}

	function onRouterRules()
	{
		$this->loadLanguage();
		return array('sef', 'force_ssl', 'sef_rewrite', 'sef_suffix', 'unicodeslugs');
	}

	function onComponentRouterRules($router = false)
	{
		if (!$router) {
			return array('joomla');
		}
		$cfg = JFactory::getApplication()->getCfg('sef_component_rules', array());
		if (in_array('joomla', $cfg)) {
			$router->attachBuildRule(array('plgSystemRouter', 'buildComponentSEF'));
			$router->attachParseRule(array('plgSystemRouter', 'parseComponentSEF'));
		}
	}

	/**
	 * Function to attach the correct ItemID to the URL
	 * and do some general processing
	 *
	 * @param JRouter calling JRouter object
	 * @param JURI URL to be processed
	 */
	public static function processItemID(JRouter $router, JURI $uri)
	{
		// Get the itemid form the URI
		$itemid = $uri->getVar('Itemid');

		if (!$itemid && !$uri->getVar('option') && is_null($uri->getPath())) {
			$uri->setQuery(array_merge($router->getVars(),$uri->getQuery(true)));
		}

		if (!$uri->getVar('option')) {
			if ($item = self::$menu->getItem($itemid)) {
					$uri->setVar('option', $item->component);
			}
		}
	}

	/**
	 * Function to build the SEF URL
	 *
	 * @param JRouter calling JRouter object
	 * @param JURI URL to be processed
	 */
	public static function buildSEF(JRouter $router, JURI $uri)
	{
		$query = $uri->getQuery(true);
		
		// Make sure any menu vars are used if no others are specified
		if (isset($query['Itemid']) && count($query) == 2) {
			// Get the active menu item
			$item = self::$menu->getItem($query['Itemid']);

			if ($item) {
				$query = array_merge($query, $item->query);
			}
		}

		$option = $query['option'];
		if (!$option) {
			return;
		}

		/*
		 * Build the component route
		 */
		$component	= preg_replace('/[^A-Z0-9_\.-]/i', '', $option);
		$tmp		= '';
		$comprouter	= $router->getComponentRouter($component);
		$parts		= $comprouter->build($query);

		$result = implode('/', $parts);
		if ($router->getOptions('sef_suffix', 0) && !(substr($result, -9) == 'index.php' || substr($result, -1) == '/')) {
			if ($format = $uri->getVar('format', 'html')) {
				$result .= '.'.$format;
				$uri->delVar('format');
			}
		}

		$tmp	= ($result != "") ? $result : '';

		/*
		 * Build the application route
		 */
		$built = false;
		if (isset($query['Itemid']) && !empty($query['Itemid'])) {
			$item = self::$menu->getItem($query['Itemid']);
			if (is_object($item) && $query['option'] == $item->component) {
				if (!$item->home || $item->language!='*') {
					$tmp = !empty($tmp) ? $item->route.'/'.$tmp : $item->route;
				}
				$built = true;
			}
		}

		if (!$built) {
			$tmp = 'component/'.substr($query['option'], 4).'/'.$tmp;
		}

		if (!$router->getOptions('sef_rewrite', 0)) {
			//Transform the route
			$result = 'index.php/'.$result;
		}
		//$route .= '/'.$tmp;

		// Unset unneeded query information
		if (isset($item) && $query['option'] == $item->component) {
			unset($query['Itemid']);
		}
		unset($query['option']);

		//Set query again in the URI
		$uri->setQuery($query);
		$uri->setPath($tmp);

		if ($limitstart = $uri->getVar('limitstart')) {
			$uri->setVar('start', (int) $limitstart);
			$uri->delVar('limitstart');
		}
	}

	public static function forceSSL(JRouter $router, JURI $uri)
	{
		if (strtolower($uri->getScheme()) != 'https') {
			//forward to https
			$uri->setScheme('https');
			JFactory::getApplication()->redirect((string)$uri);
		}
		return array();
	}

	public static function cleanupPath(JRouter $router, JURI $uri)
	{
		// Get the path
		$path = $uri->getPath();

		//Remove basepath
		$path = substr_replace($path, '', 0, strlen(JURI::base(true)));

		//Remove prefix
		$path = str_replace('index.php', '', $path);

		//Set the route
		$uri->setPath(trim($path , '/'));
	}

	public static function parseSEF(JRouter $router, JURI $uri)
	{
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$route	= $uri->getPath();

		// Get the variables from the uri
		$vars = $uri->getQuery(true);

		// Handle an empty URL (special case)
		if (empty($route)) {
			return true;
		}

		/*
		 * Parse the application route
		 */
		$segments	= explode('/', $route);
		if (count($segments) > 1 && $segments[0] == 'component') {
			$uri->setvar('option','com_'.$segments[1]);
			$uri->setvar('Itemid', null);
			$route = implode('/', array_slice($segments, 2));
		} else {
			//Need to reverse the array (highest sublevels first)
			$items = array_reverse($menu->getMenu());

			$found = false;
			$route_lowercase = JString::strtolower($route);

			foreach ($items as $item) {
				$length = strlen($item->route); //get the lenght of the route

				if ($length > 0 && strpos($route.'/', $item->route.'/') === 0 && $item->type != 'menulink') {
					$route = substr($route, $length);

					$uri->setVar('Itemid', $item->id);
					$uri->setVar('option',$item->component);
					$menu->setActive($item->id);
					$found = true;
					break;
				}
			}
			if (!$found)
			{
				$item = $menu->getDefault(JFactory::getLanguage()->getTag());
			}
			$vars['Itemid'] = $item->id;
			$vars['option'] = $item->component;
		}

		/*
		 * Parse the component route
		 */
		if (!empty($route) && $uri->getVar('option')) {
			$segments = explode('/', $route);
			if (empty($segments[0])) {
				array_shift($segments);
			}

			// Handle component	route
			$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $uri->getVar('option'));
			$comprouter = $router->getComponentRouter($component);

			$uri->setQuery(array_merge($uri->getQuery(true),$comprouter->parse($segments)));
		}

		$uri->setQuery(array_merge($uri->getQuery(true), $vars));

		if ($start = $uri->getVar('start')) {
			$uri->delVar('start');
			$uri->setVar('limitstart', $start);
		}

		// Get the path
		$path = $uri->getPath();

		//Remove the suffix
		/**
			if ($app->getCfg('sef_suffix') && !(substr($path, -9) == 'index.php' || substr($path, -1) == '/')) {
				if ($suffix = pathinfo($path, PATHINFO_EXTENSION)) {
					$path = str_replace('.'.$suffix, '', $path);
					$vars['format'] = $suffix;
				}
			}
		**/

		JRequest::set($uri->getQuery(true));

		return true;
	}

	public static function parseRAW(JRouter $router, JURI $uri)
	{
		$vars	= array();
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();

		//Handle an empty URL (special case)
		if (!$uri->getVar('Itemid')) {
			$item = $menu->getDefault(JFactory::getLanguage()->getTag());
			if (!is_object($item)) {
				// No default item set
				return true;
			}
			$uri->setVar('Itemid', $item->id);
		} else {
			$item = $menu->getItem($uri->getVar('Itemid'));
		}

		//Set the information in the request
		$uri->setQuery(array_merge($item->query, $uri->getQuery(true)));

		// Set the active menu item
		$menu->setActive($item->id);

		return true;
	}

    /**
     * Build the component part of a SEF URL
     *
     * @param JComponentRouter $crouter Component Router object
     * @param array $query query elements of the URL
     * @param array $segments path elements of the resulting URL
     * @return array path elements of the resulting URL
     */
	public static function buildComponentSEF(JComponentRouter $crouter, &$query, &$segments)
	{
        //Create the URL when no Itemid has been found
		if (!isset($query['Itemid'])) {
			$segments[] = $query['view'];
			$views = $crouter->getViews();
			if(isset($views[$query['view']]->id)) {
				$segments[] = $query[$views[$query['view']]->id];
				unset($query[$views[$query['view']]->id]);
			}
			unset($query['view']);
			unset($query['ts']);
			return;
		}

        //Get the menu item belonging to the Itemid that has been found
		$item = self::$menu->getItem($query['Itemid']);

        //Get all views for this component
        $views = $crouter->getViews();

        //Return directly when the URL of the Itemid is identical with the URL to build
		if (isset($item->query['view']) && $item->query['view'] == $query['view']) {
			$view = $views[$query['view']];
			if (isset($item->query[$view->id]) && $item->query[$view->id] == (int) $query[$view->id]) {
				unset($query[$view->id]);
				$view = $view->parent;
				while ($view)
				{
					unset($query[$view->child_id]);
					$view = $view->parent;
				}
				unset($query['view']);
				unset($query['ts']);
				unset($query['layout']);
				return array();
			}
		}

        //get the path from the view of the current URL and parse it to the menu item
		$path = array_reverse($crouter->getPath($query));
		$found = false;
		$found2 = true;
		for ($i = 0, $j = count($path); $i < $j; $i++)
		{
			reset($path);
			$view = key($path);
			if($found) {
				$ids = array_shift($path);//var_dump($ids, $views[$view]);
				if ($views[$view]->nestable) {
					foreach (array_reverse($ids) as $id)
					{
						if ($found2) {
							$segments[] = $id;
						} else {
							if($item->query[$views[$view]->id] == (int) $id) {
								$found2 = true;
							}
						}
					}
				} else {
					if (is_bool($ids)) {
						$segments[] = $views[$view]->title;
					} else {
						$segments[] = $ids[0];
					}
				}
			} else {
				if ($item->query['view'] != $view) {
					array_shift($path);
				} else {
					if (!$views[$view]->nestable) {
						array_shift($path);
					} else {
						$i--;
						$found2 = false;
					}
					$found = true;
				}
			}
			unset($query[$views[$view]->child_id]);
		}
		if (isset($query['layout']) && isset($views[$view]->layouts[$query['layout']])) {
			//$segments[] = $views[$view]->layouts[$query['layout']];
		}
		unset($query['layout']);
		unset($query['view']);
		unset($query['ts']);
		unset($query[$views[$view]->id]);
        foreach ($segments as &$segment)
        {
            $segment = str_replace(':', '-', $segment);
        }
		return $segments;
	}

    /**
     * Parse the component part of a SEF URL
     *
     * @param JComponentRouter $router Component router object
     * @param array $segments component path elements of the URL
     * @param array $vars associative array of unSEFed URL
     */
	public static function parseComponentSEF($router, $segments, $vars)
	{
		$views = $router->getViews();
		$menus = JFactory::getApplication()->getMenu();
		$active = $menus->getActive();
		$cview = $views[$active->query['view']]->children;

        $vars = array_merge($active->query, $vars);

		$nestable = false;
		foreach ($segments as $segment)
		{
			$found = false;

			@list($id, $alias) = explode('-', $segment, 2);
			for ($i = 0; $i < count($cview); $i++) 
			{
				$view = $cview[$i];
				if (isset($view->id) && (int) $id > 0)
				{
					$found = true;
					if ($view->nestable) {
						$item = call_user_func(array($router, 'get'.ucfirst($view->name)), $id);
						if($item->alias != $alias) {
							$found = false;
							$cview = array_merge($cview, $view->children);
							continue;
						}
						$nestable = true;
					}
					$vars['view'] = $view->name;
					if (isset($view->parent->id) && isset($vars[$view->parent->id])) {
						$vars[$view->parent_id] = $vars[$view->parent->id];
					}

                    if ($alias)
                    {
                        $vars[$view->id] = $id.':'.$alias;
                    } else {
                        $vars[$view->id] = $id;
                    }


				} elseif ($view->title == $segment) {
					$vars['view'] = $view->name;

					$found = true;
					break;
				}
			}
			if ($found) {
				if (!$nestable) {
					if (!isset($cview->children)) {
						break;
					}
					$cview = $cview->children;
				}
				$nestable = false;
			} else {
				break;
			}
		}
	}
}