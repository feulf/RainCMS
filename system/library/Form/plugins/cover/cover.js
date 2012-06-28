//------------------------------------------
// Cover functions
//------------------------------------------

//script_dir = directory of cover/upload.php
function cover_delete( content_id, script_dir ){
    $('#cover').html( '<iframe src="'+script_dir+'upload.php?content_id='+content_id+'" style="height:30px;width:120px;border:0px;overflow:hidden;"></iframe>' );
    $.get( ajax_file + 'Content/cover_delete', {
        content_id:content_id
    } )
}
function cover_update(src, thumb_src, content_id, script_dir){
    $('#cover').html( '<a href="'+src+'" rel="lightbox"><img src="'+thumb_src+'" alt="cover"/></a> <img src="admin/views/aimg/del.gif" style="cursor:hand" onclick="cover_delete(\''+content_id+'\',\''+script_dir+'\');" alt="delete">' );
    $('a[rel*=lightbox]').lightBox();
}