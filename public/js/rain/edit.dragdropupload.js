var RainDragDropUpload = {
    
    dropbox:'',
    message:'',
    init_drag_drop_upload: function(){

        $('footer').append('<div class="message"></div>');

        this.dropbox = $('.content>.text');
        this.message = $('.message', this.dropbox);
	this.dropbox.filedrop({
		/* The name of the $_FILES entry: */
		paramname:'file',
		maxfiles: 5,
                maxfilesize: 2,
		url: ajax_file + 'rain_edit/upload_image_content/'+content_id,
		uploadFinished:function(i,file,response){
			$.data(file).addClass('done');
			/* response is the JSON object that post_file.php returns */
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
                
                docOver: function(file){
                },

		/* Called before each upload is started */
		beforeEach: function(file){
                    
                    var date = new Date();
                    this.image_id = date.getTime();

			if(!file.type.match(/^image\//)){
				alert('Only images are allowed!');

				/* Returning false will cause the
				   file to be rejected */
				return false;
			}
		},
		
		uploadStarted:function(i, file, len){
			RainDragDropUpload.createImage(file);
		},
		
		progressUpdated: function(i, file, progress) {
			/*$.data(file).find('.progress').width(progress);*/
		}
    	 
	});

    },

    template :'<div class="preview" style="border:3px solid #ccc;background:#eee;width:100%;height:100px;">'+
              '<img />'+
              '<div class="progress"></div>'+
              '</div>',

    createImage: function (file){

            var preview = $(this.template),
            image = $('img', preview);

            var reader = new FileReader();

            image.width = 100;
            image.height = 100;

            reader.onload = function(e){
                    /* e.target.result holds the DataURL which
                    can be used as a source of the image: */

                    image.attr('src',e.target.result);
            };

            /* Reading the file as a DataURL. When finished,
               this will trigger the onload function above:
            */
            reader.readAsDataURL(file);

            this.message.hide();
            preview.appendTo(this.dropbox);

            /* Associating a preview container
               with the file, using jQuery's $.data():
            */

            $.data(file,preview);
            RainEdit.enable_save_changes_button();
    },

    showMessage :function (msg){
            this.message.html(msg);
    }
}

RainDragDropUpload.init_drag_drop_upload();