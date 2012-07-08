var RainEdit = {
    
    // init all the tools to edit page
    init: function(){
        this._init_aloha();
        this._init_block_sortable();
        this._init_toolbox();
        this._init_buttons();
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
    
    //
    // Fix the block setting functionality
    //
    _init_buttons: function(){
        
        $('.rain_load_area_add_block').live("click", function(){
            var load_area_name_class = $(this).attr('class').split(' ')[1];
            var load_area = load_area_name_class.match(/rain_load_area_(.*)/)[1];
            RainEdit.block_new( load_area );
        });
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

    block_new: function ( load_area ){

        // open a popup to create a new block

        // load scripts
        Rain.add_script( javascript_url + "jquery/jquery.form.min.js" );
        Rain.add_script( javascript_url + "jquery/jquery.validate.min.js" );

        // load the popup
        RainPopup.init( load_area + " &gt; New Block" );

        // get the type childs list
        $.getJSON( ajax_file + "rain_edit/block_type_list/" + load_area, function( json ){

            var block_type_list = json.block_type_list;
            var html = "";

            html += '<div class="new_block_list"><ul>';
            if( block_type_list ){
                for( var i = 0, n=block_type_list.length; i<n; i++ ){
                    html += '<li onclick="RainEdit._block_new_setting('+block_type_list[i].block_type_id+', \''+load_area+'\' )">'+block_type_list[i].name+'</li>';
                }
            }
            html += '</ul></div>';
           
            RainPopup.html( html );
 
        })


    },
    
    block_new_list_select: function(){
        $('.new_content_list').show();
        $('.new_content_setting').hide();
    },
    
    _block_new_setting: function ( block_type_id, load_area ){
        if( !$('.new_content_setting').html() ){
            var html = '<div class="new_block_setting"><a href="javascript:RainEdit.block_new_list_select();">Back</a><div class="content_form"></div></div>';
            RainPopup.append( html );
        }
        $('.new_block_list').hide();
        $('.new_block_setting').show();

        html = '<form id="rain_new_block_form" action="'+ajax_file+'rain_edit/block_new/'+load_area+'/" method="post">';
        html += '<input type="hidden" name="type_id" value="'+block_type_id+'">';
        html += 'Title <br><input type="text" name="title" value="" class="required"/><br>';
        html += 'Content <br><textarea name="content" class="required"></textarea>';
        html += '<input type="submit" value="SAVE" class="btn btn-primary"/>';
        html += '</form>';
        
        $('.content_form').html(html)
        
        $("#rain_new_block_form").validate({
            submitHandler: function(form){
                $('#rain_new_block_form').hide();
                $(form).ajaxSubmit({
                    dataType: "json",
                    success:function( json ){
                        if( json.success ){
                         //   document.location.href = url + json.path;
                        }
                        else{
                            RainWindow.html( json.message );
                        }
                    }
                });
            }
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
        
    }

};


var RainDragDropUpload = {
    
    dropbox:'',
    message:'',
    init_drag_drop_upload: function(){

        $('footer').append('<div class="message"></div>');

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

                    var date = new Date();
                    this.image_id = date.getTime();

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
              '<img />'+
              //'<div class="progress" style="background:red;height:20px;"></div>'+
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
            
            RainEdit.enable_save_changes_button();
    },

    showMessage :function (msg){
            this.message.html(msg);
    }
}
