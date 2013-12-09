<!-- This file is used to markup the administration form of the widget. -->
<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />

		<label for="<?php echo $this->get_field_id( 'disqus_api_key' ); ?>"><?php _e( 'Disqus Api Key:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'disqus_api_key' ); ?>" name="<?php echo $this->get_field_name( 'disqus_api_key' ); ?>" type="text" value="<?php echo esc_attr( $disqus_api_key ); ?>" />		

		<label for="<?php echo $this->get_field_id( 'forum_key' ); ?>"><?php _e( 'Forum ID:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'forum_key' ); ?>" name="<?php echo $this->get_field_name( 'forum_key' ); ?>" type="text" value="<?php echo esc_attr( $forum_key ); ?>" />		

		<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Post Limit:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />						
</p>