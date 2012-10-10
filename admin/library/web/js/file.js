	

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
	function file_edit( file_id ){
            
                if( !$('#edit_file').html() ){
                    var html =  '<div id="edit_file">'
                    html += '<div class="content"><button style="float:right" onclick="$(\'#edit_file\').fadeOut(\'fast\');">Close</button><div class="content_inside">Loading</div></div>'
                    html += '<div class="bg"></div>'
                    html += '</div>'
                    $('body').append( html );
                }
                $('#edit_file').fadeIn("fast");


		$.get( ajax_file + '/content/file_edit/'+file_id, function(result){
			$('#edit_file .content_inside').hide().html(result).fadeIn();
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