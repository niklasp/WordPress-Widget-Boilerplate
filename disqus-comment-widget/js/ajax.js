jQuery(document).ready(function($) {

	get_dq_results(1, jQuery('#dq-recent'));

	jQuery('#a-dq-popular').click(function() {
		get_dq_results(2, jQuery('#dq-popular'));	
	});

});

/**
 * gets results from php and prints on display
 * @param  {int} type 1 for recent, 2 for popular
 * @param  {jQuery element} display the jQuery element where to display
 */
function get_dq_results( type, display ) {

	var data = {
		action: 'handle_disqus_post',
		security: ajax_object.ajax_nonce,	
		type: type //1 for recent comments, 2 equals popular comments, 3 for 
	};

		jQuery.ajax({
	        type: 'POST',
	        url: ajax_object.ajax_url,
	        data: data,
	        success: function(response, textStatus, XMLHttpRequest) {
	        	var comment_html = "";
	            jQuery.each(response.data, function(index, comment) {
	            	comment_html += '<div class="dsq-widget-comment">';
	            	comment_html += '<img class="dsq-user-avatar" src="' + comment.author.avatar.cache +'" />';
	            	comment_html += '<p class="dsq-comment-author"><a class="dq-comment-link" data-thread-id="' + comment.thread + '" data-comment-id="' + comment.id + '">' + comment.author.name + '</a></p>';
	            	comment_html += '<p class="dsq-comment-content">' + comment.message + '</p>';
	            	comment_html += '</div>';
	            });
	            display.html(comment_html);

				jQuery('.dq-comment-link').click(function(e) {
					e.preventDefault();
					get_comment_link(jQuery(this).data('comment-id'),jQuery(this).data('thread-id'));
				});	            

	        },
	        error: function(MLHttpRequest, textStatus, errorThrown) {
	            display.html('<p class="text-center">error fetching comments</p>')
	        }
	    });
}	

function get_comment_link (comment_id, thread_id) {
	
	var data = {
		action: 'handle_disqus_post',
		security: ajax_object.ajax_nonce,	
		type: 3,
		comment_id: comment_id,
		thread_id: thread_id
	};

	jQuery.ajax({
	    type: 'POST',
	    url: ajax_object.ajax_url,
	    data: data,
	    success: function(response, textStatus, XMLHttpRequest) {
	    	window.location = response.data;
	    },
	    error: function(MLHttpRequest, textStatus, errorThrown) {
	        console.log("error getting post link");
	    }
	});

}