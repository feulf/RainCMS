/**
 * Rain CMS 
 * generic.js contains all the generic function that are called in all templates
 */

// Links
$('.rain_update_content').live('click',function(){
    enable_disable_edit_mode();
})

$('.rain_user_signout').live('click',function(){
    signout();
})

// Add remove edit mode
function enable_disable_edit_mode(){
    document.location.href += '?edit_mode=1';
}

function signout(){
    $.get( ajax_file + "user/signout/", function( html ){
        location.reload(true);
    })
}
