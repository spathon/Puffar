(function($){
	
	/*
	 * Save the order of puffar
	 *
	function save_the_puff_order($sidebars){
		var all = [],// save all sidebars in an array
			i = 0; // increase the array
			
		$sidebars.each(function(){
			var $this = $(this),
				toArray = [$this.sortable('toArray')], // get the sidebars puffar
				area = [$this.parent().find('.ps-puff-area-title').html()];// the sidebar id (same as sidebars real id)
				
			// check if not empty
			if (toArray[0].length > 0) {
				// put in all puff's and the associated sidebar
				all[i] = area.concat(toArray);
				i++;
			}
			
		});
		
		
		
		var data = {
				action: 'ps_save_puff_order_ajax',
				order: all,
				id: $('#puffar').attr('data-id')
			};
		$.post(ajaxurl, data, function(response){
			//console.log(response);
		});
	}
	*/
	
	function save_the_puff_order($sidebars){
		$sidebars.each(function(){
			var $this = $(this),
				toArray = $this.sortable('toArray');
				
				//console.log(toArray);
				
				$this.parent().find('.ps-puff-input-order').val(toArray);
			
		});
	}
	
	
	
	
	
	
	
	/**
	 * 
	 * 
	 *  Document ready
	 * 
	 * 
	 */
	
	$(document).ready(function(){
		
		
		
		/**
		 * Edit.php?post_type=puffar
		 *
		 * Show all pages where the puff is shown (ct-column)
		 */
		$('.puff-more-pages').click(function(){
			$(this).next().next().toggleClass('puff-all-pages');
			return false;
		});
		
		
		
	
		
		// Check all pages
		$('.spathon-check-all').change(function(){
			
			var $this = $(this),
				$checkboxes = $this.closest('.spathon-where-post-type').find('.spathon-where-list-page:visible input');
			
			if($this.is(':checked')){
				$checkboxes.attr('checked', 'checked');
			}else{
				$checkboxes.removeAttr('checked');
			}
		});
		
		
		/**
		 * Pager on puff edit
		 *
		var $pagers = $('.spathon-where-pager');
		$pagers.each(function(){
			
			var $this = $(this),
				post_type = $this.attr('data-post-type'),
				$list_wrapper = $('#spathon_where_wrapper_'+ post_type);
				
			$this.find('a').click(function(){
				
				var $pagerA = $(this),
					offset = $pagerA.attr('data-id'),
					$page = $('#spathon_where_offset_'+ offset +'_type_'+ post_type);
				
				$list_wrapper.find('.spathon-where-list-page').hide();
				
				// if the page has been loaded show it else load it
				if($page.length > 0){
					
					$page.show();
					
				}else{
					
					var	data = {
							offset: offset,
							post_type: post_type,
							action: 'spathon_ajax_puff_where_pager',
							curr_puff_id: $('#post_ID').val()
						};
					
		
					$.ajax({
						type: "POST",
						url: ajaxurl,
						data: data,
						success: function(msg){
							$list_wrapper.append(msg);
						}
					});
				}
				
				
				$pagerA.addClass('disabled').siblings('a').removeClass('disabled');
				
				
				return false;
			});
		});
		*/
		
		
		
		
		
		
		/**
		 * Settings page
		 * 
		 * 
		 * add puff area and remove button area
		 */
		
		// add a new puff area
		$('#spathon_puffar_add_area').click(function(){
			$('.spathon-clone-area').clone(true).insertBefore($(this)).removeClass('spathon-clone-area');
			
			return false;
		});
		
		// remove area
		$('.spathon-remove-area').click(function(){
			$(this).closest('.spathon-puff-area-row').remove();
			
			return false;
		});
		
		
		
		
		/**
		 * Widgets page
		 */
		// if add custom is selected show an input
		$('.spathon-select-puff-area').live('change', function(){
			
			var $this = $(this),
				id = $this.attr('id'),
				$custom = $('#'+ id +'_custom'); // the input field
			
			// if the value is add custom area show the input else hide it
			if($this.val() == 'spathon_add_area'){
				$custom.show();
			}else{
				$custom.hide();
			}
		});
		
		
		
		
		/**
		 * Posts edit page
		 */
		$('.ps-puff-handle').live('click', function(){
			$(this).siblings('.ps-post-puff-content').not(':animated').slideToggle(300);
		});
		
		
		
		
		
		
		
		
		/**
		 * 
		 * SAVE PUFF ORDER
		 * 
		 */
		
		
		var $sidebars = $('.ps-puff-sort-area'),
			$curr_add_button,
			$puffar = $('#puffar');
		
		// Unbind click for h3 ( ajax update postbox position )
		$('#puffar, #addPuffBoxWrap').find('h3').unbind();
		
		
		$('.ps-add-puff-button').click(function(){
			var height = ($(window).height() - 90);
			tb_show('', '#TB_inline?height='+ height +'&width=640&inlineId=addPuffBox');
			
			$curr_add_button = $(this);
			
		});
		
		// create a new puff
		/*
		$('#create_a_new_puff_button').click(function() {
			$('#create_a_new_puff').toggleClass('show-create-new-puff-form');
			if($('#create_a_new_puff').hasClass('show-create-new-puff-form')){
				tinyMCE.execCommand("mceAddControl", false, "ps_add_new_puff_content");
			}
		});
		*/
		
		
		/**
		 * Make the puffar sortable
		 */
		$sidebars.each(function(){
			$(this).sortable({
				placeholder: 'puff-state-highlight',
				forcePlaceholderSize: true,
				handle: '.ps-post-puff-title',
				items: '.ps-post-puff',
				helper: 'clone',
				connectWith: '.ps-puff-sort-area',
				stop: function(event, ui){
					
					save_the_puff_order($sidebars);
					
				}
			});
		});
		
		
		/**
		 * Add puff
		 * 
		 * Click on the + adds the puff to the current area
		 */
		$('#addPuffBoxWrap').find('.ps-puff-handle-add').live('click', function(){
			var $theArea = $curr_add_button.parent().find('.ps-puff-sort-area');
			
			// add the puff to the puff area
			$theArea.append($(this).parent());
			
			// refresh the sortable to include the new puff
			$theArea.sortable("refresh");
			save_the_puff_order($sidebars);
		});
		
		$('#puffar').find('.ps-puff-handle-remove').live('click', function(){
			$('#addPuffBoxWrap').prepend($(this).parent());
			save_the_puff_order($sidebars);
		});
	
		
		
		
		
		
		
		
		/**************************************************
		 *
		 *   v 0.2
		 *
		 **************************************************/
		 
		/**
		 * Open a lightbox to select pages
		 *
		$('.puff-add-more-posts').click(function(){
			var post_type = $(this).attr('data-post_type');
			$('.puff-posts-list-'+ post_type).stop(true,true).slideToggle(400);
			return false;
		});
		*/
		
		var setItemsAsAlreadyAdded = function(){
			var $all_post_ids = $('#psPuff_all_post_ids');
			if($all_post_ids.length > 0){
				var all_post_ids = $all_post_ids.text().split(','),
					count_ids = all_post_ids.length,
					i;
				for(i = 0; i < count_ids; i++){
					$('.psPuff-add-post-to-puff[data-id="'+ all_post_ids[i] +'"]').addClass('psPuff-page-in-use');
				}
			}
		}
		setItemsAsAlreadyAdded();
		/**
		 * Pager
		 */
		var $pagers = $('.spathon-where-pager');
		$pagers.each(function(){
			var $this = $(this),
				post_type = $this.attr('data-post-type'),
				$list_wrapper = $('#puff_list_posts_wrapper_'+ post_type);
				
			$this.find('a').click(function(){
				
				var $pagerA = $(this),
					offset = $pagerA.attr('data-id'),
					$page = $('#spathon_where_offset_'+ offset +'_type_'+ post_type);
				
				$list_wrapper.find('.spathon-where-list-page').hide();
				
				// if the page has been loaded show it else load it
				if($page.length > 0){
					$page.show();
				}else{
					var	data = {
						offset: offset,
						post_type: post_type,
						action: 'spathon_ajax_puff_where_pager2',
						curr_puff_id: $('#post_ID').val()
					};
					$.ajax({
						type: "POST",
						url: ajaxurl,
						data: data,
						success: function(msg){
							$list_wrapper.prepend(msg);
							setItemsAsAlreadyAdded();
						}
					});
				}
				$pagerA.addClass('disabled').siblings('a').removeClass('disabled');
				return false;
			});
			
		});
		
		
		/**
		 * Add slide
		 */
		$(document.body).delegate('.psPuff-add-post-to-puff', 'click', function(){
			if($(this).hasClass('psPuff-page-in-use')) return;
			var	post_type = $(this).attr('data-post_type'),
				data = {
					action: 'spathon_ajax_add_page_to_puff',
					id: $(this).attr('data-id')
				};
			$(this).addClass('psPuff-page-in-use');
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(msg){
					$('#spathon_where_wrapper_'+ post_type ).append(msg);
				}
			});
		});
		
		$(document.body).delegate('.psPuff-remove-post', 'click', function(){
			var $this = $(this),
				id = $this.attr('data-id'),
				$all = $('#psPuff_all_post_ids'),
				all = $all.text();
			$this.closest('.spathon-where-single-post').remove();
			$('.psPuff-add-post-to-puff[data-id="'+ id +'"]').removeClass('psPuff-page-in-use');
			// remove from the array
			$all.text(all.replace(','+id, '').replace(id+',', '').replace(id, ''));
		});
		
		
	});
})(jQuery);






/*
		var action = 'ps_load_new_puffar_to_include',
			ids = '';
		$('.ps-post-puff').each(function(){
			ids += $(this).attr('data-id') +',';
		});
		
		tb_show('', ajaxurl + '?action='+ action +'&ids='+ ids);
		*/