<?php

    // add style
    add_style("default.css", LIBRARY_DIR . "Tab/tpl/", LIBRARY_URL . "Tab/tpl/");

    // add javascript
    add_script("jquery.min.js", JQUERY_DIR, JQUERY_URL);

    // add javascript code
    add_javascript("function tabSel(id){\n\$('.tabs_content').hide();\n\$('#tab_'+id).show();\n\$('.tabs_btn div').removeClass('selected');\n\$('#btn_'+id).addClass('selected');}");

    class Tab {

        var $html, $tab, $selTab;

        function add_tab($id, $html = null, $name, $caption = null, $url = null) {
            $this->tab[$id] = array('name' => get_msg($name), 'caption' => get_msg($caption), 'html' => $html, 'url' => $url);
        }

        function sel_tab($id) {
            $this->selTab = $id;
        }

        function draw($to_string = false) {

            $this->tab = array_reverse($this->tab);
            $this->selTab = $this->selTab ? $this->selTab : key($this->tab);

            $content = $tabs = "";
            foreach ($this->tab as $id => $tab) {

                extract($tab);

                if ($url)
                    $tabs .= '		<div onclick="document.location=\'' . $url . '\'" id="btn_' . $id . '" class="' . ($this->selTab == $id ? 'selected' : null) . ' ' . ($caption ? 'tooltip' : null) . '" title="' . $caption . '">' . $name . '</div>';
                else
                    $tabs .= '		<div onclick="tabSel(\'' . $id . '\');" id="btn_' . $id . '" class="' . ($this->selTab == $id ? 'selected' : null) . ' ' . ($caption ? 'tooltip' : null) . '" title="' . $caption . '">' . $tab['name'] . '</div>';
                $content .= '		<div id="tab_' . $id . '" class="tabs_content ' . ($this->selTab == $id ? 'cont_selected' : null) . '">' . $html . '</div>';
            }

            $html = "\n\n" .
                    '<div class="tabs">' . "\n" .
                    '	<div class="tabs_btn">' . "\n" .
                    '		' . $tabs . "\n" .
                    '	</div>' . "\n" .
                    '	<div class="tabs_contents">' . "\n" .
                    '		' . $content . "\n" .
                    '	</div>' . "\n" .
                    '</div>' . "\n";

            if ($to_string)
                return $html;
            else
                echo $html;
        }

    }

// -- end