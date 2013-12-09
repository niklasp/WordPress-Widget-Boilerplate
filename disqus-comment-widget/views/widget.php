<!-- This file is used to markup the public-facing widget. -->
<?php
		if (! empty ($instance['title'])) {
			echo $before_title . $instance['title'] . $after_title;
		}

		if (! empty ($instance['disqus_api_key'])) {
			$widget_html = 
					'<div class="dsq-widget">
						<!-- Nav tabs -->
						<ul class="dq-nav dq-nav-tabs">
						  <li class="active"><a id="a-dq-recent" href="#dq-recent" data-toggle="tab">recent</a></li>
						  <li><a id="a-dq-trending" href="#dq-trending" data-toggle="tab">trending</a></li>
						</ul>

						<!-- Tab panes -->
						<div class="tab-content">
						  <div class="tab-pane active" id="dq-recent">
						  	<div class="text-center">
							<img class="loading" src="' . plugins_url( '../img/loading.gif' , __FILE__ ) .'" alt="Loading ..." width="30" height="30" />
							</div>
						  </div>
						  <div class="tab-pane" id="dq-trending">
						  <div class="text-center">
							<img class="loading" src="' . plugins_url( '../img/loading.gif' , __FILE__ ) .'" alt="Loading ..." width="30" height="30" />
							</div>
						  </div>
						</div>
					</div>';
			echo $widget_html;
		} else {
			echo __('Please enter your api Key in the widgets options', 'eedee');
		}		

?>
