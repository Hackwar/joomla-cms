<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;

/**
 * Content Component Article Model
 *
 * @since  1.5
 */
class ArticleModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_content.article';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('article.id', $pk);

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = Factory::getUser();

		// If $pk is set then authorise on complete asset, else on component only
		$asset = empty($pk) ? 'com_content' : 'com_content.article.' . $pk;

		if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset)))
		{
			$this->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);
			$this->setState('filter.archived', ContentComponent::CONDITION_ARCHIVED);
		}

		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  object|boolean  Menu item data object on success, boolean false
	 */
	public function getItem($pk = null)
	{
		$user = Factory::getUser();

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('article.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select(
						$this->getState(
							'item.select', 'a.id, a.asset_id, a.title, a.alias, a.introtext, a.fulltext, ' .
							'a.state, a.catid, a.created, a.created_by, a.created_by_alias, ' .
							// Use created if modified is 0
							'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
							'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, ' .
							'a.images, a.urls, a.attribs, a.version, a.ordering, ' .
							'a.metakey, a.metadesc, a.access, a.hits, a.metadata, a.featured, a.language'
						)
					);
				$query->from('#__content AS a')
					->where('a.id = ' . (int) $pk);

				$query->select($db->quoteName('ws.condition'))
					->join(
						'INNER',
						$db->quoteName('#__workflow_associations', 'wa'),
						$db->quoteName('a.id') . ' = ' . $db->quoteName('wa.item_id')
					)
					->join(
						'INNER',
						$db->quoteName('#__workflow_stages', 'ws'),
						$db->quoteName('wa.stage_id') . ' = ' . $db->quoteName('ws.id')
					)
					->where($db->quoteName('wa.extension') . ' = ' . $db->quote('com_content'));

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access,' .
					'c.language AS category_language'
				)
					->innerJoin('#__categories AS c on c.id = a.catid')
					->where('c.published > 0');

				// Join on user table.
				$query->select('u.name AS author')
					->join('LEFT', '#__users AS u on u.id = a.created_by');

				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route,' .
					'parent.alias as parent_alias, parent.language as parent_language'
				)
					->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				// Join on voting table
				$query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count')
					->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

				if (!$user->authorise('core.edit.state', 'com_content.article.' . $pk)
					&& !$user->authorise('core.edit', 'com_content.article.' . $pk)
				)
				{
					// Filter by start and end dates.
					$nullDate = $db->quote($db->getNullDate());
					$date = Factory::getDate();

					$nowDate = $db->quote($date->toSql());

					$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
						->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
				}

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					$query->whereIn($db->quoteName('ws.condition'), [(int) $published, (int) $archived]);
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data))
				{
					throw new \Exception(Text::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.
				if ((is_numeric($published) || is_numeric($archived)) && ($data->condition != $published && $data->condition != $archived))
				{
					throw new \Exception(Text::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'), 404);
				}

				// Convert parameter fields to objects.
				$registry = new Registry($data->attribs);

				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$data->metadata = new Registry($data->metadata);

				// Technically guest could edit an article, but lets not check that to improve performance a little.
				if (!$user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_content.article.' . $data->id;

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->params->set('access-edit', true);
					}

					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->params->set('access-edit', true);
						}
					}
				}

				// Compute view access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = Factory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ($data->catid == 0 || $data->category_access === null)
					{
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else
					{
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (\Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go through the error handler to allow Redirect to work.
					throw new \Exception($e->getMessage(), 404);
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Increment the hit counter for the article.
	 *
	 * @param   integer  $pk  Optional primary key of the article to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		$input = Factory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('article.id');

			$table = Table::getInstance('Content', 'JTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}

	/**
	 * Save user vote on article
	 *
	 * @param   integer  $pk    Joomla Article Id
	 * @param   integer  $rate  Voting rate
	 *
	 * @return  boolean          Return true on success
	 */
	public function storeVote($pk = 0, $rate = 0)
	{
		if ($rate >= 1 && $rate <= 5 && $pk > 0)
		{
			$userIP = IpHelper::getIp();

			// Initialize variables.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__content_rating'))
				->where($db->quoteName('content_id') . ' = ' . (int) $pk);

			// Set the query and load the result.
			$db->setQuery($query);

			// Check for a database error.
			try
			{
				$rating = $db->loadObject();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// There are no ratings yet, so lets insert our rating
			if (!$rating)
			{
				$query = $db->getQuery(true);

				// Create the base insert statement.
				$query->insert($db->quoteName('#__content_rating'))
					->columns([$db->quoteName('content_id'), $db->quoteName('lastip'), $db->quoteName('rating_sum'), $db->quoteName('rating_count')])
					->values((int) $pk . ', ' . $db->quote($userIP) . ',' . (int) $rate . ', 1');

				// Set the query and execute the insert.
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
			else
			{
				if ($userIP != $rating->lastip)
				{
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__content_rating'))
						->set($db->quoteName('rating_count') . ' = rating_count + 1')
						->set($db->quoteName('rating_sum') . ' = rating_sum + ' . (int) $rate)
						->set($db->quoteName('lastip') . ' = ' . $db->quote($userIP))
						->where($db->quoteName('content_id') . ' = ' . (int) $pk);

					// Set the query and execute the update.
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (\RuntimeException $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

						return false;
					}
				}
				else
				{
					return false;
				}
			}

			return true;
		}

		Factory::getApplication()->enqueueMessage(Text::sprintf('COM_CONTENT_INVALID_RATING', $rate), 'error');

		return false;
	}
}
