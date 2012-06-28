<?php

    /**
    * Linked copy of this content
    */
    function cp_linked_copy($name, $value, $param, $validation, $cp) {

        $content_id = $param['content_id'];

        $attributes = "";
        if (is_array($param))
            foreach ($param as $attr => $val)
                if ($attr != 'options' && $attr != 'content_id')
                    $attributes .= " $attr=\"$val\"";
        $checkbox = '<div style="border: 1px dotted #ccc; padding: 10px; background: #fff; width:95%;" >';
        if (isset($param['options']) && ($options = $param['options']))
            foreach ($options as $v => $n) {

                $checkbox .= '<input type="hidden" name="' . $name . '[' . $v . ']" value="0"/> ';
                if ($content_id == $v)
                    $checkbox .= '<div style="display:inline;margin-right:10px; line-height: 30px; white-space:nowrap;"><input type="checkbox" checked="checked" id="chk' . ($name . $v) . '" name="' . $name . '[' . $v . ']" ' . $attributes . ' value="1"/> <label for="chk' . ($name . $v) . '"><b>' . $n . '</b></label></div> ';
                elseif ($sel = $v == $value || (is_array($value) && isset($value[$v])))
                    $checkbox .= '<div style="display:inline;margin-right:10px; line-height: 30px; white-space:nowrap;"> <label for="chk' . ($name . $v) . '"><input type="checkbox" checked="checked" id="chk' . ($name . $v) . '" name="' . $name . '[' . $v . ']" ' . $attributes . ' value="1"/> <b>' . $n . '</b></label> <a href="?id=' . $value[$v] . '"><img src="' . ADM_TPL_IMG_DIR . 'edit.gif" class="tooltip" title="' . _CONTENT_BUTTON_CONTENT_CAPTION_ . '" alt="' . _CONTENT_BUTTON_CONTENT_ . '"/></a></div> ';
                else
                    $checkbox .= '<div style="display:inline;margin-right:10px; line-height: 30px; white-space:nowrap;"><input type="checkbox" id="chk' . ($name . $v) . '" name="' . $name . '[' . $v . ']" ' . $attributes . ' value="1"/> <label for="chk' . ($name . $v) . '">' . $n . '</label></div>';
            }
        $checkbox .= '</div>';

        return $checkbox;
    }

    // -- end