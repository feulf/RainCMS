
	$(function() {
	    init_all();
	});

	// after ajax init
	function init_all( ){
		init_tooltip()
		init_lightbox()
	}

	function init_tooltip(){
		$( '.tooltip' ).tooltip({
					 track: true,
					 showURL: false,
					 fixPNG: true,
					 showBody: " - ",
					 top: 8,
					 left: 4,
                                         fade: 300,
                                         delay: 400
		});
					
	}

	function init_lightbox(){
		$('a[rel*=lightbox]').lightBox();
	}

	// taglia i testi ed aggiunge i ...
	function cut( string, cat_at, cat_string ){
		cat_string = cat_string ? cat_string : '...'
		return ( string.length > cat_at ) ? string.substr( 0, cat_at ) + cat_string : string;
	}

	// in array
	function in_array(arr, p_val) {
		for(var i = 0, l = arr.length; i < l; i++) {
			if(arr[i] == p_val) {
				return true;
			}
		}
		return false;
	}

	function init_TinyMce(){

			$(".tiny_mce_advanced").tinymce({
				script_url : "adm/inc/raincp/plugins/tiny_mce/tiny_mce.js",
				theme: "advanced",
				language: lang_id,
				mode: "textarea",
				force_br_newlines: true,
				tab_focus: ":prev,:next",
				convert_fonts_to_spans: false,
				width: "100%",
				height: 300,
				onchange_callback: function(editor){
					tinyMCE.triggerSave();
					$("#" + editor.id).valid();
				},
				plugins: "safari,fullscreen,searchreplace,media,paste,autosave,inlinepopups,print,pagebreak,rain",
				theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,fontsizeselect,|,forecolor,backcolor,|,fullscreen,pagebreak",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,replace,|,bullist,numlist,|,undo,redo,|,link,unlink,rain,image,media,code,|,hr,removeformat,|,charmap",
				theme_advanced_buttons3 : "",
				theme_advanced_toolbar_location: "top",
				theme_advanced_toolbar_align: "left",
				theme_advanced_path_location: "bottom",
				valid_elements: "*[*]",
				pagebreak_separator : "<!-- page break -->"
				
			})

		$(".tiny_mce_simple").tinymce({
			script_url : "adm/inc/raincp/plugins/tiny_mce/tiny_mce.js",
			theme: "advanced",
			language: lang_id,
			mode: "textarea",
			force_br_newlines: true,
			tab_focus: ":prev,:next",
			convert_fonts_to_spans: false,
			width: "100%",
			height: 200,
			onchange_callback: function(editor) {
				tinyMCE.triggerSave();
				$("#" + editor.id).valid();
			},
			theme_advanced_buttons1 : "bold,italic,underline, strikethrough, separator,justifyleft, justifycenter,justifyright,  justifyfull",
		    theme_advanced_buttons2: "",
		    theme_advanced_buttons3: "",
		    theme_advanced_buttons4: "",
		    theme_advanced_toolbar_location : "top",
		    theme_advanced_toolbar_align : "left",
		    theme_advanced_resize_vertical : true,
			valid_elements: "*[*]"
			
		})
		
	}
	
	
	function selectAllCheckBox( form_name ){
		var form = document.forms[form_name]
		for( var i=0; i<form.length; i++)
			if( form[i].type == 'checkbox' )
				form[i].checked = true
	}
	
	function deselectAllCheckBox( form_name ){
		var form = document.forms[form_name]
		for( var i=0; i < form.length; i++)
			if( form[i].type == 'checkbox' )
				form[i].checked = false
	}
	
	function admin_toolbox( content_id, link, url, edit_mode, _control_panel_msg_, _edit_page_msg_ ){
		toolbox()
		$('#toolbox').append( '<a href="'+admin_file+'" class="tooltip" title="'+_control_panel_msg_+'">'+_control_panel_msg_+'</a>' );
		$('#toolbox').append( '<a href="'+admin_file+'/content/'+content_id+'" class="tooltip" title="'+_edit_page_msg_+'">'+_edit_page_msg_+'</a>' );
		$('#toolbox').append( '<a href="javascript:enableDisableEditMode('+edit_mode+', \''+url+link+'\');" class="tooltip" title="Enable/disable edit mode">Edit Mode '+(edit_mode==1?'Off':'On')+'</a>' );
		$('#toolbox').append( '<a href="javascript:toolbox_position();" class="tooltip" title="Enable/disable edit mode">Top/Bottom</a>' );
	}
	
	function toolbox(){
		if( !$("#toolbox")[0] )
			$("body").append( '<div id="toolbox"></div>' )
	}
	
	function enableDisableEditMode( edit_mode, link ){
		$.get( "admin.server.php", {module:"conf", cp:"layout/edit_mode", edit_mode: edit_mode?0:1, link: link }, function(html){
			window.location = link
		})
	}
	
	function toolbox_position(){
		if( $("#toolbox").css('top') == "0px" ){
			$("#toolbox").css("bottom", "0px" )
			$("#toolbox").css("top", null )
		}
		else{
			$("#toolbox").css("top", "0px" )
			$("#toolbox").css("bottom", null )
		}
	}

	function dump( v ){
		html = ''
		for( var i in v )
			html += i + ' => ' + v[i] + "\n"
		alert( html )
	}