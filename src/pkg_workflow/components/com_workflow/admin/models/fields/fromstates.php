<?php
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('checkboxes');

/**
 * Form Field class for selecting a task.
 *
 */
class JFormFieldFromStates extends JFormFieldCheckboxes {

	
	protected $type = 'FromStates';

		/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions() {

        $options = array();
        $user    = JFactory::getUser();
        $db      = JFactory::getDbo();
        $query   = $db->getQuery(true);

        $exclude = $this->form->getValue('target_state_id');
        
        if ($this->form->getValue('workflow_id')) {
        	$this->workflow = $this->form->getValue('workflow_id');	
        }

        // Get field attributes for the database query
        $state = ($this->element['state']) ? (int) $this->element['state'] : NULL;

        // Build the query
        $query->select('a.id AS value, a.title as text')
              ->from('#__wf_states AS a')
              ->where('a.workflow_id = '.$this->workflow);

        // Implement View Level Access.
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by state
        if (!is_null($state)) $query->where('a.published = ' . $db->quote($state));

        $query->order('a.title');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        foreach($items AS $item)
        {
        	
            // Create a new option object based on the <option /> element.
            $opt = JHtml::_('select.option', (string) $item->value,
                JText::alt(trim((string) $item->text),
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text', 
                ((string)$exclude == (string)$item->value)
            );

            // Add the option object to the result set.
            $options[] = $opt;
        }

        reset($options);

        return $options;		
	}
	
}
