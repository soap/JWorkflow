<?php
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for selecting a task.
 *
 */
class JFormFieldWorkflowState extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'WorfklowState';

    protected $workflow;

    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
    protected function getOptions()
    {
        $options = array();
        $user    = JFactory::getUser();
        $db      = JFactory::getDbo();
        $query   = $db->getQuery(true);

        if ($this->form->getValue('workflow_id')) {
        	$this->workflow = $this->form->getValue('workflow_id');	
        }else{
        	$this->workflow = $this->form->getValue('id');
        }

		if (empty($this->workflow)) return array();
		 
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

        $query->order('a.title ASC');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Generate the options
        if (count($items) > 0) {
            $options[] = JHtml::_('select.option', '',
                JText::alt('COM_WORKFLOW_OPTION_SELECT_STATE',
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );
        }

        foreach($items AS $item)
        {
            // Create a new option object based on the <option /> element.
            $opt = JHtml::_('select.option', (string) $item->value,
                JText::alt(trim((string) $item->text),
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