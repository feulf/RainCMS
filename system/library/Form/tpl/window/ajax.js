    debug:true,
    submitHandler: function(form){
        $('#{$name}_loading').fadeIn()
        $('#{$name}').hide()
        $('#{$name}_result').html('').hide()
        $(form).ajaxSubmit({
            target: '#{$name}_result',
            complete:function( data ){
                $('#{$name}_loading').fadeOut('fast', function(){
                    $('#{$name}').fadeIn();
                    $('#{$name}_result').fadeIn();
                });
            }
        });
    }