<?php
function addProcessData($id_lent, $val, $num, $status, $replacement){
    global $wpdb;
    $wpdb->query( "INSERT INTO prok_process_table (id, id_lent, value, number_list,status,replacement) VALUES (NULL, '$id_lent', '$val', '$num', '$status', '$replacement')" );
}

function updateProcessData($id_lent,$val,$num,$status,$replacement){
    global $wpdb;
    $query = $wpdb->prepare('UPDATE prok_process_table SET value=%s,status=%s,replacement=%s where id_lent=%d and number_list=%d',[$val,$status,$replacement,$id_lent,$num]);
    $wpdb->query( $query);
}

function deleteProcessData($id_lent,$num){
    global $wpdb;
    $query = $wpdb->prepare('DELETE FROM prok_process_table where id_lent=%d and number_list=%d',[$id_lent,$num]);
    $wpdb->query( $query);
}

function getProcessData($id_lent,$num){
    global $wpdb;
    $query = $wpdb->prepare('SELECT * FROM prok_process_table where id_lent=%d and number_list=%d',[$id_lent,$num]);
    $obj = $wpdb->get_row( $query, OBJECT, 0 );
    return $obj;
}

function getProcessDataArr($id_lent,$num){
    global $wpdb;
    $query = $wpdb->prepare('SELECT * FROM prok_process_table where id_lent=%d and number_list=%d',[$id_lent,$num]);
    $obj = $wpdb->get_row( $query, ARRAY_N, 0 );
    return $obj;
}

function getTable($columnsName = null,$where = null){
    global $wpdb;
    $columnsName = is_null($columnsName) ? "*" : $columnsName;
    $where = is_null($where) ? " " : $where;
    return $wpdb->get_results( "SELECT $columnsName FROM prok_table $where", OBJECT );
}

function getData($id){
    global $wpdb;
    $query = $wpdb->prepare('SELECT * FROM prok_table where ID=%d',$id);
    return $wpdb->get_row( $query, OBJECT, 0 );
}

function updateData($id,$index_url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$name,$title,$ingr_pr,$step_pr,$autoupdate,$count_add_post,$first_number,$cat,$status){
    global $wpdb;
    $query = $wpdb->prepare('UPDATE prok_table SET index_url=%s,prok_begin_index=%s,prok_end_index=%s,prok_begin=%s,prok_end=%s,name=%s,title_preg=%s,ingr_pr=%s,step_pr=%s,autoupdate=%s,count_add_post=%s,first_number=%s,category=%d,status=%s where ID=%d',
        [$index_url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$name,$title,$ingr_pr,$step_pr,$autoupdate,$count_add_post,$first_number,$cat,$status,$id]);
    $wpdb->query( $query);
    //lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}

function updateDataUrls($id,$urls){
    global $wpdb;
    $query = $wpdb->prepare('UPDATE prok_table SET urls=%s where ID=%d',  [$urls,$id]);
    $wpdb->query( $query);
    //lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}

function getDataUrls($id): ?string{
    global $wpdb;
    $query = $wpdb->prepare('SELECT urls FROM prok_table where ID=%d',  [$id]);
    return $wpdb->get_var($query);
}

function getOffset($id){
    global $wpdb;
    $query = $wpdb->prepare('SELECT offset,first_number FROM prok_table where ID=%d',  [$id]);
    $obj = $wpdb->get_row( $query,OBJECT);
    return $obj->offset + $obj->first_number;
}



function incrementOffset($id){
    global $wpdb;
    $offset = getOffset($id) + 1;
    $query = $wpdb->prepare('UPDATE prok_table SET offset=%d where ID=%d',  [$offset,$id]);
    $wpdb->query( $query);
}

function deleteData($id){
    global $wpdb;
    $query = $wpdb->prepare('DELETE FROM prok_table where ID=%d',$id);
    $wpdb->query( $query);
    //lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}

?>