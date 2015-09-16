<?php
defined('_JEXEC') or die;
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldAjaxUsers extends JFormFieldList 
{
	public $type = 'AjaxUsers';
	
	protected function getOptions() {
	
		$options = array();
		$user    = JFactory::getUser();
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
	
		// Get field attributes for the database query
		$state = ($this->element['block']) ? (int) $this->element['block'] : NULL;
	
		// Build the query
		$query->select('id, name, username')
			->from('#__users')
			->order('username');
		$ids = $this->value;
		if (count($ids) > 0) {
			$query->where('id IN (' . implode(',', $ids) . ')');
		}
	
		// Filter by state
		if (!is_null($state)) $query->where('block = ' . $db->quote($state));
	
		$query->order('name ASC');
	
		$db->setQuery((string) $query);
		$items = (array) $db->loadObjectList();

        foreach($items AS $item)
        {
            // Create a new option object based on the <option /> element.
            $opt = JHtml::_('select.option', (string) $item->id,
                JText::alt(trim((string) $item->name),
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );

            // Add the option object to the result set.
            $options[] = $opt;
        }
        
		reset($options);
	
		return $options;
	}	
}