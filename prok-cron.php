<?php

//var_dump( getTable(null));
//var_dump( getTable("ID,autoupdate,status,lastupdate","where status > 0"));
$arr = [];
$tbData = getTable("ID,autoupdate,status,lastupdate","where status > 0");

for($i=0;$i<count($tbData);$i++){
    $row = $tbData[$i];
    if((time() - $row->lastupdate) > $row->autoupdate ){
       // var_dump( getData($row->ID));
        $arr_proc = [];
        for ($j = 0; $j < 50; $j++) {
            $arr_proc[] = getProcessDataArr($row->ID, $j);
        }
        $url_arr = explode(";",getDataUrls($row->ID));
        var_dump($url_arr);
        $url = $url_arr[getOffset($row->ID)];
        echo "<br>".$url."<br>".;
        incrementOffset($row->ID);

        getContentToSave($url, $row->prok_begin,$row->prok_end,$row->title_preg,$arr_proc,false,$row->ingr_pr,$row->step_pr);
        var_dump( $Debug->getDebugData());
    }
}

?>