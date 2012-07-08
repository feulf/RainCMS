var RainEdit = {
    
    // init all the tools to edit page
    init: function(){
        this._init_aloha();
        this._init_block_sortable();
        this._init_toolbox();
        this._init_buttons();
    },

    // init aloha editor
    _init_aloha: function(){
        var $ = Aloha.jQuery;
        $('.content>.text, .content>.summary, .content>.title').aloha();
        $('.content>.text, .content>.summary, .content>.title').keyup(function(e){
            switch( e.keyCode ){
                case 27: // Left
                case 37: // Left
                case 38: // Up
                case 39: // Right
                case 40: // Down
                    break;
                default:
                    RainEdit.enable_save_changes_button();
            }
        });

        setTimeout( function(){
            $('.aloha-sidebar-bar').fadeOut();
        }, 600 );

    },
    
    // Enable order content list
    _init_block_sortable: function ( content_id ){
        $(".rain_load_area_edit_content").each(function(){
            $(this).sortable({
                opacity: 0.5,
                handle: '.rain_block_edit_title',
                connectWith: '.rain_load_area_edit_content',
                disableSelection: true,
                scroll: true,
                cancel: "#rain_block_main",
                update: function( i ){
                    RainEdit.enable_save_changes_button();
                    init_aloha();
                }
            });
        });

    },
    
    
    _init_buttons: function(){
        
        $('.rain_block_setting').live("click", function(){
            var block_class_id = $(this).attr('class').split(' ')[1];
            var block_id = (block_class_id.match(/rain_block_(\d*)/))[1];
            var title_div = $(this).parent().parent().find(".rain_block_title")[0];
            var title = $(title_div).html();
            
            RainEdit.block_setting( block_id, title );
        });

    },
    
    block_delete: function ( block_id ){

        if( confirm( "Are you sure you want to delete this block?" ) ){
            RainEdit.block_setting_close();
            $.post( ajax_file + "rain_edit/block_delete/" + block_id, function( result ){
                $('#rain_block_' + block_id).slideUp(function(){
                    $(this).remove();
                });
            });

        }
    },

    block_setting: function ( block_id, title ){

        Rain.add_script( javascript_url + "jquery/jquery.form.min.js" );

        $('#rain_block_'+block_id).addClass("selected");
        if( !title )
            title = "Loading";
        
        RainPopup.init( title );

        $.getJSON(ajax_file+"rain_edit/block_settings_get/"+block_id, function(json){
            
            var title = json["block"]["name"];
            var html = "";
            if( json["options"].length ){
                html += '<form id="block_settings" name="block_settings" action="' + ajax_file + 'rain_edit/block_settings_save/'+block_id+'" method="POST">'
                for( var i in json["options"] ){
                    var block_option = json["options"][i];
                    var option_name = block_option.name;
                    var option_title = block_option.title;
                    if( option_name ){
                        var option_value = block_option.value;
                        html += option_title + ': <input type="text" name="'+option_name+'" value="'+option_value+'"/><br>';
                    }
                }
                html += '<input type="submit" value="SAVE" class="btn btn-primary"/>';
                html += '</form>';
                
                RainPopup.html(html);
                RainPopup.title(title);
            }

            html += '<a class="rain_block_delete">Delete this block?</a>';
            $('.rain_block_delete').live("click",function(){
                RainEdit.block_delete( block_id );
            })

            $('.rain_popup_window_title').html( title );
            $('.rain_popup_window_content').html( html );

            $('#block_settings').ajaxForm( function(){
                RainEdit.block_refresh();
            })

        });

    },
    
    // close the block settings
    block_setting_close: function (block_id){
        $('.rain_block_edit').removeClass("selected");
        $('.rain_popup').fadeOut("fast", function(){
            $("html,body").css("overflow","scroll");
            $(this).remove();
        })
    },
    
    // reload one selected block
    block_refresh: function (){
        window.location.reload();
    },
    
    enable_save_changes_button: function (){
        $('#save_changes_button').removeClass('disabled').addClass('on').unbind('click').click( function(){
            RainEdit.save_changes();
        })
    },

    disable_save_changes_button: function (){
        $('#save_changes_button').removeClass('on').addClass('disabled').unbind('click');
    },
    
    
    // save the change of the page
    save_changes: function (){
        RainEdit.disable_save_changes_button();

        // save the position of the blocks
        $(".rain_load_area_edit_content").each( function(i){
            var load_area = this.id.substr(15);
            var sortedList = $(this).sortable("serialize");
            $.post( ajax_file + "rain_edit/block_sort/", {
                load_area:load_area, 
                content_id:content_id, 
                sortable:sortedList
            }, function(){

            });
        })

        // save the content of the blocks
        var block_to_edit = [];
        $('.rain_block_editable').each(function(){
            var class_list = $(this).attr('class');
            if( m = class_list.match( /.*rain_content_(\d*).*/ ) ){
                var content_id = m[1];

                // get title and text of the content
                var content_text = $('.rain_content_' + content_id + ' .content>.text' ).html();
                var content_title = $('.rain_content_' + content_id + ' .content>.title' ).html();
                var content_summary = $('.rain_content_' + content_id + ' .content>.summary' ).html();
                $.post( ajax_file + "rain_edit/content_wysiwyg_update/"+content_id, {
                    title:content_title, 
                    content:content_text,
                    summary:content_summary
                }, function(){
                    //edit_mode_off();
                    });
            }
            else{
            //edit_mode_off();
            }

        })

    },
    
    _init_toolbox: function (){
        $("body").append( '<div id="toolbox"></div>' );
        $('#toolbox').append( '<a href="'+admin_file+'" class="tooltip_popup logo"></a>' );
        $('#toolbox').append( '<a id="save_changes_button" class="tooltip_popup disabled" title="Enable/disable edit mode">Save Changes</a>' );
    },
    
    new_content_setting: function( type_id, parent_id ){
        if( !$('.new_content_setting').html() ){
            var html = '<div class="new_content_setting"><a href="javascript:new_content_list_select();">Back</a><div class="content_form"></div></div>';
            $('.rain_popup_window_content').append( html );
        }
        RainEdit.new_content_setting_select();

        html = '<form action="'+ajax_file+'rain_edit/content_new/'+parent_id+'/" method="post">';
        html += '<input type="hidden" name="type_id" value="'+type_id+'">';
        html += 'Title <br><input type="text" name="title" value=""/><br>';
        html += 'Content <br><textarea width=100%></textarea>';
        html += '<input type="submit" value="SAVE" class="btn btn-primary"/>';
        html += '</form>';
        
        $('.content_form').html( html)
        
    },

};