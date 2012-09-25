
	// Enable order content list
	function content_sortable( content_id ){
		$("#sortable").sortable({
			opacity: 0.5,
			placeholder: 'list_placeholder',
			scroll: true,
			update: function( event, ui ){
				var sortedList = $('#sortable').sortable("serialize");
				$.post( ajax_file + "/content/sort/"+content_id+"/", {sortable:sortedList}, function(h){ update_tree( content_id, content_id ) } )
			}
		});
	}

	// Delete content
	function content_delete( content_id, parent_id, msg, reindex ){
		if( confirm( msg ) ){
			$("#i_" + content_id ).slideUp()
			delete_tree( content_id )
			$.get( ajax_file + "/content/delete/"+content_id+"/")
		}
	}
	

	function content_deleteAndRefresh( content_id, parent_id, msg ){
		if( confirm( msg ) ){
			$("#i_" + content_id ).slideUp();
			//delete_tree( content_id )
			$.get( ajax_file + "/content/delete/"+content_id+"/", function(data){
				delete_tree( content_id )
				window.location = admin_file + '/content/edit/' + parent_id + '/'
			});
			
		}
	}
	
	function content_new( content_id, type_id, layout, msg ){
		if( title = prompt( msg ) ){
			$.getJSON( ajax_file, {module:'content', cp:'content/new', content_id:content_id, type_id:type_id, title:title, layout:layout}, function( result ){
				if( result['status'] == true )
					window.location = admin_file + 'content/edit/' + result['new_id']
				else
					alert( result['msg'] )
			})
		}
	}
	
	
	function content_copy( content_id, msg ){
		if( title = prompt( msg ) ){
			$.getJSON( ajax_file, {module:'content', cp:'content/copy', content_id:content_id, title:title }, function( result ){
				if( result['status'] == true )
					window.location = admin_file + 'content/edit/' + result['content_id']
				else
					alert( result['msg'] )
			})
		}
	}


	
	
	function showHideStats( content_id ){
		if( $('#content_stats').html() == '')
			$('#content_stats').hide().html('<iframe src="admin.server.php?module=content&cp=stats/show&id='+content_id+'"></iframe>' ).slideDown()
		else{
			$.get( ajax_file, {module:'content', cp:'stats/hide'} )
			$('#content_stats').slideUp("slow", function(){$('#content_stats').html('');} )
		}
		
	}


	//------------------------------------------
	// Tree functions
	//------------------------------------------

	// called only on Content tree
	if( $('#site_tree').html() ){
		$(function(){
			var tree_top_orig = $('#site_tree').offset().top
			var tree_top_new = tree_top_orig - $('#header').height()
			var win_height = $(window).height()

                        var tree_top = $('#site_tree').offset().top
                        var tree_height = $('#site_tree').height()

			$(window).scroll( function(){

				var win_scroll = $(window).scrollTop()

				if( win_scroll > tree_top_orig && (tree_top + tree_height < win_height) )
					$('#site_tree').css('position','fixed').css('top', tree_top_new )
				else
					$('#site_tree').css('position','static')

			})
		});
	}


	function update_tree( content_id, sel_id, update_time ){
		$.get( ajax_file + "/content/tree/update/", { content_id: content_id, sel_id:sel_id }, function(data){
			$('#tree_'+content_id).html( data )
			if( update_time )
				setTimeout( "update_tree(0,"+sel_id+","+update_time+");", update_time );
		});
	}

	function update_open_tree( content_id, sel_id ){
		$.get( ajax_file + "/content/tree/update/", { content_id: content_id, sel_id:sel_id }, function(data){
			$('#tree_'+content_id).html( data ).hide().slideDown("slow")
		});
	}

	function update_close_tree( content_id ){
		$.get( ajax_file + "/content/tree/close/", { content_id : content_id } );
		$('#tree_'+content_id).slideUp("fast", function(){ $( "#tree_"+content_id).html(''); })
	}

	function open_tree( img, content_id, sel_id ){
		var obj = $( '#tree_'+content_id )
		if( obj.html().length > 50 ){		// in a closed tree i can find the html <li style="display:none;"></li> for xhtml validation
			img.src= admin_views_images_url + "tree/closed.gif"
			update_close_tree( content_id )
		}
		else{
			img.src= admin_views_images_url + "tree/opened.gif"
			update_open_tree( content_id, sel_id )
		}
	}

	function delete_tree( content_id ){
		$( "#li_tree_"+content_id ).slideUp( "fast", function(){
			$( "#li_tree_"+content_id ).remove()
		} )
	}

	
	

	//------------------------------------------
	// File functions
	//------------------------------------------

	// active file sortable
	function file_sortable( content_id ){
		$("#sortable_file").sortable({
			opacity: 0.5,
			scroll: true,
			handle: '.thumb',
			update: function(){
				var sortedList = $('#sortable_file').sortable("serialize");
				$.post( ajax_file + "/content/file_sort/"+content_id+"/", {sortable:sortedList} )
			}
		});
	}
	
	// active the Upload button in file_list
	function file_enable_upload_btn( content_id, max_file_size ){
		new AjaxUpload('#upload_button', {
  			action: ajax_file + "/content/file_upload/" + content_id + "/" ,
  			name: 'file',
  			autoSubmit: false,
			responseType: 'json',
			onChange: function(file, extension){
				var upload_id = new Date().getTime()
				this.setData({upload_id: upload_id, MAX_FILE_SIZE: max_file_size });
				if( file_upload( file, extension, upload_id ) )
					this.submit()
			},
			onComplete: function( file, response ) {
				if( response['status'] )
					file_upload_complete( response['upload_id'], response['file_id'], file, response['thumb_src'], response['filename'], content_id );
				else
					file_upload_cancel( response['upload_id'], response['msg'], content_id );
			}
		});
	}


	// delete file
	function file_delete( file_id ){
		if( confirm( msg_file_delete ) ){
			$.get( ajax_file + '/content/file_delete/' + file_id );
			$("#f_"+file_id).slideUp();
		}
	}

	// edit file
	function file_edit( content_id, file_id ){
		$.get( ajax_file, {module: "content", cp: "file/edit", content_id:content_id, file_id: file_id}, function(result){
			
			html =  '<div id="edit_file">'
			html += '<div class="content">' + result + '</div>'
			html += '<div class="bg"></div>'
			html += '</div>'
			$('body').append( html );
			$('#edit_file').fadeIn()
			init_all()
		});
	}
	
	function file_edit_close(){
		$('#edit_file').fadeOut("slow", function(){$('#edit_file').remove()});
	}

	// file upload
	var upload_counter = 0
	function file_upload( file, extension, upload_id ){
		if( extension == '' || !in_array(allowed_ext.split(','), extension) )
			return alert( file + "\n" + msg_extension_not_allowed );
			
		upload_counter++
		$('#upload_list').html( $('#upload_list').html() + '<div class="upload_li_'+upload_id+'">'+file+' <img class="upload_img_'+upload_id+'" src="'+admin_views_images_url+'loading.gif" alt="...loading" width="16"></div>' ).fadeIn("fast", function(){
			$('.upload_li_'+upload_id).fadeIn()
		});
		return true
	}
	
	// error if the upload fail
	function file_upload_cancel( upload_id, msg, content_id ){
		upload_counter--
		$('.upload_img_'+upload_id).attr( 'src', admin_views_images_url + 'error-mini.gif' ).attr( 'alt', 'error' );
		alert( msg )
		$('.upload_li_'+upload_id).fadeOut("slow", function(){
																$('.upload_li_'+upload_id ).remove();
																if( upload_counter == 0 )
																	$('#upload_list').slideUp()
																});
		
	}


	// upload has been completed
	function file_upload_complete( upload_id, file_id, file, thumb_src, filename, content_id ){
		upload_counter--
		$('.upload_li_'+upload_id).fadeOut("fast", function(){
				$('.upload_li_'+upload_id ).remove();
				if( upload_counter == 0 )
					$('#upload_list').slideUp()
		});

		add_thumb( file_id, file, thumb_src, filename, content_id )
	}

	// add a thumbnail
	function add_thumb( file_id, file, thumb_src, filename, content_id ){
		var thumb_html  =   '<li class="thumbnail" id="f_'+file_id+'">'+"\n" +
							'	<div class="thumb_tools thumb_tools_'+file_id+'">' + "\n" +
					    	'		<a href="' + uploads_url + filename + '" rel="lightbox" title="'+file+'"><img src="'+admin_views_images_url+'preview.gif" title="preview" alt="preview" /></a>' + "\n" +
					    	'		<a href="javascript:file_edit('+file_id+')"><img src="'+admin_views_images_url+'edit.gif" title="edit" alt="edit"/></a>' + "\n" +
					    	'		<a href="javascript:file_delete('+file_id+');"><img src="'+admin_views_images_url+'del.gif" title="del" alt="del"/></a>' + "\n" + 
							'	</div>' + "\n" + 
							'	<img src="' + thumb_src + '" alt="'+file+'" class="thumb thumb_image tooltip" title="'+file+'"/>' + "\n" +		
					 		'	<div class="thumb_title" id="thumb_name_'+file_id+'">'+file+'</div>' + "\n" + 
					    	'</li>' + "\n";

		$('#sortable_file').html( $('#sortable_file').html() + thumb_html )
		$('#thumbnail_'+file_id).hide().fadeIn("slow")
		init_lightbox()
		//file_sortable(id)
	}



	// insert image in the content
	function file_insert_content( file_id, dir, filename ){
		img_html = '<img src="'+dir+filename+'" longdesc="$:'+file_id+'"/>';
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, img_html );
		tinyMCEPopup.close();
		window.close();
	}

	
	

	//------------------------------------------
	// Rss functions
	//------------------------------------------
	
	function add_rss( url, content_id, timestamp, i ){

		$('#row_'+i).fadeOut("slow");
		$.getJSON( ajax_file, {module:'content', cp:'content/addRss', timestamp:timestamp, rssurl:url, content_id:content_id }, function(result){
			if( result['result'] ){
				$('#row_'+i).html( '<img src="'+admin_views_images_url+'tick.gif">' );
				$('#row_'+i).fadeIn("slow");
			}
			else{
				alert( result['msg'])
			}
		});
		

	}
	

	
	
	//------------------------------------------
	// Block Sortable
	//------------------------------------------
	
	// Enable blocks drag&drop order in the pages
	function block_sortable( content_id, layout_id ){
		$( ".edit_mode_load_area_content").sortable({
			opacity: 0.5,
			connectWith: '.edit_mode_load_area_content',
			handle: '.edit_mode_block_header',
			scroll: true,
			tolerance: 'pointer',
			update: function(){
				var load_area = this.content_id.substr(6)
				var sortedList = $('#lname_' + load_area).sortable( 'serialize' )
				$.post( ajax_file, {module:'conf',cp:'block/sort',content_id: content_id, layout_id: layout_id, load_area: load_area, sortable:sortedList }, function( html ){
					//alert( html )
				})
			}
		});
	}
	
	function block_delete( block_id ){
		
		$('#block_'+block_id).slideUp( "slow", function(){
			$('#block'+block_id).remove()
		})
		$.get( ajax_file, { module: 'conf', cp: 'block/delete', block_id: block_id }, function( html ){
			//alert( html )
		})

	}
	
	function addBlock( load_area ){
		// mostra menu a tendina con scelta tra:
		// Immagine >> upload o carica dal sito
		// Testo >> deve apparire una finestra popup con il testo nella lingua selezionata
		// Modulo >> deve apparire una lista dei moduli disponibili da includere in quel punto
	}
