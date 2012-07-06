var RainEdit = {
    
    // init all the tools to edit page
    init: function(){
        this._init_aloha();
        this._init_block_sortable();
        this._init_toolbox();
        RainDragDropUpload.init_drag_drop_upload();
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
        $('#edit_mode_html').live( "click", function(){
            RainEdit.edit_mode_load_html();
        });

        $('#edit_mode_blocks').live( "click", function(){
            if( $('#toolbox .block_list').css('display') == 'block' ){
                $('#toolbox .block_list').fadeOut(function(){
                    $(this).remove();
                });
            }
            else{
                $('#toolbox').append('<div class="toolbox_popup block_list rain_load_area_edit_content" id="rain_load_area-disabled"><div class="close"></div></div>');
                $('#toolbox .block_list').css('display','none').fadeIn();
                RainEdit.edit_mode_load_blocks();
                $('#toolbox .block_list .close').click(function(){
                    $('#toolbox .tooltip_popup').remove();
                })
            }
        });

        $('#edit_mode_themes').live( "click", function(){
            if( $('#toolbox .theme_list').css('display') == 'block' ){
                $('#toolbox .theme_list').fadeOut(function(){
                    $(this).remove();
                });
            }
            else{
                $('#toolbox').append('<div class="toolbox_popout theme_list"><div class="close"></div></div>');
                $('#toolbox .theme_list').css('display','none').fadeIn();
                RainEdit.edit_mode_load_themes();
                $('#toolbox .theme_list .close').click(function(){
                    $('#toolbox .theme_list').remove();
                })
            }
        });

        $('#edit_mode_pages').live( "click", function(){
            if( $('#toolbox .page_list').css('display') == 'block' ){
                $('#toolbox .page_list').fadeOut(function(){
                    $(this).remove();
                });
            }
            else{
                $('#edit_mode_pages').append('<div class="toolbox_popout page_list"><div class="close"></div></div>');
                $('#toolbox .page_list').css('display','none').fadeIn();
                RainEdit.edit_mode_load_pages();
                $('#toolbox .page_list .close').click(function(){
                    $('#toolbox .page_list').remove();
                })
            }
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

    block_setting: function ( block_id ){

        $('#rain_block_'+block_id).addClass("selected");

        $('body').append('<div class="rain_popup"><div class="rain_popup_bg"></div><div class="rain_popup_window"><div class="rain_popup_close"></div><div class="rain_popup_window_inner"><h1 class="rain_popup_window_title"></h1><div class="rain_popup_window_content"></div></div></div>');
        $('.rain_popup_bg').click( function(){
            RainEdit.block_setting_close();
        })
        $('.rain_popup_close').click( function(){
            RainEdit.block_setting_close();
        })

        $('.rain_popup').fadeIn("fast");

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

            }

            html += '<a href="javascript:block_delete('+block_id+');" class="delete">Delete the block?</a>';

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
        $("body").append( '<div id="toolbox"></div><div id="toolbox_user"></div>' );
        $('#toolbox').append( '<a href="'+admin_file+'" class="tooltip_popup logo"></a>' );
        //$('#toolbox').append( '<a href="'+admin_file+'" class="tooltip_popup" title="'+_control_panel_msg_+'">'+_control_panel_msg_+'</a>' );
        //$('#toolbox').append( '<a id="edit_mode_blocks" class="tooltip_popup" title="Show list of blocks">Blocks</a>' );
        //$('#toolbox').append( '<a id="edit_mode_themes" class="tooltip_popup" title="Show list of Themes">Themes</a>' );
        //$('#toolbox').append( '<a id="edit_mode_pages" class="tooltip_popup" title="Show list of Pages">Layout</a>' );
        //$('#toolbox').append( '<a id="edit_mode_html" class="tooltip_popup" title="Edit HTML">Edit HTML</a>' );
        $('#toolbox').append( '<a id="save_changes_button" class="tooltip_popup disabled" title="Enable/disable edit mode">Save Changes</a>' );


        $('#toolbox_user').append( 'Welcome <b>'+user_name+'</b> <a href="javascript:user_logout()">Sign out</a>' );

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

    toolbox_position: function(){
        if( $("#toolbox").css('top') == "0px" ){
            $("#toolbox").css("bottom", "0px" )
            $("#toolbox").css("top", null );
        }
        else{
            $("#toolbox").css("top", "0px" )
            $("#toolbox").css("bottom", null );
        }
    },

    edit_mode_load_blocks: function(){

        $.getJSON( ajax_file + "rain_edit/block_list", {
            id:id
        }, function(blocks){
            var html = '';
            for( var i in blocks ){

                // for some strange reason there is one iteration that is "remove"
                if( i != 'remove' ){
                    var block_name		= blocks[i].title;
                    var block_id		= blocks[i].block_id;
                    var content_id		= blocks[i].content_id;
                    var content             = blocks[i].content;

                    html+='<div class="rain_block_edit rain_block_editable rain_content_'+content_id+'" id="rain_block_'+block_id+'"><div class="rain_block_edit_title">'+block_name+'</div><div class="rain_block_content"><div class="content"><div class="text">'+content+'</div></div></div></div>';
                }
            }

            $('#toolbox .block_list').html( html );
            RainEdit._init_block_sortable();
        })


        //$('#toolbox .block_list').append('<div class="rain_block_edit rain_block_editable rain_content_0" id="rain_block_0"><div class="rain_block_edit_title">Drag Me!</div><div class="rain_block_content"><div class="content"><div class="text">Woohoo, you can edit this content!</div></div></div></div>');

    },


    edit_mode_load_themes: function(){
        $('#toolbox .theme_list').html('');
        $.getJSON( ajax_file + "rain_edit/themes_list", function(themes){
            var html = '';
            for( var i in themes ){
                // for some strange reason there is one iteration that is "remove"
                if( i != 'remove' ){
                    var theme	= themes[i].theme;
                    var theme_id	= themes[i].theme_id
                    var theme_dir	= themes_dir + theme_id;
                    var thumbnail	= url + theme_dir + '/' + 'preview.gif';

                    html+='<div id="t_'+theme_id+'" class="theme_block '+(theme_id == selected_theme?'selected':'')+'"><a onclick="theme_set(\''+theme_id+'\')"><img title="select this theme" src="'+thumbnail+'" /></a><div class="theme_block_title">'+theme+' <img src="'+admin_views_images_url+'preview.gif" title="Preview this theme" onclick="theme_preview(\''+theme+'\',\''+url+'\')"></div></div>';
                }
            }

            $('#toolbox .theme_list').html( html );

        })
    },


    edit_mode_load_pages: function(){
        $('#toolbox .page_list').html('');
        $.getJSON( ajax_file + "rain_edit/pages_list", function(pages){
            var html = '';
            for( var i in pages ){
                // for some strange reason there is one iteration that is "remove"
                if( i != 'remove' ){
                    var layout	= pages[i].name;
                    var layout_id	= pages[i].layout_id;

                    html+='<div id="p_'+layout_id+'" class="block '+(layout_id == selected_layout?'selected':'')+'"><div class="block_title"><a onclick="page_set(\''+layout_id+'\')">'+layout+' </a></div></div>';

                }
            }

            $('#toolbox .page_list').html( html );


        })
    },


    edit_mode_load_html: function(){
        $('#toolbox .page_list').html('');
        $.get( ajax_file + "rain_edit/get_layout_html/"+selected_theme+"/"+selected_layout, function(html){
            alert( html );
        })
    },


    theme_preview: function( theme_id, url ){
        $.get( admin_ajax_file + "configure/languages/", {
            cp:"themes/preview", 
            theme_id: theme_id
        }, function( html ){
            window.location = url;
        });
    },

    theme_set: function( theme_id ){

        $.get( admin_ajax_file + "configure/set_theme/" + theme_id, function(h){
            $('.thumbnail>a>img').attr('class','thumb_image');
            $('.theme_block').removeClass('selected');
            $('#t_'+theme_id+'').addClass('selected');
            window.location.reload();
        });
    },

    page_set: function( layout_id ){

        $.get( ajax_file + "rain_edit/set_layout/" + id + "/" + layout_id, function(h){
            $('.page_block').removeClass('selected');
            $('#p_'+layout_id+'').addClass('selected');
            window.location.reload();
        });
    }

};


var RainDragDropUpload = {
    
    dropbox:'',
    message:'',
    init_drag_drop_upload: function(){

        $('footer').append('<div class="message">HELLO</div>');

        this.dropbox = $('.content>.text');
        this.message = $('.message', this.dropbox);
	this.dropbox.filedrop({
		// The name of the $_FILES entry:
		paramname:'file',
		maxfiles: 5,
                maxfilesize: 2,
		url: ajax_file + 'rain_edit/upload_image_content/'+content_id,
		uploadFinished:function(i,file,response){
			$.data(file).addClass('done');
			// response is the JSON object that post_file.php returns
		},
                error: function(err, file) {
			switch(err) {
				case 'BrowserNotSupported':
					showMessage('Your browser does not support HTML5 file uploads!');
					break;
				case 'TooManyFiles':
					alert('Too many files! Please select 5 at most! (configurable)');
					break;
				case 'FileTooLarge':
					alert(file.name+' is too large! Please upload files up to 2mb (configurable).');
					break;
				default:
                                        console.log( err );
					break;
			}
		},
		// Called before each upload is started
		beforeEach: function(file){
			if(!file.type.match(/^image\//)){
				alert('Only images are allowed!');

				// Returning false will cause the
				// file to be rejected
				return false;
			}
		},
		
		uploadStarted:function(i, file, len){
			RainDragDropUpload.createImage(file);
		},
		
		progressUpdated: function(i, file, progress) {
			//$.data(file).find('.progress').width(progress);
		}
    	 
	});

    },

    template :'<div class="preview">'+
                            '<span class="imageHolder">'+
                                    '<img />'+
                                    '<span class="uploaded"></span>'+
                            '</span>'+
                            '<div class="progressHolder">'+
                                    '<div class="progress"></div>'+
                            '</div>'+
                    '</div>',


    createImage: function (file){

            var preview = $(this.template), 
            image = $('img', preview);

            var reader = new FileReader();

            image.width = 100;
            image.height = 100;

            reader.onload = function(e){
                    // e.target.result holds the DataURL which
                    // can be used as a source of the image:

                    image.attr('src',e.target.result);
            };

            // Reading the file as a DataURL. When finished,
            // this will trigger the onload function above:
            reader.readAsDataURL(file);

            this.message.hide();
            preview.appendTo(this.dropbox);

            // Associating a preview container
            // with the file, using jQuery's $.data():

            $.data(file,preview);
    },

    showMessage :function (msg){
            this.message.html(msg);
    }
}



