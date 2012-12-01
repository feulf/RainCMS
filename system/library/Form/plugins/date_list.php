<?php

    /**
    * Form Date list plugin
    */
    function form_date_list($name, $value, $parameters, $cp) {

        Layout::addScript("ui/ui.datepicker.js", JQUERY_DIR, JQUERY_URL );
        Layout::addScript( "date_list.js", LIBRARY_DIR . "Form/plugins/" );
        Layout::addStyle("themes/smoothness/ui.all.css", JQUERY_DIR, JQUERY_URL);
        Layout::addStyle("date_list.css", LIBRARY_DIR . "Form/plugins/" );

        $time = time();
        $date = time_format($time, DATE_FORMAT);
        $content_id = $parameters['content_id'];

        // get the date list
        $date_list = Content::get_content_date_list($content_id);


        $html = '<div id="date_list">';

        for ($i=0, $n=count($date_list); $i < $n; $i++) {
            $start_time = $date_list[$i]['start_time'];
            $end_time = $date_list[$i]['end_time'];
            Layout::addJavascript("add_date( '$start_time', '$end_time' )", $onLoad = true);
        }

        $html .= "</div>";
        $html .= "<div class=\"add_date_list\"><a href=\"javascript:add_date()\"><img src=\"" . ADMIN_VIEWS_IMAGES_URL . "add.gif\" alt=\"add\">" . get_msg("add_event_date") . "</a></div>";

        return $html;
    }

    // -- end
