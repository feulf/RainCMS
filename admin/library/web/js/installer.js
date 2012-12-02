var RainInstaller = {
    init:function(){
        $('.rain_module_download').live("click", function(){
            RainInstaller.download( $(this).attr("class") );
        });
        $('.rain_module_activate').live("click", function(){
            RainInstaller.activate( $(this).attr("class") );
        });
        $('.rain_module_deactivate').live("click", function(){
            RainInstaller.deactivate( $(this).attr("class") );
        });
        $('.rain_module_remove').live("click", function(){
            RainInstaller.remove( $(this).attr("class") );
        });
    },
    
    download:function(classes){
        var module = RainInstaller.get_selected_module(classes)
        $.get( ajax_file + "configure/module_download/"+module, function( html ){
            location.reload(true);
        });
    },
    activate:function(classes){
        var module = RainInstaller.get_selected_module(classes)
        $.get( ajax_file + "configure/module_activate/"+module, function( html ){
            location.reload(true);
        });
    },
    deactivate:function(classes){
        var module = RainInstaller.get_selected_module(classes);   
        $.get( ajax_file + "configure/module_deactivate/"+module, function( html ){
            location.reload(true);
        });
    },
    remove:function(classes){
        var module = RainInstaller.get_selected_module(classes);
        $.get( ajax_file + "configure/module_remove/"+module, function( html ){
            location.reload(true);
        });
    },
    get_selected_module: function(classes){
        var preg = /[^\w]module_(.*)/;
        var match = classes.match( preg );
        return match[1];
    }
}

RainInstaller.init();