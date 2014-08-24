<?php
/**
 * @version 1.1.8 Dated: 2014-02-27
 */
// no direct access
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class plgGuardRequireditems extends plgAbstractTrigger 
{
	protected $_type = 'guard';
   	protected $_namespace = 'Workflow.Transition.Guard.Requireditems';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }

    /**
     * Validate if the transition is blocked 
     */ 
    public function allowTransition($oDocument, $oUser) 
    {
        if (!$this->isLoaded()) {
            return true;
        }
        
        $context = trim($this->params->get('context'));
    	if (!empty($context) && isset($oDocument->context)) {
    		if ($oDocument->context != $context) {
    			return true;
    		}
    	}
        
        $detailTable = $this->params->get('detail_table');
        $foreignKey = $this->params->get('foreign_key');
    
        $table = $this->validateTableName($detailTable);
        if ($table===false) return false;
        	
        // Count if the user was assigned for this item
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('count(*)')
                ->from($table)
        	->where($foreignKey.' = '.(int)$oDocument->id);
        		
        $db->setQuery($query);
        $countItems = $db->loadResult();
        		
        return ( $countItems > 0);
        
    }
    
    public function getConfigSummary()
    {
        $detailTable = $this->params->get('detail_table');
    	return JText::sprintf('PLG_GUARD_REQUIREDITEMS_GUARD_ITEM_MUST_HAVE_DETAIL', $detailTable );
    }
    
    public function getExplain() 
    {
        $detailTable = $this->params->get('detail_table');
    	return JText::_('PLG_GUARD_REQUIREDITEMS_GUARD_ITEM_MUST_HAVE_DETAIL', $detailTable  );
    }
    
    private function validateTableName($tableName)
    {
    	$prefixPos = strpos( $tableName, '#__' ); 
       
        //use of ===.  Simply == would not work as expected
    	if ($prefixPos === false){
            $tableName = '#__'.$tableName;      
        }else{
            if ( $prefixPos != 0 )  return false;
          
          //if ($prefixPos == 0) $tableName = $tableName; //do nothing
        }
        
        return $tableName;
 
    }
 }


