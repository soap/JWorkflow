<?php
defined('_JEXEC') or die;

abstract class WFPluginHelper 
{
	/**
	 * A persistent cache of the loaded plugins.
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected static $plugins = null;

	/**
	 * Get the plugin data of a specific type if no specific plugin is specified
	 * otherwise only the specific plugin data is returned.
	 *
	 * @param   string  $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 *
	 * @return  mixed  An array of plugin data objects, or a plugin data object.
	 *
	 * @since   11.1
	 */
	public static function getPlugin($type, $plugin = null)
	{
		$result = array();
		$plugins = self::_load();

		// Find the correct plugin(s) to return.
		if (!$plugin)
		{
			foreach ($plugins as $p)
			{
				// Is this the right plugin?
				if ($p->type == $type)
				{
					$result[] = $p;
				}
			}
		}
		else
		{
			foreach ($plugins as $p)
			{
				// Is this plugin in the right group?
				if ($p->type == $type && $p->name == $plugin)
				{
					$result = $p;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Loads the published plugins.
	 *
	 * @return  array  An array of published plugins
	 *
	 * @since   11.1
	 */
	protected static function _load()
	{
		if (self::$plugins !== null)
		{
			return self::$plugins;
		}

		$user = JFactory::getUser();
		$cache = JFactory::getCache('com_plugins', '');

		$levels = implode(',', $user->getAuthorisedViewLevels());

		if (!self::$plugins = $cache->get($levels))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('name, group, namespace, alias')
				->from('#__wf_triggers')
				->where('access IN (' . $levels . ')')
				->order('ordering');

			self::$plugins = $db->setQuery($query)->loadObjectList();

			if ($error = $db->getErrorMsg())
			{
				JError::raiseWarning(500, $error);
				return false;
			}

			$cache->store(self::$plugins, $levels);
		}

		return self::$plugins;
	}	
}