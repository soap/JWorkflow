<?php
defined('_JEXEC') or die;

class WorkflowModelUser extends JModelLegacy
{

	/**
	 * Gets the list of users.
	 *
	 * @param array   $ids
	 * @param mixed   $search
	 * @param integer $limit
	 *
	 * @return array
	 */
	public function getUsers(array $ids, $search, $limit)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$users = array();

		// If some ids are specified, they must be treated in priority
		if (count($ids) > 0) {
			$query
			->select('id, name, username')
			->from('#__users')
			->where('id IN (' . implode(',', $ids) . ')');

			$db->setQuery($query);

			$users = $db->loadAssocList();
		}

		// Select the other users
		$query = $db->getQuery(true);

		$query
		->select('id, name, username')
		->from('#__users')
		->order('username');

		if (count($ids) > 0) {
			$query->where('id NOT IN (' . implode(',', $ids) . ')');
		}

		if (null !== $search) {
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(name LIKE ' . $search . ' OR username LIKE ' . $search . ')');
		}

		$db->setQuery($query, 0, 50);

		$users = array_merge($users, $db->loadAssocList());
		$allowedUsers = array();

		foreach ($users as $user) {
			if ($limit === count($allowedUsers)) {
				break;
			}

			/* if (JAccess::check($user['id'], 'core.login.admin')
					|| JAccess::check($user['id'], 'core.admin')) {
						$allowedUsers[] = $user;
					} */
			$allowedUsers[] = $user;
		}

		return $allowedUsers;
	}

	/**
	 * Gets the list of groups.
	 *
	 * @param array   $ids
	 * @param mixed   $search
	 * @param integer $limit
	 *
	 * @return array
	 */
	public function getGroups(array $ids, $search, $limit)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$groups = array();

		// If some ids are specified, they must be treated in priority
		if (count($ids) > 0) {
			$query
			->select('id, title')
			->from('#__usergroups')
			->where('id IN (' . implode(',', $ids) . ')');

			$db->setQuery($query);

			$groups = $db->loadAssocList();
		}

		// Select the other groups
		$query = $db->getQuery(true);

		$query
		->select('id, title')
		->from('#__usergroups')
		->order('title');

		if (count($ids) > 0) {
			$query->where('id NOT IN (' . implode(',', $ids) . ')');
		}

		if (null !== $search) {
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(title LIKE ' . $search . ')');
		}

		$db->setQuery($query, 0, 50);

		$groups = array_merge($groups, $db->loadAssocList());
		$allowedGroups = array();

		foreach ($groups as $group) {
			if ($limit === count($allowedGroups)) {
				break;
			}

			/*if (JAccess::checkGroup($group['id'], 'core.login.admin')
					|| JAccess::checkGroup($group['id'], 'core.admin')) {
						$allowedGroups[] = $group;
					} */
			$allowedGroups[] = $group;
		}

		return $allowedGroups;
	}
}
