

<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Puff Settings', 'ps_puffar_lang'); ?></h2>
	
	<form action="options.php" method="post">
		<?php 
		settings_fields('spathon_puff_settings');
		do_settings_sections('puff_settings');
		?>
		<p class="submit">
			<input type="submit" value="<?php _e('Save changes', 'ps_puffar_lang'); ?>" class="button-primary" id="submit" name="submit">
		</p>
	</form>

</div>