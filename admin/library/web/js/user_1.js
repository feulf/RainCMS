// ADDRESS BOOK

	$(document).ready( function(){
		
		if( window.location.hash.substr(1,5) == 'user=' ){
			var user_id = window.location.hash.substr(6)
			openAccount( user_id )
		}
		else if( window.location.hash.substr(1,6) == 'guest=' ){
			var user_where_is_id = window.location.hash.substr(7)
			openAccount( 0, user_where_is_id )
		}
		else if( window.location.hash.substr(1,6) == 'group=' ){
			var group_id = window.location.hash.substr(7)
			openGroup( group_id )
		}
		
		resizeAddressBook(1)
	})

	var open_group_id, open_user_id;
	function resizeAddressBook( reload ){
		$('#account_list_inner').css( "height", $(window).height() - ( $("#header").height()+75 ) )
		$('#address_book').css( "height", $(window).height() - ( $("#header").height()+70 ) )
		if(reload)
			setTimeout("resizeAddressBook(1)",5000)
	}

	function user_delete( msg ){
		if( confirm(msg) ){
			var nchecked = $('#account_ul>li>input:checked').length
			var selected_checkbox = ""
			  $("input[class=account_checkbox]:checked").each(function() {
			    selected_checkbox += selected_checkbox == "" ? $(this).attr('value') : ","+$(this).attr('value')
				$('#a_'+ $(this).attr('class') ).remove()
			  });

			$('#group_detail').html('');
			$('#account_detail').html('');
			$('#account_list_inner>ul>li').attr('class', '');
			$.post( ajax_file + "/user/account/del/",{user_id: selected_checkbox}, function( data ){
				if( open_group_id )
					openGroup( open_group_id )
				else
					openGroup(0)
			})

		}
	}
	
	function open_group_list( sel_group_id, user_id, add_to_msg, remove_from_msg ){

		if( $('#button_group_list').css('display') == 'none'){
			$.getJSON( ajax_file + "/user/account/group", {user_id:user_id}, function(json){
				
				if( json['group_list']){
					$('#button_group_list').html( '<span><b>'+add_to_msg+'</b></span>' )
					for( group_id in json['group_list'] )
						$('#button_group_list').append( '<div onclick="addUserToGroup('+json['group_list'][group_id]['group_id']+', \''+user_id+'\');">' + json['group_list'][group_id]['name'] + '</div>' )
				}

				if( json['in_group_list'] ){
					$('#button_group_list').append( '<span><b>'+remove_from_msg+'</b></span>' )
					for( group_id in json['in_group_list'] )
						$('#button_group_list').append( '<div onclick="delUserFromGroup(\''+sel_group_id+'\', '+json['in_group_list'][group_id]['group_id']+', \''+user_id+'\');">' + json['in_group_list'][group_id]['name'] + '</div>' )
				}
				
				$('#button_group_list').fadeIn()	
				
				init_tooltip()
			});
			
			
		}
		else
			$('#button_group_list').fadeOut()
	}

	function addUserToGroup( group_id, user_id ){
		$.get( ajax_file + "/user/group/addUser/"+user_id+"/"+group_id, null, function( data ){
			openGroup( group_id )
		});
		$('#button_group_list').fadeOut()
	}

	function delUserFromGroup( selected_group_id, group_id, user_id ){
		$.get( ajax_file + "user/group/delUser/" + user_id + "/" + group_id, null, function( data ){
			var selected_checkbox = ""
			  $("input[class=account_checkbox]:checked").each(function() {
			    selected_checkbox += selected_checkbox == "" ? $(this).attr('value') : ","+$(this).attr('value')

			    if( selected_group_id == group_id )
			    	$('#a_'+$(this).attr('class') ).remove()
			  });
			$.post( ajax_file, { module:'user', cp:'account/view', user_id: selected_checkbox }, function( html ){  
				$('#account_detail').html( html );
			})
		});
		$('#button_group_list').fadeOut()
	}



	function openAccount( user_id, user_where_is_id, group_id ){

		$('#account_ul>li').attr('class', '');
		$('#account_ul>li>input[class=account_checkbox]').attr('checked', false );
                
		if( user_id > 0 ){
			window.location.hash = '#user='+user_id
			$('#a_'+user_id ).attr('class','selected');
			$('input[name=account['+user_id+']]').attr('checked', true );
		}
		else if( user_where_is_id > 0 ){
			window.location.hash = '#guest='+user_where_is_id
			$('#guest_'+user_where_is_id ).attr('class','selected');
		}
		
		$.get( ajax_file + "/user/account/view/" + user_id + "/" + group_id + "/" + user_where_is_id, null, function( html ){  
			$('#account_detail').html( html );
			init_tooltip()
			resizeAddressBook()
		})

	}
	
	function editAccount( user_id, group_id ){
		$.get( ajax_file + "user/account/edit/" + user_id + "/" + group_id, null, function( html ){  
			$('#account_detail').html( html );
			init_tooltip()
		})
	}

	function editAccountPw( user_id, msg ){
		if( user_pw = prompt(msg) )
			$.get( ajax_file + "user/account/save_pw/" + user_id, {user_pw:user_pw}, function( html, result ){  
				alert( html )
			})
	}
	
	function accountSearch(){
		var usearch = $('#usearch').attr('value')
		$.get( ajax_file + "/user/account/list/", { usearch:usearch, group_id:open_group_id }, function( html ){  
			$('#account_list').html( html );		
			init_tooltip()
		})
	}


	function selectAccount( user_id, user_where_is_id, group_id, checked ){

		if( checked == false )
			$('#a_'+user_id ).attr('class', '' )
		else
			$('#a_'+user_id ).attr('class', 'selected' )
			
		var nchecked = $('#account_ul>li>input:checked').length
		if( nchecked == 1 ){
			user_id = $('#account_ul>li>input:checked').attr('value')
			openAccount( user_id, user_where_is_id, group_id )
		}
		else if( nchecked > 1 ){
			var selected_checkbox = ""
			  $("input[class=account_checkbox]:checked").each(function() {
			    selected_checkbox += selected_checkbox == "" ? $(this).attr('value') : ","+$(this).attr('value')
			  });

			if( group_id == 'online' || group_id == 'chat' )
				user_where_is_id = selected_checkbox
			else
				user_id = selected_checkbox
			  
			$.post( ajax_file + "/user/account/view/" + user_id + "/" + group_id + "/" + user_where_is_id, null, function( html ){  
				$('#account_detail').html( html );
				init_tooltip()
			})
		}
		else{
			if( open_group_id )
				openGroup( open_group_id )
			else
				$('#account_detail').html( '<div class="text">seleziona un account</div>' );
		}
		
			
			
	}
	
	function account_select_all(){
		selectAllCheckBox('account')
		$('#account_ul>li').attr('class', 'selected' )
		
		var selected_checkbox = ""
		  $("input[class=account_checkbox]:checked").each(function() {
		    selected_checkbox += selected_checkbox == "" ? $(this).attr('value') : ","+$(this).attr('value')
		  });
		$.post( ajax_file, { module:'user', cp:'account/view', user_id: selected_checkbox }, function( html ){  
			$('#account_detail').html( html );
			init_tooltip()
		})

	}
	
	function account_deselect_all(){
		deselectAllCheckBox('account')
		$('#account_ul>li').attr('class', '' );
		$('#account_detail').html( '<div class="text">seleziona un account</div>' );
	}

	function openGroup( group_id, user_id ){
		
		window.location.hash = '#group='+group_id
		
		open_group_id = group_id
		$('#group_list>ul>li').attr('class', '');
		$('#g_'+group_id).attr('class','selected');

		$.get( ajax_file + "/user/account_list/"+group_id, null, function( html ){
			$('#account_list').html( html );
			init_tooltip()
			
			if( user_id )
				openAccount( user_id )
			else
				$.get( ajax_file, { module:'user', cp:'group/view', group_id:group_id }, function( html ){  
					$('#account_detail').html( html );
					init_tooltip()
				})
			resizeAddressBook()
		})
	}
	
	function editGroup( group_id ){
		$.get( ajax_file, { module:'user', cp:'group/edit', group_id:group_id }, function( html ){  
			$('#account_detail').html( html );
			init_tooltip()
		})
	}

	
	function import_account(){
		
		$('#account_list').html( '' );		
		$.get( ajax_file, { module:'user', cp:'account/importPage'}, function( html ){  
			$('#account_detail').html( html );
			init_tooltip()
		})
	}
	
	
	function new_group( msg_name ){
		var name = prompt( msg_name )
		if( name )
			$.get( ajax_file, {module:'user', cp:'group/new', name:name}, function( group_id ){
				if( group_id ){
					$('#group_list>ul').append( '<li id="g_'+group_id+'" onclick="openGroup('+group_id+')"><span>'+name+'</span></li>' )
					openGroup( group_id )
				}
			})
	}

	function new_account(){
		openAccount(0)
	}
	
	function group_delete( group_id, msg ){
		if( confirm( msg ) ){
			$('#g_'+group_id).remove()	
			$('#group_detail').html('');
			$('#account_detail').html('');
			$('#group_list>ul>li').attr('class', '');
			
			$.get( ajax_file, {module:'user', cp:'group/del', group_id: group_id}, function( data ){
				openGroup('all')
			})
		}
	}
	


//	PERMISSION
	var permission_counter = 0
	function addPermission( permission_id, id, content_access, subcontent_access ){

		if( !permission_id )
			permission_id = 0

		html = '<div id="p_'+permission_counter+'">' +
					'<input type="hidden" name="permission['+permission_counter+'][permission_id]" value="'+permission_id+'">' +
					' has permission in ' +
					'<select name="permission['+permission_counter+'][id]">' +
					'<option value="0">Tutti i contenuti</option>'
					for( content_list_id in content_list )

		html +=			'<option value="'+content_list_id+'" '+(id==content_list_id?' selected="selected" class="selected"':null)+'>&nbsp;&nbsp;'+content_list[content_list_id]+'</option>'
		
		html +=		'</select>' +
				'	to &nbsp;&nbsp; <input type="checkbox" name="permission['+permission_counter+'][content_access]" id="content_access_'+permission_counter+'" '+( content_access==1 ? 'checked="checked"' : null)+'> ' +
				'	<label for="content_access_'+permission_counter+'">manage this content</label> &nbsp;&nbsp; and &nbsp;&nbsp; ' +
				'	to &nbsp;&nbsp; <input type="checkbox" name="permission['+permission_counter+'][subcontent_access]" id="subcontent_access_'+permission_counter+'" '+( subcontent_access==1 ? 'checked="checked"' : null)+'> ' +
				'	<label for="subcontent_access_'+permission_counter+'">manage the sub content</label>' +
				'	&nbsp; &nbsp; <a href="javascript:delPermission('+permission_counter+')"><img src="'+adm_tpl_img_dir+'del.gif" alt="del"></a>' +
				'</div>'

		permission_counter++
		$('#permission_list').append( html )
	}
	
	function delPermission( permission_counter ){
		$('#p_'+permission_counter).append('<input type="hidden" name="permission['+permission_counter+'][delete]" value="1">')
		$('#p_'+permission_counter).hide()
	}
	
	function loadPermission(){
		$.get( ajax_file, {module:"user", cp:"permission/edit", user_id: user_id}, function(html){
			$('#permission').html( html )
		});
	}
	
	function updatePermission( group_id, user_id ){
		$.getJSON( ajax_file, {module:"user", cp:"permission/list", user_id: user_id, group_id: group_id}, function(permission_list){
			if( permission_list ){
				var permission_counter = 0
				$('#permission_list').html('')
				for( i in permission_list ){
					var permission = permission_list[i]
					addPermission( permission['permission_id'], permission['id'], permission['content_access'], permission['subcontent_access'] )
				}
			}
		});
	}
	
	// create new email with user selected
	function newUserMail(){
		var selected_checkbox = ''
		$("input[class=account_checkbox]:checked").each(function() {
			selected_checkbox += selected_checkbox == "" ? $(this).attr('value') : ","+$(this).attr('value')
		});

		$.get( ajax_file, {module:"mailer", cp:"account/save", user: selected_checkbox} )
		window.location = "admin.server.php?module=mailer&cp=mail/new";
	}