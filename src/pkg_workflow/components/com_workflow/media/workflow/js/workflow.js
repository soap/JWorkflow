
var Workflow =
{
    isJsonString: function(str)
    {
        if (typeof str == 'undefined') return false;
        if (str == null) return false;
        
        var l = str.length;
        var e = l - 1;

        if (l == 0) return false;
        if (str[0] != '{' && str[0] != '[') return false;
        if (str[e] != '}' && str[e] != ']') return false;

        return true;
    },


    /**
    * Method to display the ajax response messages
    *
    * @param    object    resp    The ajax response object
    * @param    string    err     The error message
    */
    displayMsg: function(resp, err)
    {
        var mc = jQuery('#workflow-message-container');

        if (typeof mc == 'undefined') {
        	mc = jQuery('#system-message-container');
        	
        	if (typeof mc == 'undefined') return false;
        }

        if (resp.length != 0 && typeof resp.success != 'undefined') {
            if (typeof resp.messages != 'undefined') {
                var c = (resp.success == true) ? 'success' : 'error';
                var l = resp.messages.length;
                var x = 0;

                if (l > 0) {
                    for (x = 0; x < l; x++)
                    {
                        mc.append('<div class="alert alert-' + c + '"><a class="close" data-dismiss="alert" href="#">×</a>' + resp.messages[x] + '</div>');
                    }
                }
            }
        }
        else {
            var m = (typeof err != 'undefined' && err.length > 0) ? err : 'Request failed!';

            mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + m + '</div>');
        }
    },


    displayException: function(msg)
    {
        var mc = jQuery('#workflow-message-container');
        
        if (typeof mc == 'undefined') {
        	mc = jQuery('#system-message-container');
        	
        	if (typeof mc == 'undefined') return false;
        }

        (mc.length == 0) ? alert(msg) : mc.append(msg);
    }
}