<?php

    session_start();
    define("RAINCMS", 1);                // security check

    define("RELATIVE_BASE_DIR", "../../../../../");

    $url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/" . RELATIVE_BASE_DIR;
    $url = reduce_path_upload( $url );
    
    function reduce_path_upload( $path ){
            $path = str_replace( "://", "@not_replace@", $path );
            $path = preg_replace( "#(/+)#", "/", $path );
            $path = preg_replace( "#(/\./+)#", "/", $path );
            $path = str_replace( "@not_replace@", "://", $path );
            
            while( preg_match( '#\.\./#', $path ) ){
                $path = preg_replace('#\w+/\.\./#', '', $path );
            }
            return $path;
    }
    
    define( URL, 'http://'.$url );

    
    include RELATIVE_BASE_DIR . "config/dir.php";
    include RELATIVE_BASE_DIR . "config/url.php";
    include RELATIVE_BASE_DIR . "system/const/constants.php";
    include RELATIVE_BASE_DIR . "system/const/rain.constants.php";
    include RELATIVE_BASE_DIR . "system/library/functions.php";
    define("RAIN_UPLOAD_PLUGIN_URL", LIBRARY_URL . "Form/plugins/cover/" );
?>

    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>upload</title>

            <link href="<?php echo RAIN_UPLOAD_PLUGIN_URL; ?>cover.css" type="text/css" rel="stylesheet"/>

            <!-- included javascript -->
            <script src="<?php echo RELATIVE_BASE_DIR; ?>public/js/jquery/jquery.min.js" type="text/javascript"></script>
            <script src="<?php echo RELATIVE_BASE_DIR; ?>public/js/jquery/ui/jquery.ui.core.js" type="text/javascript"></script>
            <script src="<?php echo RELATIVE_BASE_DIR; ?>admin/library/web/js/ajaxupload.js" type="text/javascript"></script>
            <!-- javascript -->
            <script type="text/javascript">

                $(document).ready( function(){
                    new AjaxUpload('#upload_cover_button', {
                        action: '<?php echo RELATIVE_BASE_DIR; ?>admin.ajax.php/Content/cover_upload',
                        name: 'cover',
                        autoSubmit: true,
                        responseType: 'json',
                        onChange: function(file, ext){
                            this.setData({ content_id: '<?php echo get_post('content_id'); ?>' });
                            if( ext != 'jpg' && ext != 'jpeg' && ext != 'png' && ext != 'gif' )
                                return alert( 'File not allowed' );
                            this.submit()
                        },
                        onComplete: function( file, response ){
                            if( response )
                                window.parent.cover_update( response['src'], response['thumb_src'], '<?php echo get('content_id'); ?>', '<?php echo RAIN_UPLOAD_PLUGIN_URL; ?>' )
                        }
                    });
                });
            </script>
        </head>
        <body class="cover-plugin-inside">
            <div  class="upload_cover_button">
                <button id="upload_cover_button" class="cover-upload">Upload Cover</button>
            </div>
            <a class="cover-choose" href="javascript:parent.cover_choose(<?php echo get('content_id'); ?>)">Choose a cover</a>
        </body>
    </html>
