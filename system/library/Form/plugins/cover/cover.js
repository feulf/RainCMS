//------------------------------------------
// Cover functions
//------------------------------------------

//script_dir = directory of cover/upload.php
function cover_delete( content_id, script_dir ){
    $('.cover-plugin').html( '<iframe src="'+script_dir+'upload.php?content_id='+content_id+'"></iframe>' );
    $.get( ajax_file + 'Content/cover_delete', {
        content_id:content_id
    } )
}
function cover_update(src, thumb_src, content_id, script_dir){
    $('.cover-plugin').html( '<a href="'+src+'" rel="lightbox"><img src="'+thumb_src+'" alt="cover"/></a> <img src="admin/views/aimg/del.gif" style="cursor:hand" onclick="cover_delete(\''+content_id+'\',\''+script_dir+'\');" alt="delete">' );
    $('a[rel*=lightbox]').lightBox();
}

function cover_choose(content_id){

    if( !$('#popup').html() ){
        var html =  '<div id="popup">'
        html += '<div class="content"><button style="float:right" onclick="cover_popup_close()">Close</button><div class="content_inside">Loading</div></div>'
        html += '<div class="bg"></div>'
        html += '</div>'
        $('body').css('overflow','hidden').append( html );
    }
    $('#popup').fadeIn("fast");
    $.get( admin_file + 'file/file_list/', {content_id:content_id}, function(result){
            $('#popup .content_inside').hide().html(result).fadeIn();
    });
}

function cover_popup_close(){
    $('#popup').fadeOut('fast');
    $('body').css('overflow','auto');
    
}