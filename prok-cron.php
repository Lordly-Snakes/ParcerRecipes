<?php

//var_dump( getTable(null));
//var_dump( getTable("ID,autoupdate,status,lastupdate","where status > 0"));
$arr = [];
$tbData = getTable("*","where status > 0");
echo count($tbData);
for($i=0;$i<count($tbData);$i++){
    $row = $tbData[$i];
    if((time() - $row->lastupdate) > $row->autoupdate ){

        $arr_proc = [];
        for ($j = 0; $j < 50; $j++) {
            $arr_proc[] = getProcessDataArr($row->ID, $j);
        }
        $url_arr = explode(";",getDataUrls($row->ID));
        for($j=0;$j<count($url_arr);$j++){
            echo $url_arr[$j]."<br>";
        }
        $url = $url_arr[getOffset($row->ID)];
        echo "<br>".$url."<br>";
        incrementOffset($row->ID);
       // var_dump(($arr_proc));
       echo(htmlentities( $row->title_preg));
        echo getContentToSave($url, $row->prok_begin,$row->prok_end,$row->title_preg,$arr_proc,false,$row->ingr_pr,$row->step_pr);
       // var_dump( $Debug->getDebugData());
    }
}

?>