<?php 

	session_start();	
	define( "RAINCMS", 1 );				// security check

	define ("ROOT_DIR", "../../../../../../../" );
	include ROOT_DIR . "config/dir.php";
	include ROOT_DIR . CONSTANTS_DIR . "constants.php";
	include ROOT_DIR . LIBRARY_DIR . "functions.php";

	$cp 	= get('cp') ? get('cp') : 'content';
	$module = get('module') ? get('module') : 'content';

	$param = get();
	$param_encoded = "";
	foreach( $param as $k => $v )
		if( $k != 'cp' && $k != 'module' && $k != 'css' ){
			$v = str_replace( "'", "\\'", $v );
			$param_encoded .= $param_encoded ? ",$k:'$v'" : "$k:'$v'";
		}
	
?><!DOCTYPE html>
<html>
    <head>
	<title>upload</title>
        
        <script src="<?php echo ROOT_DIR . JQUERY_DIR; ?>/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo ROOT_DIR . ADMIN_JAVASCRIPT_DIR; ?>ajaxupload.js" type="text/javascript"></script>
        <script src="<?php echo ROOT_DIR . ADMIN_JAVASCRIPT_DIR; ?>file.js" type="text/javascript"></script>
        <script src="<?php echo ROOT_DIR . ADMIN_JAVASCRIPT_DIR; ?>content.js" type="text/javascript"></script>
        <script src="<?php echo ROOT_DIR . LIBRARY_DIR; ?>Form/plugins/tiny_mce/tiny_mce_popup.js" type="text/javascript"></script>

  	<!-- javascript -->
	<script type="text/javascript">

            $(document).ready( function(){
                new AjaxUpload('#upload_cover_button', {
                                action: '<?php echo ROOT_DIR; ?>admin.ajax.php/Content/upload_image_content',
                                name: 'file',
                                autoSubmit: false,
                                responseType: 'json',
                                onChange: function( file, ext ){
                                                                <?php echo "this.setData({ $param_encoded });"; ?>
                                                                if( ext != 'jpg' && ext != 'jpeg' && ext != 'png' && ext != 'gif' )
        								return alert( 'File not allowed' );
                    						this.submit()
                                                               },
				onComplete: function( file, response ){
                                                    if( response['result'] )
                                                        file_insert_content( response['file_id'], response['dir'], response['filepath'] )
                                                    else
                                                        alert( response['msg'] )
				}
		});
            });
		</script>
</head>
    <body style="margin:10px;">
        <div id="upload_cover_button" class="upload_cover_button"><img src="img/upload.gif" alt="upload"/></div>    
    </body>
</html>
