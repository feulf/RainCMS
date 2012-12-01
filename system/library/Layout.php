<?php

class Layout {

    protected static
            $var = array(),
            $conf = array(),
            /* CSS and Javascript variables */
            $script             = array(), // Included scripts
            $style              = array(), // Included styles
            $javascript         = '',
            $javascript_onload  = '';

    public static function assign($variable, $value = null) {
        if (is_array($variable))
            static::$var = $variable + static::$var;
        else
            static::$var[$variable] = $value;
    }

    public static function draw($template = null, $return_string = false) {
        $view = new View;
        $view->assign(static::$var);
        return $view->draw($template ? : static::$conf['page_layout'], $return_string);
    }

    public static function addStyle( $file, $dir = CSS_DIR, $url = null, $attr = array()) {
        if (!$url)
            $url = URL . $dir;
        self::$style[$dir . $file] = array("url" => $url . $file) + $attr;
    }

    public static function addScript($file, $dir = JAVASCRIPT_DIR, $url = null, $attr = array()) {
        if (!$url)
            $url = URL . $dir;
        self::$script[$dir . $file] = array("url" => $url . $file) + $attr;
    }

    public static function addJavascript($javascript, $onload = false) {
        if (!$onload)
            self::$javascript .= "\n" . $javascript . "\n";
        else
            self::$javascript_onload .= "\n" . $javascript . "\n";
    }

    public static function getJavascript( $compress = false ) {
        $html = "";
        if ( self::$script) {
            
            foreach (self::$script as $s) {
                $attr = '';
                foreach ($s as $key => $value){
                    if ($key != "url")
                        $attr .= $key . '="' . $value . '" ';
                }
                $html .= '<script src="' . $s['url'] . '" type="text/javascript" ' . $attr . '></script>' . "\n";
            }
        }

        if (self::$javascript)
            $html .= "<script type=\"text/javascript\">" . "\n" . self::$javascript . "\n" . "</script>";

        return $html;
    }

    public static function getJavascriptOnload() {
        if (self::$javascript_onload)
            return "<script type=\"text/javascript\">" . "\n" . self::$javascript_onload . "\n" . "</script>";
    }

    public static function getStyle() {
        $html = "";
        if (self::$style) {
            foreach (self::$style as $s) {
                $attr = '';
                foreach ($s as $key => $value)
                    if ($key != "url")
                        $attr .= $key . '="' . $value . '" ';
                $html .= '<link href="' . $s['url'] . '" rel="stylesheet" type="text/css" ' . $attr . '/>' . "\n";
            }
        }
        return $html;
    }

}