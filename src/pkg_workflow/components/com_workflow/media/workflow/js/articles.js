/**
 * 
 */

var WFArticles = 
{
		/**
	     * @param    string    fi    The form ID (Optional)
	     * @param    string    fn    The form name (Optional)
	     */
		removeButtons: function(fi)
		{
			
	        if (typeof fi == 'undefined') {
	            fi = 'adminForm';
	        }
	       
	        var f = jQuery('#' + fi);
	        var l = f.attr('action');
		    var q = l.split('?');
		    var u = q[0];
		    //console.log('url to make a call for workflow is ' + u);
	        jQuery("#articleList > tbody", f).find('tr').each( 
	        	/* each row */	
	        	function (rIdx, row) {
	        		/* Find id of article */
	        		var id = jQuery('td:nth-child(2)', this).find(':checkbox').prop('value');
	        		var target = jQuery('td:nth-child(3)', this);
	        		/* Find state change buttons and disabled them */
	        		
	        		target.find('div > a')
	        			.prop('onclick', null)
	        			.addClass('disabled')
	        			.prop('title', 'This behavior was disabled by JWorkflow');
	        		
	        		WFArticles.loadWorkflowState(u, target, id);
	        	}
	        );
		},
		
		loadWorkflowState : function(baseUrl, target, item_id) 
		{

    		jQuery('ul.dropdown-menu', target).html('<li class="divider"></li>');
    		//console.log(jQuery('ul.dropdown-menu', target).html());
    		jQuery.ajax(
    		{
    			url: baseUrl + '?option=com_workflow',
    			data: 'task=instance.state&context=com_content.article&id=' + item_id + '&tmpl=component&format=json',
    			type: 'POST',
    			cache: false,
    			dataType: 'json',
    			success: function(resp)
    			{
    				if (resp.success == true) {
    					if (resp.data != 'undefined') {
    						var title = resp.data.title;
    						jQuery('ul.dropdown-menu', target).prepend('<li class="disabled"><a href="#"><span class="icon"> state: '+ title +'</span></a></li>');
    					}
    				}
    			},
    			error: function(resp, e, msg)
    			{
    				//Workflow.displayMsg(resp, msg);
    			},
    			complete: function()
    			{
    				
    			}
    		});
    		
    		jQuery.ajax(
	        {
	        	url: baseUrl + '?option=com_workflow',
	        	data: 'task=instance.transitions&context=com_content.article&id=' + item_id + '&tmpl=component&format=json',
	        	type: 'POST',
	        	cache: false,
	        	dataType: 'json',
	        	success: function(resp)
	        	{
	        		//console.log('query for transitions success for item id = '+id);
	        		//console.log(resp.data[0].title);
	        		if (resp.success == true) {
	        			if (resp.data != 'undefined') {
	        				var ix = 0;
	        				for (ix = 0; ix < resp.data.length; ix++) {
	        					//console.log(resp.data[ix].id);
	        					jQuery('ul.dropdown-menu', target).append('<li><a href="#" class="wf-transition" item_id="'+item_id+'" transition="'+resp.data[ix].id+'"><span class="icon"> '+ resp.data[ix].title +'</span></a></li>');
	        				}
	        				
	        				jQuery('a.wf-transition', target).click(function() {
	        					var item_id = jQuery(this).attr('item_id');
	        					var transition_id = jQuery(this).attr('transition');
	        					var link = baseUrl + '?option=com_workflow&tmpl=component&format=json';
	        		
	        					var tdata = 'transition_id='+transition_id+'&context=com_content.article';
	        					/*
	        					jQuery('#transition-dialog').modal({
	        						backdrop: 'static',
	        						keyboard: true,
	        						show: true
	        					});*/
	        					jQuery('input#transition-comment').val('');
	        					jQuery.blockUI({message: jQuery('#transition-dialog'),  css: { width: '375px', top: '100px' } });
	        					jQuery('button#transition-yes').click(function(){
	        						
	        						WFArticles.doTransition(link, tdata, target, item_id, jQuery('input#transition-comment').val());
	        					});
	        					
	        				});
	        			}
	        		}
	        	},
	        	
	        	error: function(resp, e, msg)
	        	{
	        		//Workflow.displayMsg(resp, msg);
	        	},
	        	complete: function()
	        	{
	        				
	        	}
	        });			
		},
		
		
		doTransition: function (transitionUrl, transitionData, target, item_id, comment)
		{
			console.log('Try to make transition for com_content.article.'+item_id+' with comment '+comment);
			jQuery.ajax(
			{
	        	url: transitionUrl+'&task=instance.transition',
	        	data: transitionData+'&item_id=' + item_id + '&comment=' + comment ,
				type: 'POST',
				async: false,
	        	cache: false,
	        	dataType: 'json',
	        	success: function(resp)
	        	{
	        		if (resp.success == true) {
	        			if (resp.data != 'undefined') {
	        				 jQuery(function(){
	        				        new PNotify({
	        				            title: resp.data.title,
	        				            text: resp.data.text,
	        				            type: 'success',
	        				            width: '450px'
	        				        });
	        				    });
	        				var u = transitionUrl.split('?');
	        				WFArticles.loadWorkflowState(u[0], target, item_id);
	        			}
	        		}
	        	},
	        	error: function(resp, e, msg)
	        	{
	        		//Workflow.displayMsg(resp, msg);
	        		jQuery.unblockUI();
	        	},
	        	complete: function()
	        	{
	        		jQuery.unblockUI();
	        	}
			});
		}
};