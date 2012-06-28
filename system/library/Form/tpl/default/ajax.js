    debug:true,
    submitHandler: function(form){
        $('#{$name}_loading').fadeIn('slow')
        $('#{$name}').hide()
        $('#{$name}_result').html('').hide()
        $(form).ajaxSubmit({
            target: '#{$name}_result',
            complete:function( data ){
                $('#{$name}_loading').slideUp('slow', function(){
                    $('#{$name}').fadeIn('slow');
                    $('#{$name}_result').fadeIn('slow');
                });
            }
        });
    }