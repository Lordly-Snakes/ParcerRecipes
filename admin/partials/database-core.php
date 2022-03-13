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

function getData($id){
    global $wpdb;
    $query = $wpdb->prepare('SELECT * FROM prok_table where ID=%d',$id);
    $obj = $wpdb->get_row( $query, OBJECT, 0 );
    return $obj;
}

function updateData($id,$index_url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$name,$title,$ingr_pr,$step_pr,$autoupdate,$count_add_post,$first_number,$cat){
    global $wpdb;
    $query = $wpdb->prepare('UPDATE prok_table SET index_url=%s,prok_begin_index=%s,prok_end_index=%s,prok_begin=%s,prok_end=%s,name=%s,title_preg=%s,ingr_pr=%s,step_pr=%s,autoupdate=%s,count_add_post=%s,first_number=%s,category=%d where ID=%d',
        [$index_url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$name,$title,$ingr_pr,$step_pr,$autoupdate,$count_add_post,$first_number,$cat,$id]);
    $wpdb->query( $query);
    //lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}

function updateDataUrls($id,$urls){
    global $wpdb;
    $query = $wpdb->prepare('UPDATE prok_table SET urls=%s where ID=%d',  [$urls,$id]);
    $wpdb->query( $query);
    //lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}

function deleteData($id){
    global $wpdb;
    $query = $wpdb->prepare('DELETE FROM prok_table where ID=%d',$id);
    $wpdb->query( $query);
    //lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}

?>