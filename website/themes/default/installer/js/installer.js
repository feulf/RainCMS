

var RainInstaller = {
    init:function(){
        $('.rain_module_download').live("click", function(){
            RainInstaller.download( $(this).parent().attr("class") );
        });
        $('.rain_module_install').live("click", function(){
            RainInstaller.install( $(this).parent().attr("class") );
        });
        $('.rain_module_unistall').live("click", function(){
            RainInstaller.unistall( $(this).parent().attr("class") );
        });
        $('.rain_module_remove').live("click", function(){
            RainInstaller.remove( $(this).parent().attr("class") );
        });
    },
    
    download:function(module){
        var preg = /module_(.*)/
        var match = module.match( preg )
        var module = match[1];
        
        $.get( ajax_file + "installer/download/"+module, function( html ){
            console.log( html );
        });
    },
    install:function(module){
        var preg = /module_(.*)/
        var match = module.match( preg )
        var module = match[1];
        
        $.get( ajax_file + "installer/install/"+module, function( html ){
            console.log( html );
        });
    },
    unistall:function(module){
        var preg = /module_(.*)/
        var match = module.match( preg )
        var module = match[1];
        
        $.get( ajax_file + "installer/unistall/"+module, function( html ){
            console.log( html );
        });
    },
    remove:function(module){
        var preg = /module_(.*)/
        var match = module.match( preg )
        var module = match[1];
        
        $.get( ajax_file + "installer/remove/"+module, function( html ){
            console.log( html );
        });
    }
}

RainInstaller.init();