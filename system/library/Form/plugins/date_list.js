function del_date(date_list_class){
    $( '#date_list .'+date_list_class ).slideUp("slow", function(){$(this).remove();});
}

function add_date( start_time, end_time ){

    var n_rows = $('#date_list div').length;
    var start_date = get_date(start_time);
    var end_date = get_date(end_time);
    var date_list_class = 'date_' + (n_rows+1);
    
    var html = '';
    html =  '<div class="'+date_list_class+'">';
    html += '   <div class="place_holder"></div>';
    html += '   <input type="text" class="start_date" name="start_date[]" id="" value="' + start_date + '">';
    html += '   <input type="text" name="end_date[]" class="end_date" value="'+end_date+'"> ';
    html += '   <a href="javascript:del_date(\''+date_list_class+'\')"><img src="'+admin_views_images_url+'del.gif" alt="del"></a>';
    html += '</div>';

    $('#date_list').append( html );

    calendar_execute( "#date_list ."+date_list_class+" .start_date" )
    calendar_execute( "#date_list ."+date_list_class+" .end_date" )
}

function calendar_execute( name ){
    $(name).datepicker( {yearRange:"-70:0"});
    $('#date_list').sortable({
                                handle:'.place_holder'
                             });
}

function get_date( time ){
    if( time )
        var date = new Date( time * 1000 );
    else
        var date = new Date();
    
    var month = date.getMonth() + 1;
    if( month < 10 ) month = "0" + month;
    var day = date.getDate();
    if( day < 10 ) day = "0" + day;
    return month + "/" + day + "/" + date.getFullYear()
}