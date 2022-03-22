<?php
add_action('admin_enqueue_scripts', 'prok_enqueue_custom_js');
function prok_enqueue_custom_js() {
    wp_enqueue_script('custom', plugins_url('prok-parcer/admin/js/prok-parcer-admin.js'),array('jquery'), false, true);
    wp_enqueue_style( "prok", plugins_url('prok-parcer/admin/css/prok-parcer-admin.css'));

}

add_action('admin_menu', 'mt_add_pages');
// action function for above hook
function mt_add_pages() {
    //-------- Add a new top-level menu (ill-advised):
    // Здесь устанавливается функция отображения
    add_menu_page('parcer resipes settings', 'Parcer resipes settings','activate_plugins','prok','prok_menu_display');
    //--------
}

add_action( 'wp_ajax_prok_del', 'prok_del' );
function prok_del(){
    $id = $_POST['id'];
    deleteData($id);
    for($i=0;$i<50;$i++){
        deleteProcessData($id,$i);
    }
    echo "OK";
    wp_die();
}

add_action('wp_ajax_prok_get_urls','getUrls');
function getUrls(){
    global $Debug;
    deleteAllTestImage( "prok-test-uploads");
    $url = $_POST['url'];
    $id = getPost('id');
    $begin = $_POST['beginCon'];
    $end = $_POST['endCon'];
    $process_arr = json_decode(stripslashes($_POST["process"]));
    $arr = getUrlsInDocument($url,$begin,$end,$process_arr);
    $Debug->addDebugData($id);
    $str ="";
    for($i=0;$i<count($arr);$i++){
        $str.=$arr[$i].";";
    }
    if(getDataUrls($id) != $str){
        updateDataUrls($id,$str);
    }
    standartResponse(100, 'URLS_OK', ($arr));
}

add_action( 'wp_ajax_prok_action', 'prok_action_callback' );
function prok_action_callback(){
    global $Debug;
    $res_str = "";
    error_log("---------------------------------------------------------------START--simple");

    $id = getPost("id");
    $url_arr = explode(";",getDataUrls($id));
    $url = $url_arr[getOffset($id)];
    $beginw = $_POST['begin'];
    $endw = $_POST['end'];
    $test = $_POST['test'];
    $title = $_POST['title'];
    $Debug->addDebugData($url);
    $ingr_pr = stripslashes(getPost('ingr_pr'));
    $step_pr = stripslashes(getPost('step_pr'));
    $timeAuto = getPost('autopost');
    $process_arr = json_decode(stripslashes($_POST["process"]));
    $res_str = $res_str."<div class=\"res-content\">";
    if($test){
        $res_str =$res_str.getContentToSave($url,$beginw,$endw,$title,$process_arr,true,$ingr_pr,$step_pr);
    }else{
        incrementOffset($id);
        $res_str = $res_str.getContentToSave($url,$beginw,$endw,$title,$process_arr,false,$ingr_pr,$step_pr);
    }
    $res_str = $res_str."</div>";
    // выход нужен для того, чтобы в ответе не было ничего лишнего,
    // только то что возвращает функция
    standartResponse(100,'DATA_OK',($res_str),null);


}

function getPost($nameData){
    return $_POST[$nameData];
}

add_action( 'wp_ajax_prok_save', 'prok_save' );
function prok_save(){
    global $Debug;
    $id = getPost('id');
    $url = getPost('url');
    $begin = getPost('beginCon');
    $end = getPost('endCon');
    $beginC = getPost('begin');
    $endC = getPost('end');
    $name = getPost('name');
    $title = getPost('title');
    $ingr_pr = getPost('ingr_pr');
    $step_pr = getPost('step_pr');
    $timeAuto = getPost('autopost');
    $cat = getPost("cat");
    $count_add_post=getPost('countAddPost');
    $first_number =getPost('firstNumber');
    $process_arr = json_decode(stripslashes($_POST["process"]));
    $begin = str_replace('\"','"',$begin);
    $end = str_replace('\"','"',$end);
    $beginC = str_replace('\"','"',$beginC);
    $endC = str_replace('\"','"',$endC);
    $title = stripslashes($title);
    $ingr_pr = stripslashes($ingr_pr);
    $step_pr = stripslashes($step_pr);
    $timeAuto = stripslashes($timeAuto);
    $status = getPost("status");
    $Debug->addDebugData(["status",$status]);
    $status = $status == "true" ? 1 : 0;

    updateData($id,$url,$begin,$end,$beginC,$endC,$name,$title,$ingr_pr,$step_pr,$timeAuto,$count_add_post,$first_number,$cat,$status);
    for($i=0;$i<50;$i++){
        $obj = getProcessData($id,$i);
        /*
            $process_arr[$i] = array(4) {
                [0]=>string(6) "search"
                [1]=>string(5) "index"
                [2]=>string(4) "name"
                [3]=>string(11) "replacement"
            }
        */
        $val = str_replace('\"','"',$process_arr[$i][0]);
        $replacement = str_replace('\"','"',$process_arr[$i][3]);
        $status = $process_arr[$i][1];
        if(!is_null($obj) && $val != ""){
            updateProcessData($id,$val,$i,$status,$replacement);
        }else if(is_null($obj) && $val != ""){
            addProcessData($id, $val, $i, $status, $replacement);
        }else if(!is_null($obj) && $val == ""){
            deleteProcessData($id,$i);
        }
    }
    //var_dump($process_arr);
    //echo "OK";

    standartResponse(100,'DATA_SAVED','data saved');
    wp_die();
}

?>