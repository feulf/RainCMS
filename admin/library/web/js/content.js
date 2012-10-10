
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
