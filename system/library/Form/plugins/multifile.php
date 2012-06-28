<?php

    /**
    * Disegna un form data. Richiama questa funzione usando additem( 'date', ...
    * Parametri aggiuntivi:
    * maxLength: definisce il limite di caratteri che posso scrivere nel campo
    * size: definisce la dimensione in caratteri del campo
    */
    addScript("multifile.js", RAINCP_DIR . "plugins/");

    function cp_multifile($name, $value, $parameters, $validation, $cp) {


        $mail_id = $parameters['mail_id'];
        $dir = $parameters['dir'];
        $cp->uploadFile = true;

        $files = '';
        for ($i = 0; $i < count($value); $i++)
            $files .= '<span class="file_' . $i . '">' .
                    '<img src="' . ADM_TPL_IMG_DIR . 'upload.gif"> <a href="' . $dir . $value[$i] . '" target="_blank">' . $value[$i] . '</a> ' .
                    '<a target="actioniframe" href="admin.server.php?module=mailer&cp=file_del&mail_id=' . $mail_id . '&file=' . $value[$i] . '" onclick="$(\'#file_' . $i . '\').remove();"><img src="' . ADM_TPL_IMG_DIR . 'del.gif"></a></span>';

        $html = '<div class="multifile">
                        <div id="files_list">' . $files . '</div>
                        <input id="my_file_element" type="file" name="file_1" value="" >
                        <script>
                            <!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
                            var multi_selector = new MultiSelector( document.getElementById( \'files_list\' ), 3 );
                            <!-- Pass in the file element -->
                            multi_selector.addElement( document.getElementById( \'my_file_element\' ) );
                        </script>
                    </div>
                    ';

        return $html;
    }

    // -- end