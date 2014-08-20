<?php
defined('_JEXEC') or die();

abstract class JHtmlWorkflow {
	
   /**
     * Build a list of workflow options
     *
     * @return    array                  The options array
     */
    public static function workflowOptions($exclude = null)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('a.id AS value, a.title AS text')
              ->from('#__wf_workflows AS a');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        if (is_numeric($exclude)) {
            $query->where('a.id != ' . $db->quote((int) $exclude));
        }

        if (is_array($exclude)) {
            JArrayHelper::toInteger($exclude);

            $query->where('a.id NOT IN(' . implode(', ', $exclude) . ')');
        }

        $query->order('a.title');

        $db->setQuery($query);

        $list    = (array) $db->loadObjectList();
        $options = array();

        foreach($list AS $item)
        {
            $options[] = JHtml::_('select.option',
                (int) $item->value, htmlspecialchars($item->text, ENT_COMPAT, 'UTF-8')
            );
        }

        return $options;
    }

    public static function triggerOptions()
    {

        $list    = array();
        
        $obj = new stdClass();
        $obj->value = '1';
        $obj->text	= 'Guard';
        
        $list[] = $obj;
        
        $obj = new stdClass();
        $obj->value = '2';
        $obj->text	= 'Action';
                
        $list[] = $obj;
        
        $options = array();

        foreach($list AS $item)
        {
            $options[] = JHtml::_('select.option',
                (int) $item->value, htmlspecialchars($item->text, ENT_COMPAT, 'UTF-8')
            );
        }

        return $options;
    }    
}