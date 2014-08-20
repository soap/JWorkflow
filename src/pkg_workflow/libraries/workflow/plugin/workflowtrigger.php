<?php
defined( '_JEXEC' ) or die;
/**
 * Abstract class for workflow trigger (transition guard trigger and action trigger)
 * 
 */
class plgAbstractTrigger {
    protected 	$_namespace = 'core.workflowtriggers.abstractbase';
    protected 	$_type = 'abstract'; //guard, action
    protected	$_name = '';
    protected 	$_title = 'core';
    protected 	$_description = '';
    
    protected 	$_triggerInstance;
    protected 	$params = null;
    
    // generic requirements - both can be true
    
   	protected $_isConfigurable = true;

   	/**
   	 * 
   	 * We now supplies the guard config through the constructer
   	 * @param unknown_type $config
   	 */
    public function __construct($config = array()) {    	
        // Get the parameters.
		if (isset($config['params']))
		{
			if ($config['params'] instanceof JRegistry)
			{
				$this->params = $config['params'];
			}
			else
			{
				$this->params = new JRegistry;
				$this->params->loadString($config['params']);
			}
		}
		
    	// Get the plugin name.
		if (isset($config['title']))
		{
			$this->_title = $config['title'];
		}

        // Get the plugin name.
		if (isset($config['description']))
		{
			$this->_description = $config['description'];
		}
		// Get the plugin type.
		if (isset($config['type']))
		{
			$this->_type = $config['type'];
		}		
		
		if (empty($this->_name)) {
			$this->_name = str_replace('plg'.$this->_type, '', strtolower(get_class($this)));
		}
        
   		$this->loadLanguage();
    }
    
    // simple function to inform the UI/registration what kind of event this is
    public function getInfo() {
        return array(
            'type' => $this->_type,
            'title' => $this->_title,
            'description' => $this->_description,
        	'explain' => $this->getExplain()
        );
    }
    
    public function getName() { return htmlspecialchars($this->_name); }    
    public function getNamespace() { return $this->_namespace; }    
    public function getDescription() { return htmlspecialchars($this->_description); }  
    public function isConfigurable() { return $this->_isConfigurable; }
    
    /**
     * 
     * Enter description here ...
     */
    protected function isLoaded()
    {
    	return !is_null($this->params);
    }
    
    /**
     * 
     * Load configuration data for guard transition
     * @param unknown_type $params
     */
    public function loadConfig($params) 
    {
   		if ($params instanceof JRegistry)
		{
			$this->params = $params;
		}
		else
		{
			$registry = new JRegistry;
			$registry->loadString($params);
			$this->params = $registry;
		}    	
    }
    
    /**
     * 
     * Get the human readable configuration summary
     * @param none
     * @return string
     */
    public function getConfigSummary()
    {
    	return '';
    }
    
    /**
     * 
     * Get explaination text why the transition blocked
     * @return string
     */
    public function getExplain()
    {
    	return '';	
    }
    
    /**
     *  
     * return true for transition allowed on doc, false for transition not allowed on doc.
     */
    public function allowTransition($oDocument, $oUser) {
        return true;  // abstract base class
    }
    
    /**
     * 
     * perform more expensive checks -before- performTransition.
     * 
     */
    
    public function precheckTransition($oDocument, $oUser) {
        return true;
    }
    
    /*
    Multiple triggers can occur on a given transition.  If this trigger fails,
    return a JError::error (the overall system -will- roll the db back - 
    no need to do it yourself) with a -useful- human error message.
    
    Any other return is simply discarded.
     */
    public function afterTransition($oDocument, $oUser) {
        return true;
    }
    
    /** 
     * 
     * Check instance config if it required user input e.g. user for role.
     */
    public function isRequiredUserInput()
    {
        if (!$this->isConfigurable()) return false;
        return false;
    }
    
    /**
     * 
     * Load language for the guard plugin
     * @param unknown_type $extension
     */
   	protected function loadLanguage($extension = null) 
   	{
   		if (empty($extension)) 
   		{
   			$extension = 'plg_' . $this->_type . '_' . $this->_name;
   		}	
   		
   		$lang = JFactory::getLanguage();
   		if (!defined('WFPATH_PLUGINS')) {
   			$_path = JPATH_ADMINISTRATOR.'/components/com_workflow/plugins';
   			define('WFPATH_PLUGINS', $_path);
   		}else{
   			$_path = WFPATH_PLUGINS;
   		}
   		
		return $lang->load(strtolower($extension), $_path . '/' . $this->_type . '/' . $this->_name, null, false, false)
			|| $lang->load(strtolower($extension), $_path . '/' . $this->_type . '/' . $this->_name, $lang->getDefault(), false, false);
   	}
}