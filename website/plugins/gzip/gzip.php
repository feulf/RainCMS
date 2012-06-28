<?php

    // Gzip plugin
    // actions
    add_action("plugin_loaded", "gzip", "enable");

    class GzipPlugin extends Plugin{

        var $loader;

        static function enable() {
            if (extension_loaded('zlib')) {
                ob_start('ob_gzhandler');
                add_action("after_draw", "gzip", "flush");
            }
        }

        static function flush() {
            ob_end_flush();
        }

        

    }

// -- end