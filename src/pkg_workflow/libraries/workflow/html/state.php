<?php
defined('_JEXEC') or die;

/**
 * Utility class for categories
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
abstract class JHtmlState
{
	/**
	 * Cached array of the states items.
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected static $items = array();

	/**
	 * Returns an array of states for the given workflow
	 *
	 * @param   string  $workflow_id  The workflow id e.g. 1.
	 * @param   array   $config     An array of configuration options. By default, only
	 *                              published and unpublished states are returned.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public static function options($workflow_id, $config = array('filter.published' => array(0, 1)))
	{
		$hash = md5($workflow_id . '.' . serialize($config));

		if (!isset(self::$items[$hash]))
		{
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id, a.title');
			$query->from('#__wf_states AS a');
			$query->where('a.workflow_id = '.(int)$workflow_id);

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					JArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			$query->order('a.ordering');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			self::$items[$hash] = array();

			foreach ($items as &$item)
			{
				self::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
			}
		}

		return self::$items[$hash];
	}

	/**
	 * Returns an array of states for the given workflow id.
	 *
	 * @param   string  $workflow_id  The workflow id.
	 * @param   array   $config     An array of configuration options. By default, only published and unpublished categories are returned.
	 *
	 * @return  array   states for the workflow id
	 *
	 * @since   11.1
	 */
	public static function states($workflow_id, $config = array('filter.published' => array(0, 1)))
	{
		$hash = md5($workflow_id . '.' . serialize($config));

		if (!isset(self::$items[$hash]))
		{
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id, a.title');
			$query->from('#__wf_state AS a');
			$query->where('a.workflow_id = '.(int)$workflow_id);


			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					JArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			$query->order('a.ordering');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			self::$items[$hash] = array();

			foreach ($items as &$item)
			{
				self::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
			}
			// Special "Add to root" option:
			self::$items[$hash][] = JHtml::_('select.option', '0', JText::_('COM_WORKFLOW_SELECT_WORKFLOW_STATE'));
		}

		return self::$items[$hash];
	}
}