	//------------------------------------------
	// Lang
	//------------------------------------------

	// Enable order content list
	function lang_sortable( content_id ){

		$("#sortable").sortable({
			opacity: 0.5,
			placeholder: 'list_placeholder',
			scroll: true,
			update: function(){
				var sortedList = $('#sortable').sortable("serialize");
				$.post( ajax_file + "/Configure/languages/", {cp:'sort',sortable:sortedList}, function(h){
				//	alert(h);
				})
			}
		});
	}

	function lang_compile(){
		$.get( ajax_file + "/Configure/languages/", {cp:"languages/compile"}, function( html ){
			alert( html );
		});
	}

	//------------------------------------------
	// Theme
	//------------------------------------------

	function theme_set( theme_id ){
		$.get( ajax_file + "configure/set_theme/" + theme_id, function(h){
			$('.thumbnail>a>img').attr('class','thumb_image')
			$('#t_'+theme_id+'>a>img').attr('class','thumb_image selected')
		});
	}
	
	function theme_load(){
		$.get( ajax_file + "configure/load_themes/", function( html ){
			//window.location= admin_file + '/configure/themes/';
		});
	}

	function theme_preview( theme_id, url ){
		$.get( ajax_file + "configure/languages/", {cp:"themes/preview", theme_id: theme_id}, function( html ){
			window.location = url;
		});
	}
