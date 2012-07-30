/* draw a modal */
var RainPopup = {
    id:0,
    init: function( title, html ){
        Rain.add_css( css_url + "rain.edit.css" );
        this._loadPopup(title, html);
        this.id = "rain_popup_" + (new Date().getTime());
        $(window).resize(function(){
            RainPopup._position();
        });
        
    },
    html: function(html){
        $('.rain_popup_window_content').fadeOut("fast", function(){
            $(this).html( html ).show();
            RainPopup._position();
        });
    },
    title: function(title){
        $('.rain_popup_window_title').html(title);
    },
    append: function(html){
        $('.rain_popup_window_content').append(html);
    },
    popupClose: function(){
        $('.rain_popup').fadeOut("fast", function(){
            $("body").css("overflow","visible" );
            $(this).remove();
        })
    },
    width: function(width){
        $('.rain_popup_window').css({
            width: width,
            left:-width/2
        });
        this._position();
    },
    height: function(height){
        $('.rain_popup_window').css({
            height: height,
            top:-height/2
        });
        this._position();
    },
    _position: function(){
        $('.rain_popup_window').css({
            top: $(window).height()/2 - $('.rain_popup_window').height()/2,
            left: $(window).width()/2 - $('.rain_popup_window').width()/2
        });
    },
    _loadPopup: function(title, html, width, height){
        if( !html ){ html = ""; }
        if( !width ){ width = 500; }
        if( !height ){ height = 300; }
        $("body").css("overflow","hidden");
        $('body').append('<div class="rain_popup new_content" id="'+this.id+'"><div class="rain_popup_bg"></div><div class="rain_popup_window"><div class="rain_popup_close"></div><h1 class="rain_popup_window_title">'+title+'</h1><div class="rain_popup_window_content">'+html+'</div></div>');
        $('.rain_popup_bg').click( function(){
            RainPopup.popupClose();
        });
        $('.rain_popup_close').click( function(){
            RainPopup.popupClose();
        });
        
        $('.rain_popup').show();
        this._position();

    }
};
