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
		
		/**
		 * 
		 * @param baseUrl
		 * @param item_id
		 */
		updateButton: function(baseUrl, item_id)
		{
    		jQuery.ajax(
    		{
    			url: baseUrl + '?option=com_workflow',
    			data: 'task=content.state&context=com_content.article&id=' + item_id + '&tmpl=component&format=json',
    			type: 'POST',
    			cache: false,
    			dataType: 'json',
    			success: function(resp)
    			{
    				if (resp.success == true) {
    			        var f = jQuery('#adminForm');
    					if (resp.data != 'undefined') {
    						var state = resp.data.state;
    						var featured = resp.data.featured;
    						
    				        jQuery("#articleList > tbody", f).find('tr').each( 
    					       /* each row */	
    					       function (rIdx, row) {
    					        	/* Find id of article */
    					        	var id = jQuery('td:nth-child(2)', this).find(':checkbox').prop('value');
    					        	var elementId = jQuery('td:nth-child(2)', this).find(':checkbox').prop('id');
    					        	var target = jQuery('td:nth-child(3)', this);
    					        	/* Find state change buttons and update them them */
    					        	if (id == item_id) {	
    					        		
    					        		if (state == '1') {
    					        			var iconClass = 'icon-publish';
    					        			var task = 'articles.unpublish';
    					        		}else if (state == '0') {
    					        			var iconClass = 'icon-ubpublish';
    					        			var task = 'articles.publish';
    					        		}else if (state == '2') {
    					        			var iconClass = 'icon-archive';
    					        			var task = 'articles.unpublish'
    					        		}else if (state == '-2') {
    					        			var iconClass = 'icon-trash';
    					        			var task = 'articles.unpublish'
    					        		}
    					        		
    					        		target.find('div > a')
    					        			.prop('onclick', "return listItemTask('"+elementId+"', '"+task+"')");
    					        		}

    					        		target.find('div > a > li')
    					        			.attr('class', iconClass);	
    					       		}
    					        );
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
	        	url: baseUrl + '?option=com_workflow&task=instance.transitions&',
	        	data: 'context=com_content.article&id=' + item_id + '&tmpl=component&format=json',
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
	        				var reason = '';
	        				var liClass = '';
	        				var tooltip = '';
	        				for (ix = 0; ix < resp.data.length; ix++) {
	        					//console.log(resp.data[ix].id);
	        					if (resp.data[ix].blocked == true) {
	        						reason = resp.data[ix].explain;
	        						liClass = ' class="disabled"';
	        						tooltips =  ' data-toggle="tooltip" data-placement="top" title="'+reason+'"'; 
	        					}else{
	        						reason = '';
	        						liClass = '';
	        						tooltips = '';
	        					}
	        					jQuery('ul.dropdown-menu', target).append('<li' + liClass +'><a href="#" class="wf-transition" item_id="'+item_id+'" transition="'+resp.data[ix].id+'" blocked="'+reason+'"><span class="icon"' +tooltips + '> '+ resp.data[ix].title +'</span></a></li>');
	        				}
	        				
	        				jQuery('a.wf-transition', target).click(function() {
	        					var blocked = jQuery(this).attr('blocked');
	        					if ( blocked !=='') {
	        						jQuery(function(){
		        				        new PNotify({
		        				            title: 'This transition is not allowed for you',
		        				            text: blocked, 
		        				            type: 'success',
		        				            width: '450px'
		        				        });
		        				    });
	        						return false;
	        					}
	        					var item_id = jQuery(this).attr('item_id');
	        					var transition_id = jQuery(this).attr('transition');
	        					var link = baseUrl + '?option=com_workflow&tmpl=component&format=json';
	        		
	        					var tdata = 'transition_id='+transition_id+'&context=com_content.article';

	        					jQuery('#transition-comment').val('');
	        					jQuery.blockUI({message: jQuery('#transition-dialog'),  css: { width: '475px', top: '100px' } });
	        					jQuery('button#transition-yes').click(function(){
	        						WFArticles.doTransition(link, tdata, target, item_id, jQuery('#transition-comment').val());
	        						return false;
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
			jQuery('button#transition-yes').prop('diabled', true);
			//console.log('Try to make transition for com_content.article.'+item_id+' with comment '+comment);
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
	        				WFArticles.updateButton(u[0], item_id);
	        			}
	        		}
	        	},
	        	error: function(resp, e, msg)
	        	{
	        		//Workflow.displayMsg(resp, msg);
	        		jQuery('button#transition-yes').prop('disabled', false);
	        		jQuery.unblockUI();
	        	},
	        	complete: function()
	        	{
	        		jQuery('button#transition-yes').prop('disabled', false);
	        		jQuery.unblockUI();
	        	}
			});
		}
};