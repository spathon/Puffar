<?php

/**
 * 
 * PUFF WIDGET
 * 
 */

class SpathonPuffWidget extends WP_Widget {
	
	/**
	 * Start the plugin
	 * @return 
	 */
	function SpathonPuffWidget() {
		$widget_ops = array('classname' => 'ps-puff', 'description' => __('Puffar shown on selected places', 'ps_puffar_lang') );
		$this->WP_Widget('ps_puff', __('Puffar', 'ps_puffar_lang'), $widget_ops);
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
		
		
		if(is_singular()):
			
			global $post, $wpdb;
			
			$where = (isset($instance['puff_area'])) ? $instance['puff_area'] : 'Primary area';
			
			
			$table_name = PUFFAR_TABLE_NAME;
			$puffar = $wpdb->get_results( $wpdb->prepare( "SELECT puff_id FROM $table_name 
					WHERE post_id = %d
					AND puff_where = %s
					ORDER BY puff_order
					", 
					$post->ID, $where ) );
			$puff_ids = array();
			
			foreach($puffar as $puff){
				$puff_ids[] = $puff->puff_id;
			}
			
			
			if(!empty($puff_ids)):
				$args = array(
					'post_type' => 'puffar',
					'posts_per_page' => -1,
					'post__in' => $puff_ids
				);

				// The Query
				$the_query = new WP_Query( $args );

				// The Loop
				while ( $the_query->have_posts() ) : $the_query->the_post();

					$puff_meta = get_post_meta($post->ID, '_puff_meta', true);

					// the puff template
					$template = (isset($puff_meta['template'])) ? $puff_meta['template'] : false; 

					echo $before_widget;

					if($template && file_exists(TEMPLATEPATH.'/'.$template)){
						include(TEMPLATEPATH.'/'.$template);
					// puff template
					}elseif(file_exists(TEMPLATEPATH.'/puff.php')){
						include(TEMPLATEPATH.'/puff.php');
					// default template
					}else{

						if(has_post_thumbnail()){ the_post_thumbnail(); }

						echo $before_title;
								the_title();
						echo $after_title;

						echo '<div class="puff-content">';
							the_content();
						echo '</div>';

						if(is_user_logged_in() && current_user_can('edit_post')){
							echo '<a class="edit-puff-link" href="'.get_edit_post_link($post->ID).'">Edit</a>';
						}

					}// end puff mall

					echo $after_widget;

				endwhile;

				// Reset Post Data
				wp_reset_postdata();
				
			endif; // if no results were found
		else:
			
			//echo 'I\'m not single';
		
		endif;
		
		
		
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
		
		// escape the area
		$puff_area = esc_attr($new_instance['puff_area']);
		// if add custom is set
		if($puff_area == 'spathon_add_area'){
			$custom_area = esc_attr($new_instance['puff_area_custom']);
			
			if(!empty($custom_area)){
				$options = get_option('puff_settings');
				$options['area'][] = ps_puff_validate_area($custom_area);
				// save the custom area in the puff_settings
				update_option('puff_settings', $options);
				// save the puff area in the widget setting
				$puff_area = $custom_area;
			}else{
				$puff_area = '';
			}
			
		}
		
		$instance['puff_area'] = $puff_area;
		
		return $instance;
	}
	

	
	
	
	/**
	 * The Widget settings
	 * 
	 * @return 
	 * @param object $instance
	 */
	function form($instance) {
		// These are our default values
        $defaults = array( 'puff_area' => '');
		
        // This overwrites any default values with saved values
        $instance = wp_parse_args( (array) $instance, $defaults );
		
		$options = get_option('puff_settings');
		
		// get the get the puff widgets setings
		$puff_widgets = get_option('widget_ps_puff');
		
		/*  NEED FIX
		$areas_set = array();
		foreach( (array) $puff_widgets as $puff_widget ){
			if(isset($puff_widget['puff_area'])) $areas_set[] = $puff_widget['puff_area'];
		}
		*/
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('puff_area'); ?>"><?php _e('Puff area:', 'ps_puffar_lang'); ?></label>
            <select id="<?php echo $this->get_field_id('puff_area'); ?>"class="widefat spathon-select-puff-area" name="<?php echo $this->get_field_name('puff_area'); ?>">
				
				<?php if( isset($options['area']) && !empty($options['area']) ): foreach($options['area'] as $area): ?>
					<option <?php selected($instance['puff_area'], $area);
						//if(in_array($area, $areas_set) && $instance['puff_area'] != $area) echo ' disabled="disabled"';
						?>><?php echo $area; ?></option>
				<?php endforeach; endif; ?>
				<option value="spathon_add_area"><?php _e('(Add puff area)', 'ps_puffar_lang'); ?></option>
			</select>
			<span class="description"><?php _e('Select an area', 'ps_puffar_lang'); ?></span>
		</p>
		<p id="<?php echo $this->get_field_id('puff_area_custom'); ?>" style="display: none;">
			<label for="<?php echo $this->get_field_id('puff_area_custom'); ?>"><?php _e('Custom puff area:', 'ps_puffar_lang'); ?></label>
            <input class="widefat spathon-select-puff-area" name="<?php echo $this->get_field_name('puff_area_custom'); ?>" ?>
			<span class="description"><?php _e('Create a new puff area', 'ps_puffar_lang'); ?></span>
		</p>
        <?php
	}
}




add_action('widgets_init', create_function('', 'return register_widget("SpathonPuffWidget");'));