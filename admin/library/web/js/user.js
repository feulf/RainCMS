// ADDRESS BOOK

$(document).ready( function(){

    var match, reg;

    //group_id
    reg = new RegExp( 'group=([a-z_A-Z_0-9]+)');
    match = reg.exec( window.location.hash );
    open_group_id = match ? match[1] : 'all';

    reg = new RegExp( 'user=([0-9]+)');
    match = reg.exec( window.location.hash );
    open_user_id = match ? match[1] : null;

    reg = new RegExp( 'guest=([0-9]+)');
    match = reg.exec( window.location.hash );
    open_user_localization_id = match ? match[1] : null;
        
    openGroup( open_group_id );

    if( open_user_id )
        openAccount( open_user_id, open_user_localization_id );

    resizeAddressBook(1);

})

function createAccountHash(user_id, user_localization_id, group_id ){
        
    window.location.hash = '';
    if( group_id )
        window.location.hash += 'group='+group_id;
    else if( open_group_id )
        window.location.hash += 'group='+open_group_id;
    if( user_id )
        window.location.hash += window.location.hash ? '&user='+user_id : 'user='+user_id;
    if( user_localization_id  )
        window.location.hash += window.location.hash ? '&guest='+user_localization_id : 'guest='+user_localization_id;

}


// resize the address book
var open_group_id, open_user_id, open_user_localization_id;
window.onresize = function(){
    resizeAddressBook();
}   
function resizeAddressBook(){
    $('#account_list_inner').css( "height", $(window).height() - ( $("#header").height()+80 ) );
}


// select an account
function openAccount( user_id, user_localization_id, group_id){

    createAccountHash( user_id, user_localization_id, group_id );

    $('#account_ul>li').attr('class','');
    $('#account_ul>li>input[class=account_checkbox]').attr('checked', false );

    if( user_id > 0 ){
        $('#a_'+user_id ).attr('class','selected');
        $('input[id=account_input_'+user_id+']').attr('checked', true );
    }
    else if( user_localization_id > 0 ){
        $('#guest_'+user_localization_id ).attr('class','selected');
    }

    $.get( ajax_file + "/user/account/", {
        group_id:group_id,
        user_id:user_id,
        user_localization_id:user_localization_id
    }, function( html ){  
        $('#account_detail').html( html );
        init_tooltip()
        resizeAddressBook()
    });
            
}

function editAccount( user_id, group_id ){
    $.get( ajax_file + "/user/account_edit/" + user_id + "/" + group_id, null, function( html ){  
        $('#account_detail').html( html );
        init_tooltip();
    })
}

function editAccountPw( user_id, msg ){
    if( user_pw = prompt(msg) )
        $.get( ajax_file + "user/account_save_pw/" + user_id + "/", {
            user_pw: user_pw
        }, function( html, result ){
            alert( html );
        })
}



function selectAccount( user_id, user_localization_id, group_id, checked ){

    if( checked == false )
        $('#a_'+user_id ).attr('class', '' );
    else
        $('#a_'+user_id ).attr('class', 'selected' );

    var nchecked = $("#account_ul>li>input:checked").length;

    if( nchecked == 1 ){
        user_id = $('#account_ul>li>input:checked').attr('value');
        openAccount( user_id, user_localization_id, group_id );
    }
    else if( nchecked > 1 ){

        var selected_checkbox = "";
        $("input[class=account_checkbox]:checked").each(function() {
            selected_checkbox += selected_checkbox == "" ? $(this).attr('value') : ","+$(this).attr('value');
        });

        if( group_id == 'online' )
            user_localization_id = selected_checkbox;
        else
            user_id = selected_checkbox;

        $.get( ajax_file + "/user/account/", {
            user_id:user_id, 
            group_id:group_id, 
            user_localization_id:user_localization_id
        }, function( html ){  
            $('#account_detail').html( html );
            init_tooltip();
        })
    }
    else{
        if( open_group_id )
            openGroup( open_group_id );
        else
            $('#account_detail').html( '<div class="text">seleziona un account</div>' );
    }

}


function account_select_all(){
    selectAllCheckBox('account');
    $('#account_ul>li').attr('class', 'selected' );
    selectAccount();
}

function account_deselect_all(){
    deselectAllCheckBox('account');
    $('#account_ul>li').attr('class', '' );
    selectAccount();
}

    
/**
 * Group methods
 */
    
// select a group
function openGroup( group_id, user_id ){

    if( group_id == 'all' || group_id == '' )
        window.location.hash = '';
    else
        window.location.hash = '#group='+group_id;

    open_group_id = group_id;

    $('#group_list>ul>li').attr('class', '');
    $('#g_'+group_id).attr('class', 'selected');

    $.get( ajax_file + "/user/account_list/" + group_id, null, function( html ){

        $('#account_list').html( html );
        init_tooltip();

        if( user_id )
            openAccount( user_id );
        else{
            $.get( ajax_file + "/user/group_view/" + group_id + "/", null, function( html ){  
                $('#account_detail').html( html );
                init_tooltip();
            });
        }
        resizeAddressBook();
    })
}

function editGroup( group_id ){
    $.get( ajax_file + "/user/group_edit/"+group_id, null, function( html ){
        $('#account_detail').html( html );
        init_tooltip();
    })
}

function open_group_list( sel_group_id, user_id, add_to_msg, remove_from_msg ){

    if( $('#button_group_list').css('display') == 'none'){

        $.getJSON( ajax_file + "/user/account_group/" + user_id, function(json){

            $('#button_group_list').html('');
            if( json && json['group_list'] != 0 ){
                $('#button_group_list').append( '<span><b>'+add_to_msg+'</b></span>' )
                for( group_id in json['group_list'] )
                    $('#button_group_list').append( '<div onclick="addUserToGroup('+json['group_list'][group_id]['group_id']+', \''+user_id+'\');">' + json['group_list'][group_id]['name'] + '</div>' );
            }

            if( json && json['in_group_list'] != 0 ){
                $('#button_group_list').append( '<span><b>'+remove_from_msg+'</b></span>' );
                for( group_id in json['in_group_list'] )
                    $('#button_group_list').append( '<div onclick="delUserFromGroup(\''+sel_group_id+'\', '+json['in_group_list'][group_id]['group_id']+', \''+user_id+'\');">' + json['in_group_list'][group_id]['name'] + '</div>' );
            }

            $('#button_group_list').fadeIn("fast");
            init_tooltip();

        });


    }
    else
        $('#button_group_list').fadeOut("fast");

}
    
    
function new_group( msg_name ){
    var group_name = prompt( msg_name );
    if( group_name )
        $.get( ajax_file + "/user/group_new/", {
            group_name:group_name
        }, function( group_id ){
            if( group_id ){
                $('#group_list>ul').append( '<li id="g_'+group_id+'" onclick="openGroup('+group_id+')"><span>'+group_name+'</span></li>' );
                openGroup( group_id );
            }
        })
}
    



function addUserToGroup( group_id, user_id ){
    $.get( ajax_file + "/user/group_add_user/", {
        user_id:user_id,
        group_id:group_id
    }, function( data ){
        openGroup( group_id );
    });
    $('#button_group_list').fadeOut("fast");
}



function delUserFromGroup( selected_group_id, group_id, user_id ){

    $('#button_group_list').fadeOut("fast");
    $.get( ajax_file + "/user/group_del_user", {
        user_id:user_id, 
        group_id:group_id
    }, function( data ){
        var selected_checkbox = null;
        openGroup( selected_group_id );
    });
	
}
	
function user_delete( msg ){
    if( confirm(msg) ){
        var nchecked = $('#account_ul>li>input:checked').length;
        var selected_checkbox = "";
        $("input[class=account_checkbox]:checked").each(function() {
            selected_checkbox += selected_checkbox == "" ? $(this).attr('value') : ","+$(this).attr('value');
            $('#a_'+ $(this).attr('value') ).remove();
        });

        $('#group_detail').html('');
        $('#account_detail').html('');
        $('#account_list_inner>ul>li').attr('class', '');
        $.post( ajax_file + "/user/account_delete/",{
            user_id: selected_checkbox
        }, function( data ){
            if( open_group_id )
                openGroup( open_group_id );
            else
                openGroup(0);
        })

    }
}


//	PERMISSION
var permission_counter = 0
function add_permission( permission_id, id, content_access, subcontent_access ){

    if( !permission_id )
        permission_id = 0;

    var html = '<div id="p_'+permission_counter+'">' +
    '<input type="hidden" name="permission['+permission_counter+'][permission_id]" value="'+permission_id+'">' +
    ' has permission in ' +
    '<select name="permission['+permission_counter+'][id]">' +
    '<option value="0">Tutti i contenuti</option>';

    for( content_list_id in content_list )
        html +=			'<option value="'+content_list_id+'" '+(id==content_list_id?' selected="selected" class="selected"':null)+'>&nbsp;&nbsp;'+content_list[content_list_id]+'</option>';
		
    html +=		'</select>' +
    '	to &nbsp;&nbsp; <input type="checkbox" name="permission['+permission_counter+'][content_access]" id="content_access_'+permission_counter+'" '+( content_access==1 ? 'checked="checked"' : null)+'> ' +
    '	<label for="content_access_'+permission_counter+'">manage this content</label> &nbsp;&nbsp; and &nbsp;&nbsp; ' +
    '	to &nbsp;&nbsp; <input type="checkbox" name="permission['+permission_counter+'][subcontent_access]" id="subcontent_access_'+permission_counter+'" '+( subcontent_access==1 ? 'checked="checked"' : null)+'> ' +
    '	<label for="subcontent_access_'+permission_counter+'">manage the sub content</label>' +
    '	&nbsp; &nbsp; <a href="javascript:delPermission('+permission_counter+')"><img src="'+admin_views_images_url+'del.gif" alt="del"></a>' +
    '</div>';

    permission_counter++;
    $('#permission_list').append( html );
}
	
function del_permission( permission_counter ){
    $('#p_'+permission_counter).append('<input type="hidden" name="permission['+permission_counter+'][delete]" value="1">');
    $('#p_'+permission_counter).hide();
}
	
function load_permission(){
    $.get( ajax_file, {
        module:"user", 
        cp:"permission/edit", 
        user_id: user_id
    }, function(html){
        $('#permission').html( html );
    });
}
	
function update_permission( group_id, user_id ){
    $.getJSON( ajax_file, {
        module:"user", 
        cp:"permission/list", 
        user_id: user_id, 
        group_id: group_id
    }, function(permission_list){
        if( permission_list ){
            var permission_counter = 0;
            $('#permission_list').html('');
            for( i in permission_list ){
                var permission = permission_list[i];
                addPermission( permission['permission_id'], permission['id'], permission['content_access'], permission['subcontent_access'] );
            }
        }
    });
}