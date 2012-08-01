/**
 * Rain CMS 
 * generic.js contains all the generic function that are called in all templates
 */

var Rain = {

    /* initialize all the button for Rain */
    init: function(){
        $('.rain_new_content').live( "click", function(){
            Rain.new_content();
        });
        $('.rain_edit_content').live( "click", function(){
            Rain.edit_mode();
        });
        $('.content>.text').live( "dblclick", function(){
            Rain.edit_mode();
        });
        $('.rain_edit_settings').live( "click", function(){
            Rain.advanced_editing();
        });
        $('.rain_edit_delete').live( "click", function(){
            Rain.delete_content();
        });
        $('.rain_user_signout').live( "click", function(){
            Rain.signout();
        });
    },

    /* load dynamically a script */
    add_script: function( url ){
        $('<script type="text/javascript" src="' + url + '" />').appendTo('body');
    },

    /* load dynamically a stylesheet */
    add_css: function( url ){
        $('<link rel="stylesheet" type="text/css" href="' + url + '" />').appendTo('head');
    },

    /* enable edit mode */
    edit_mode: function(){
        var location = document.location.href;
        var match = /edit_mode=1/;
        if( !location.match(match) ){
            document.location.href += "?edit_mode=1";
        }
        else{
            document.location.href = location.replace("?edit_mode=1","");
        }
    },

    /* open a popup to create a new content */
    new_content: function(){
        
        /* load scripts */
        Rain.add_script( javascript_url + "jquery/jquery.form.min.js" );
        Rain.add_script( javascript_url + "jquery/jquery.validate.min.js" );
        
        /* load the popup */
        RainPopup.init( "new content" );

        /* get the type childs list */
        $.getJSON( ajax_file + "rain_edit/content_type_childs/" + content_id, function( json ){

            var type_childs = json.type_childs;
            var selected_type_childs = json.selected_type_childs;
            var parent_name = json.parent_name;
            var html = "";

            html += '<div class="new_content_list"><ul>';
            if( selected_type_childs ){
                for( var i = 0, n=selected_type_childs.length; i<n; i++ ){
                    html += '<li onclick="Rain._new_content_setting('+selected_type_childs[i].type_id+', 0 )">'+selected_type_childs[i].type+'</li>';
                }
            }

            if( type_childs )
                for( var i = 0, n=type_childs.length; i<n; i++ ){
                    html += '<li onclick="Rain._new_content_setting('+type_childs[i].type_id+','+type_childs[i].content_id+' )">'+type_childs[i].parent_name+' &gt; '+type_childs[i].type+'</li>';
                }
            html += '</ul></div>';

            RainPopup.html( html );
        })


    },
    
    advanced_editing: function(){

        Rain.add_script( javascript_url + "jquery/jquery.form.min.js" );
        Rain.add_script( javascript_url + "jquery/jquery.validate.min.js" );

        /* get the type childs list */
        $.get( ajax_file + "rain_edit/content_edit/" + content_id, function( form ){
            var html = '';
            html += form;
            html += '<hr><a href="javascript:Rain.delete_content()" class="btn btn-danger">Delete</a>';
            RainPopup.html(html);
        });
        
        RainPopup.init( "Settings" );

        window.location.href = admin_file + 'content/edit/'+content_id;
    },
    
    delete_content: function(){
        $.getJSON(ajax_file+'rain_edit/content_delete/'+content_id, function(json){
            if( json.success ){
                document.location.href= url + json.path;
            }
        });
    },
    
    new_content_list_select: function(){
        $('.new_content_list').show();
        $('.new_content_setting').hide();
    },
    
    signout: function(){
        $.get( ajax_file + 'user/signout/', function(){
            window.location.reload(); 
        });
    },

    _new_content_setting: function ( type_id, parent_id ){
        if( !$('.new_content_setting').html() ){
            /* Check the field for this content type (example if news there it should be a date field) */
            var html = '<div class="new_content_setting"><a href="javascript:Rain.new_content_list_select();">Back</a><div class="content_form"></div></div>';
            RainPopup.append( html );
        }
        $('.new_content_list').hide();
        $('.new_content_setting').show();

        html = '<form id="rain_new_content_form" action="'+ajax_file+'rain_edit/content_new/'+parent_id+'/" method="post">';
        html += '<input type="hidden" name="type_id" value="'+type_id+'">';
        html += 'Title <br><input type="text" name="title" value="" class="required"/><br>';
        html += 'Content <br><textarea name="content" class="required"></textarea>';
        html += '<input type="submit" value="SAVE" class="btn btn-primary"/>';
        html += '</form>';
        
        $('.content_form').html(html);
        
        $("#rain_new_content_form").validate({
            submitHandler: function(form){
                $('#rain_new_content_form').hide();
                $(form).ajaxSubmit({
                    dataType: "json",
                    success:function( json ){
                        if( json.success ){
                            document.location.href = url + json.path;
                        }else{
                            RainWindow.html( json.message );
                        }
                    }
                });
            }
        });
        
    }    
    
};

/* init Rain */
Rain.init();