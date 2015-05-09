<?php
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

/**
 * Form Field class for selecting a task.
 *
 */
class JFormFieldTransition extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Transition';


    /**
     * Method to get the field list options markup.
     *
     * @return    array      $options      The list options markup.
     */
   
    public function getInput()
    {
        // Load the current project title a value is set.
        $title = ($this->value ? $this->getTransitionTitle() : JText::_('COM_WORKFLOW_SELECT_A_TRANSITION'));

        if ($this->value == 0) $this->value = '';

        $html = $this->getHtml($title);

        return implode("\n", $html);    	
    }
    
    protected function getHtml($title)
    {
        // Initialize some field attributes.
        $attr = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"'      : '';

        // Create a dummy text field with the project title.
        $html[] = '<div class="fltlft">';
        $html[] = '    <input type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '" disabled="disabled"' . $attr . ' />';
        $html[] = '</div>';   

       	// Create the hidden field, that stores the id.
        $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
        
        return $html;
        
    }
    
    protected function getTransitionTitle()
    {
        $default = JText::_('COM_WORKFLOW_SELECT_A_TRANSITION');

        if (empty($this->value)) {
            return $default;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select("CONCAT(w.title, ' -> ', a.title)")
              ->from('#__wf_transitions AS a')
              ->join('LEFT', '#__wf_workflows AS w ON w.id=a.workflow_id')
              ->where('a.id = ' . $db->quote($this->value));

        $db->setQuery((string) $query);
        $title = $db->loadResult();

        if (empty($title)) {
            return $default;
        }

        return $title;    		
    }
}
