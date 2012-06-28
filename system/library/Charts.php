<?php

    class Charts {

        protected static $charts_count = 0;
        protected static $colors = array('#3366cc', '#dc3912', '#ff9900', '#109618', '#990099', '#0099c6', '#dd4477');

        /**
        * Load data from CSV file
        */
        function load_csv($file) {
            $file = file_get_contents($file);
            $rows = explode("\n", $file);
            for ($i = 0; $i < count($rows); $i++)
                $this->data[$i] = explode(",", $rows[$i]);
        }

        /**
        * Load data from array
        */
        function set_data($array) {
            $this->data = $array;
        }

        /**
        * Draw line
        */
        function draw_line($width = 750, $height = 350) {
            return $this->_draw('line', $width, $height);
        }

        /**
        * Draw pie
        */
        function draw_pie($width = 450, $height = 350) {
            return $this->_draw('pie', $width, $height);
        }

        private function _draw($chart = 'line', $width = 450, $height = 350) {

            $div_id = 'name' . self::$charts_count;
            $js = "";
            if (!self::$charts_count)
                $js .= $this->_init_script();

            self::$charts_count++;

            $js .= '<script>' . "\n";
            $js .= '    google.load("visualization", "1", {packages:["corechart"]});' . "\n";
            $js .= '    google.setOnLoadCallback(drawChart);' . "\n";
            $js .= '    function drawChart() {' . "\n";
            $js .= '        var data = new google.visualization.DataTable();' . "\n";

            $data = $this->data;
            $n_rows = count($data);

            // define the colors
            $colors = '[';
            foreach (self::$colors as $k => $v)
                $colors .= "'$v',";
            $colors = substr($colors, 0, -1);
            $colors .= "]";
            
            switch ($chart) {

                case 'pie':
                    // number of rows
                    $js .= "        data.addRows($n_rows);" . "\n";

                    // define column
                    $column = $data[0];
                    foreach ($column as $k => $v) {
                        $js .= is_numeric($v) ? "        data.addColumn('number', '$k' );" . "\n" : "        data.addColumn('string', '$k' );" . "\n";
                    }

                    // define the values
                    for ($i = 0; $i < $n_rows; $i++) {
                        $j = 0;
                        foreach ($data[$i] as $k => $v) {
                            $js .= is_numeric($v) ? "        data.setValue($i, $j, $v )" . "\n" : "        data.setValue($i, $j, '$v' )" . "\n";
                            $j++;
                        }
                    }
                    
                    $js .= "        var chart = new google.visualization.PieChart(document.getElementById('$div_id'));" . "\n";
                    $js .= "        chart.draw(data, {width: $width, height: $height, colors:$colors, is3D:true });" . "\n";

                    break;

                case 'line':
                    // number of rows
                    $js .= "        data.addColumn('string', '{$data[0][0]}' );" . "\n";
                    $js .= "        data.addRows(" . ( $n_rows - 1 ) . " );" . "\n";

                    //dump( $data );
                    $column = $data[0];
                    foreach ($column as $k => $v)
                        if ($k > 0)
                            $js .= "        data.addColumn('number', '$v' );" . "\n";

                    // define the values
                    for ($i = 1; $i < $n_rows; $i++) {
                        $j = 0;
                        foreach ($data[$i] as $k => $v) {
                            $js .= $k > 0 ? "        data.setValue(" . ($i - 1) . ", $j, $v )" . "\n" : "        data.setValue(" . ($i - 1) . ", $j, '$v' )" . "\n";
                            $j++;
                        }
                    }
                    
                    
                    $js .= "        var chart = new google.visualization.LineChart(document.getElementById('$div_id'));" . "\n";
                    $js .= "        chart.draw(data, {lineType: 'function', colors:$colors, width:$width, height: $height, vAxis: {maxValue: 10}} );" . "\n";

                    break;
            }

            $js .= '    }' . "\n";
            $js .= '</script>' . "\n";

            $html = '<div id="' . $div_id . '"></div>';

            return $js . $html;
        }

        function get_colors() {
            return self::$colors;
        }

        function set_colors($array) {
            self::$colors = $array;
        }

        function _init_script() {
            return '<script type="text/javascript" src="https://www.google.com/jsapi"></script>' . "\n";
        }

    }

    // -- end