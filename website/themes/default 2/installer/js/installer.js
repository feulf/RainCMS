

var RainInstaller = {
    init:function(){
        $('.rain_module_download').live("click", function(){
            RainInstaller.download( $(this).parent().attr("class") );
        });
        $('.rain_module_activate').live("click", function(){
            RainInstaller.activate( $(this).parent().attr("class") );
        });
        $('.rain_module_deactivate').live("click", function(){
            RainInstaller.deactivate( $(this).parent().attr("class") );
        });
        $('.rain_module_remove').live("click", function(){
            RainInstaller.remove( $(this).parent().attr("class") );
        });
    },
    
    download:function(classes){
        var module = RainInstaller.get_selected_module(classes)
        $.get( ajax_file + "installer/download/"+module, function( html ){
            location.reload(true);
        });
    },
    activate:function(classes){
        var module = RainInstaller.get_selected_module(classes)
        $.get( ajax_file + "installer/activate/"+module, function( html ){
            location.reload(true);
        });
    },
    deactivate:function(classes){
        var module = RainInstaller.get_selected_module(classes);   
        $.get( ajax_file + "installer/deactivate/"+module, function( html ){
            location.reload(true);
        });
    },
    remove:function(classes){
        var module = RainInstaller.get_selected_module(classes);
        $.get( ajax_file + "installer/remove/"+module, function( html ){
            location.reload(true);
        });
    },
    get_selected_module: function(classes){
        var preg = /module_(.*)/
        var match = classes.match( preg )
        return match[1];
    }
}

RainInstaller.init();