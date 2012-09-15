var RainToolbox = {
    
    /* init all the tools to edit page */
    init: function(){
        this._init_toolbox();
        this._init_buttons();
    },



    
    /*
    // Fix the block setting functionality
    */
    _init_buttons: function(){

        $('#edit_content_button').live("click", function(){
            window.location = admin_file + "content/edit/" + content_id;
        });

    },
    
    _init_toolbox: function (){
        $("body").append( '<div id="toolbox"></div>' );
        $('#toolbox').append( '<a href="'+admin_file+'" class="tooltip_popup logo"></a>' );
        $('#toolbox').append( '<a id="edit_content_button" class="tooltip_popup" title="edit this content">Edit Content</a>' );
    }

};