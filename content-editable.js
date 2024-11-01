;(function($){
	jQuery(document).ready(function($){
		initContentEditable();
		
		var dir = ContentEditableSettings.content_editable_url;
		var ajaxPath = '/wp-admin/admin-ajax.php';
		
		function initContentEditable(){					
			$('.contenteditable.furniture, .contenteditable.post_content').on('blur', updateContentEditable);
			$('.contenteditable.custom').blur(updateContentEditableCustom);
			$('.contenteditable.title').blur(updateContentEditableTitle);
			$('.contenteditable').each(function(){
				var parent = $(this).parent();
				var display = parent.css('display');
				//$(this).css('display', display);
			});

			$('#wpadminbar').on('click', '[href="#trigger-content-editable"]', triggerContentEditable);
			if (typeof ceAutoEditNoButton != 'undefined') {
				if (ceAutoEditNoButton){
					enableContentEditable();
				}
			}
		}	

		function triggerContentEditable(e){
			e.preventDefault();
			var $this = $(e.currentTarget);
			var $body = $('body');
			if ($body.hasClass('editing')){
				disableContentEditable();
			} else {
				enableContentEditable();
			}
		}

		function disableContentEditable(){
			$('body').removeClass('editing');
			var $parents = $('.contenteditable').closest('a');
			$parents.off('click', onEditedParentClick);
			$('.contenteditable').attr('contenteditable', 'false');
		}

		function enableContentEditable(){
			$('body').addClass('editing');
			var $parents = $('.contenteditable').closest('a');
			$parents.on('click', onEditedParentClick);
			$('.contenteditable').attr('contenteditable', 'true');
		}

		function onEditedParentClick(e){
			e.preventDefault();
		}
		
		function updateContentEditableTitle(e){
			e.preventDefault();
			var span = $(this);
			var data = new Object();
			data.pid = span.attr('data-pid');
			data.title = span.find('.saver').text();
			data.action = 'ce_update_title';
			data.security = ContentEditableSettings.nonce;
			$.post(ajaxPath, data, onContentSaved);
		}
		
		function updateContentEditable(){
			var span = $(this);
			var data = new Object();
			data.pid = span.attr('data-pid');
			data.content = span.find('.saver').html();
			data.content.replace('<p class="auto-p">', '');
			data.content.replace('</p>', "\n");
			data.action = 'ce_update_content';
			data.security = ContentEditableSettings.nonce;
			$.post(ajaxPath, data, onContentSaved);
		}
		
		function updateContentEditableCustom(){
			var span = $(this);
			var data = {};
			data.pid = span.attr('data-pid');
			data.key = span.attr('data-key');
			data.content = span.find('.saver').html();
			data.action = 'ce_update_custom';
			data.security = ContentEditableSettings.nonce;
			$.post(ajaxPath, data, onContentSaved);
		}
		
		function onContentSaved(data){
			//console.log(data);
		}
		
		
		
	});
})(jQuery);