<?php

/**
 * 
 * PUFF WIDGET
 * 
 */

class SpathonSinglePuffWidget extends WP_Widget {
	
	/**
	 * Start the plugin
	 * @return 
	 */
	function SpathonSinglePuffWidget() {
		$widget_ops = array('classname' => 'ps-single-puff', 'description' => __('Puff on selected place', 'ps_puffar_lang') );
		$this->WP_Widget('ps_single_puff', __('Single puff', 'ps_puffar_lang'), $widget_ops);
	}
	
	/**
	 * The Output of the plugin
	 * 
	 * 
	 * @return 
	 * @param object $args
	 * @param object $instance
	 */
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		
		// if no puff isset
		if(!isset($instance['puff_id'])) return;
		
		/*
		 * Check conditional tags
		 */
		
		$hide = true;
		if(isset($instance['puff_single_where'])){
			foreach ((array)$instance['puff_single_where'] as $value) {
				// if a condition is met set to false and exit
				if($value()) {
					$hide = false;
					break;
				}
			}
		}
		
		if($hide && isset($instance['puff_post_types_where'])){
			foreach( $instance['puff_post_types_where'] as $post_type ){
				if ( $post_type == get_post_type() ){
					$hide = false;
					break;
				}
			}
		}
		
		// Check taxonomies
		if($hide && isset($instance['puff_tax_where'])){
			if(in_array(get_query_var( 'taxonomy' ), (array) $instance['puff_tax_where'])){
				$hide = false;
			}
		}
		// if it didn't meet any ot the conditions return
		if($hide) return;
		
		global $post;
		$args = array(
			'post_type' => 'puffar',
			'posts_per_page' => -1,
			'p' => $instance['puff_id']
		);

		// The Query
		$the_query = new WP_Query( $args );

		// The Loop
		while ( $the_query->have_posts() ) : $the_query->the_post();

			$puff_meta = get_post_meta($post->ID, '_puff_meta', true);
			$puff_link = get_post_meta($post->ID, '_puff_link', true);
			
			// the puff template
			$template = (isset($puff_meta['template'])) ? $puff_meta['template'] : false; 

			// prevent same id
			$before_puff_widget = preg_replace('/id=\".+?\"/i', 'id="ps_puff_'. $post->ID .'"', $before_widget);
			echo preg_replace('/class=\"/i', 'class="puff-nr-'. $i .' ', $before_puff_widget, 1);

			if($template && file_exists(TEMPLATEPATH.'/'.$template)){
				include(TEMPLATEPATH.'/'.$template);
			// puff template
			}elseif(file_exists(TEMPLATEPATH.'/puff.php')){
				include(TEMPLATEPATH.'/puff.php');
			// default template
			}else{

				if(has_post_thumbnail()){ the_post_thumbnail(); }

				echo $before_title;
					if(!empty($puff_link)) echo '<a href="'. $puff_link .'" title="'. esc_attr(get_the_title($post->ID)) .'">';
						the_title();
					if(!empty($puff_link)) echo '</a>';
				echo $after_title;

				echo '<div class="puff-content">';
					the_content();
				echo '</div>';

				if(is_user_logged_in() && current_user_can('edit_post')){
					edit_post_link( __( 'Edit', 'ps_puffar_lang' ), '<span class="edit-puff-link">', '</span>', $post->ID );
				}

			}// end puff mall

			echo $after_widget;

		endwhile;

		// Reset Post Data
		wp_reset_postdata();

	}

	
	
	
	
	/**
	 * Uppdate the settings on save
	 * 
	 * @return 
	 * @param object $new_instance
	 * @param object $old_instance
	 */
	
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$instance['puff_single_where'] = $new_instance['puff_single_where'];
		$instance['puff_post_types_where'] = $new_instance['puff_post_types_where'];
		$instance['puff_tax_where'] = $new_instance['puff_tax_where'];
		$instance['puff_id'] = (int) $new_instance['puff_id'];
		return $instance;
	}
	

	
	
	
	/**
	 * The Widget settings
	 * 
	 * @return 
	 * @param object $instance
	 */
	function form($instance) {
		global $puff_conditional;
		// These are our default values
        $defaults = array( 'puff_id' => '', 'puff_single_where' => array(), 'puff_tax_where' => array() );
		
        // This overwrites any default values with saved values
        $instance = wp_parse_args( (array) $instance, $defaults );
        ?>
        <div class="ps-single-puff-widget">
        <p>
            <label for="<?php echo $this->get_field_id('puff_id'); ?>"><strong><?php _e('Select puff:', 'ps_puffar_lang'); ?></strong></label>
            <select id="<?php echo $this->get_field_id('puff_id'); ?>" class="widefat spathon-select-puff-id" name="<?php echo $this->get_field_name('puff_id'); ?>">
				<?php
				
				$args = array(
					'post_type' => 'puffar',
					'numberposts' => -1
				);
				
				$puffar = get_posts($args);
				
				foreach($puffar as $puff): ?>
					<option <?php selected($instance['puff_id'], $puff->ID); ?> value="<?php echo $puff->ID; ?>"><?php echo $puff->post_title; ?></option>
				<?php endforeach; ?>
			</select>
			<!--span class="description"><?php _e('Select puff', 'ps_puffar_lang'); ?></span-->
		</p>
		
		<p>
			<strong><?php _e('Where?', 'ps_puffar_lang'); ?></strong>
			<br style="clear: both" />
	        <?php
			foreach($puff_conditional as $value => $name): ?>
	        	<label style="width: 50%; float: left; " >
	        		<input type="checkbox" name="<?php echo $this->get_field_name('puff_single_where'); ?>[]" <?php 
	        			if(in_array($value, (array)$instance['puff_single_where'])) echo 'checked="checked"';
	        			?> value="<?php echo $value; ?>" />
	        		<?php echo $name; ?>
	        	</label>
			<?php endforeach; ?>
			<br style="clear: both" />
			<!--span class="description"><?php _e('Select where or none to show every where', 'ps_puffar_lang'); ?></span-->
		</p>
		
		<p>
			<strong><?php _e('Post types', 'ps_puffar_lang'); ?></strong>
			<br style="clear: both" />
			<?php
			$post_types = get_post_types( array('public' => true ), 'objects');
			foreach( $post_types as $post_type ): ?>
				<label style="width: 50%; float: left; " >
	        		<input type="checkbox" name="<?php echo $this->get_field_name('puff_post_types_where'); ?>[]" <?php 
	        			if(in_array($post_type->name, (array)$instance['puff_post_types_where'])) echo 'checked="checked"';
	        			?> value="<?php echo $post_type->name; ?>" />
	        		<?php echo $post_type->label; ?>
	        	</label>
	        <?php endforeach; ?>
			<br style="clear: both" />
		</p>
		
		<p>
			<strong><?php _e('taxonomies', 'ps_puffar_lang'); ?></strong>
			<br style="clear: both" />
			<?php
			$taxonomies = get_taxonomies( array('public' => true), 'object' );
			foreach( $taxonomies as $tax ): ?>
				<label style="width: 50%; float: left; " >
	        		<input type="checkbox" name="<?php echo $this->get_field_name('puff_tax_where'); ?>[]" <?php 
	        			if(in_array($tax->name, (array)$instance['puff_tax_where'])) echo 'checked="checked"';
	        			?> value="<?php echo $tax->name; ?>" />
	        		<?php echo $tax->label; ?>
	        	</label>
			<?php endforeach; ?>
			<br style="clear: both" />
		</p>
		
		</div>
		<?php
	}
}




add_action('widgets_init', create_function('', 'return register_widget("SpathonSinglePuffWidget");'));