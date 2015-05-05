var WFtransition = 
{
	/**
	 * @fi form id
	 * @fn forrm name
	 * @id transition id
	 * @task task
	 */
	validate: function(/*fi, fn,*/ id, task) 
	{
	    var f = jQuery('#item-form');
	    if (f.length == 0) {
	    	var f = jQuery('#adminForm');
	    }
	    var u = f.attr('action');
	    console.log(f.length);
	    var q = u.split('?'); 
	    var comment = jQuery('#comment', f);
	    
	    var d = {'transition_id': id, 'comment' : comment.val()};
		// Do the ajax request
        jQuery.ajax(
        {
            url: q[0] + '?option=com_workflow&task=transition.validate&tmpl=component&format=json',
            data: jQuery.param(d),
            type: 'POST',
            async: false,
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                if (Workflow.isJsonString(resp) == false) {
                    Workflow.displayException(resp);
                }
                else {
                    resp = jQuery.parseJSON(resp);
                    if ( resp.success == true || resp.success === 'true' ) {
                    	WFtransition.execute(task, id, comment);
                    }else{
                        Workflow.displayMsg(resp); 		                     
                    }
                }
            },
            error: function(resp, e, msg)
            {
                Workflow.displayMsg(resp, msg);
            },
            beforeSend: function() 
            {
                var mc = jQuery('#ajax-loading-container');
                	
                if (typeof mc == 'undefined') return true;
                
                if (mc.length > 0) mc.show();
            	return true;
            },
            complete: function()
            {
                var mc = jQuery('#ajax-loading-container');
                	
                if (typeof mc == 'undefined') return true;
                
                if (mc.length > 0) mc.hide();
                
            	return true;            	
            }
        });		
	},
	
	execute: function (task, id, comment)
	{
		document.adminForm.transition_id.value = id;
		Joomla.submitbutton(task); 
	}
	
};	