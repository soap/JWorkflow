<?php
defined('_JEXEC') or die;

abstract class WFHtmlWorkflow
{
	/**
	 * Renders a filter input field for selecting a workflow
	 *
	 * @param     int       $value         The state value
	 * @param     bool      $can_change
	 *
	 * @return    string                   The input field html
	 */
	public static function filter($value = 0, $can_change = true)
	{
		$app = JFactory::getApplication();
	
		if (version_compare(JVERSION, '3.4', 'lt') && $app->isAdmin()) {
			//return self::modal($value, $can_change);
		}
	
		// For all other versions and locations show the typeahead
		// return self::typeahead($value, $can_change);
	
		// Show select2 dropdown
		return self::select2($value, $can_change);
	}

	protected static function select2($value, $can_change) 
	{
        JHtml::_('jquery.framework');
		JHtml::_('script', 'com_workflow/select2/select2.min.js', false, true, false, false, false);
    	JHtml::_('stylesheet', 'com_workflow/select2/select2.css', false, true);

        static $field_id = 0;

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        // Get currently active project data
        $active_id    = (int) WFApplicationHelper::getActiveWorkflowId();
        $active_title = WFApplicationHelper::getActiveWorkflowTitle();

        $field_id++;

        // Prepare title value
        $title_val = htmlspecialchars($active_title, ENT_COMPAT, 'UTF-8');

        // Prepare field attributes
        $attr_read = ($can_change ? '' : ' readonly="readonly"');
        $css_txt   = ($can_change ? '' : ' disabled muted') . ($active_id ? ' success' : ' warning');
        $placehold = htmlspecialchars(JText::_('COM_WORKFLOW_SELECT_WORKFLOW'), ENT_COMPAT, 'UTF-8');

        // Query url
        $url = 'index.php?option=com_workflow&view=workflows&tmpl=component&format=json&select2=1';

        // Prepare JS typeahead script
        $js = array();
        $js[] = "jQuery(document).ready(function()";
        $js[] = "{";
        $js[] = "    jQuery('#filter_workflow_id" . $field_id . "').select2({";
        $js[] = "        placeholder: '" . $placehold . "',";
        if ($active_id) $js[] = "        allowClear: true,";
        $js[] = "        minimumInputLength: 0,";
        $js[] = "        ajax: {";
        $js[] = "            url: '" . $url . "',";
        $js[] = "            dataType: 'json',";
        $js[] = "            quietMillis: 200,";
        $js[] = "            data: function (term, page) {return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};},";
        $js[] = "            results: function (data, page) {var more = (page * 10) < data.total;return {results: data.items, more: more};}";
        $js[] = "        },";
        $js[] = "        escapeMarkup:function(markup) { return markup; },";
        $js[] = "        initSelection: function(element, callback) {";
        $js[] = "           callback({id:" . $active_id . ", text: '" . ($active_id ? htmlspecialchars($active_title, ENT_QUOTES) : htmlspecialchars($placehold, ENT_QUOTES)) . "'});";
        $js[] = "        }";
        $js[] = "    });";
        $js[] = "    jQuery('#filter_workflow_id" . $field_id . "').change(function(){this.form.submit();});";
        $js[] = "});";

        // Prepare html output
        $html = array();

        $html[] = '<input type="hidden" id="filter_workflow_id' . $field_id . '" name="filter_workflow_id" placeholder="' . $placehold . '"';
        $html[] = ' value="' . $active_id . '" autocomplete="off"' . $attr_read . ' class="input-large" tabindex="-1" />';

        if ($can_change) {
            // Add script
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $js));
        }

        return implode("\n", $html);
	}
}