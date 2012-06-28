<?php

    /**
    * Disegna un form data. Richiama questa funzione usando additem( 'date', ...
    * Parametri aggiuntivi:
    * maxLength: definisce il limite di caratteri che posso scrivere nel campo
    * size: definisce la dimensione in caratteri del campo
    */
    function form_map($name, $value, $parameters, $cp) {

        if (!$value)
            $value = get_setting('website_address');

        add_javascript("

                            var map, stepDisplay, marker, old_position, old_address = '" . $value . "';

                            function initMap(){

                                $('#map_canvas').css('height', '500px');
                                $('#reset_map').show();

                                var myOptions = { zoom: 13, mapTypeId: google.maps.MapTypeId.ROADMAP, };
                                map = new google.maps.Map(document.getElementById('map_canvas'), myOptions );

                                address = $('#addr').val()
                                geocoder = new google.maps.Geocoder();
                                geocoder.geocode( { 'address': address}, function(results, status) {
                                    var location = results[0].geometry.location
                                    old_position = location
                                    map.setCenter( location );
                                    addMarker( location )
                                });

                            }

                            function resetLocation(){
                                $('#addr').val( old_address )
                                address = old_address
                                geocoder = new google.maps.Geocoder();
                                geocoder.geocode( { 'address': address}, function(results, status) {
                                    var location = results[0].geometry.location
                                    old_position = location
                                    map.setCenter( location );
                                    addMarker( location )
                                });
                            }

                            function addMarker( location ){
                                if( marker )
                                    marker.setMap(null)
                                marker = new google.maps.Marker({ position: location, map: map, draggable: true });

                                google.maps.event.addListener(marker, 'dragstart', function() {
                                    //map.closeInfoWindow();
                                });

                                google.maps.event.addListener(marker, 'dragend', function(event) {
                                    updateLocation( event.latLng )
                                });
                            }

                            function updateLocation(location){
                                var p = location.toString()
                                var latlngStr = p.split(',');
                                var lat = latlngStr[0].substr(1)
                                var lng = latlngStr[1].substr(0,latlngStr[1].length-1)

                                $('#location').val(lat + ',' + lng);

                                geocoder.geocode({'latLng': location}, function(results, status) {
                                if (status == google.maps.GeocoderStatus.OK)
                                    if (results[0]){

                                        var address = results[0].formatted_address
                                        var address_components = new Array();
                                        for( var i in results[0].address_components )
                                            address_components[results[0].address_components[i].types] = results[0].address_components[i].long_name

                                        $('#addr').val(results[0].formatted_address)
                                    }

                                });

                            }

                            ");

        $html = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
        $html .= '<div id="map_canvas"><a href="javascript:initMap();">Click to init the map</a></div>';
        $html .= '<input type="hidden" id="location" name="extra3"/><br>address <input type="text" name="extra2" style="width:76%;" id="addr" value="' . $value . '" onchange="initMap()"/> <a href="javascript:initMap();">Refresh</a>';
        $html .= '<div id="reset_map" style="display:none;"><br><a href="javascript:resetLocation();">Reset Address</a><br></div>';
        return $html;
    }

    // -- end
