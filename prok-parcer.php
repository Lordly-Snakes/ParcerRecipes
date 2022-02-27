<?php

/**
 * Plugin Name: prok-parcer
 * Description: Парсинг рецептов
 * Author:      Прокофьев Антон
 * Version:     Версия плагина, например 1.0
 */


function save_img_stn($url_image,$path_to_save,$name){
	file_put_contents($path_to_save."/".$name, file_get_contents($url_image));
}

function saveImgCurl($url_image, $path_to_save, $name){
	$ch = curl_init($url_image);
	$fp = fopen($path_to_save."/".$name, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
}

function getImageFromContent($content){
	if(preg_match_all("/src=\"(.*?)\"/is",$content,$matches) != NULL){
		//echo print_r($matches[1]);
		return $matches[1];
	}else{
		echo "image not found";
		return NULL;
	}
}

function saveImages($arr_images_urls, $path, $prefix_name){
	$images_urls = [];
	$images_urls_assoc = [];
    if($path == "prok-test-uploads"){
        deleteAllTestImage($path);
    }
	for($i=0;$i<count($arr_images_urls);$i++){
		$img_url = $arr_images_urls[$i];
        $prefix_name = str_replace(" ","-",$prefix_name);
		$name_tmp = $prefix_name."-".time()."-".$i;
		saveImgCurl($img_url,wp_upload_dir()['basedir']."/$path",$name_tmp);
		error_log($name_tmp);
		//echo "$img_url";
        //         /prok-$test-uploads/
		$images_urls[] = wp_upload_dir()['baseurl']."/$path/$name_tmp";
		$images_urls_assoc[$img_url] = wp_upload_dir()['baseurl']."/$path/$name_tmp";
	}
	//error_log("-----------------------------images=>".var_dump($images_urls));
	$arr = array($images_urls,$images_urls_assoc);
	return $arr;
}

function saveImagesAndAddToPost($post_id, $file, $desc = null , $thumb = false){
	global $debug; // определяется за пределами функции как true

	if( ! function_exists('media_handle_sideload') ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}

	// Загружаем файл во временную директорию
	$tmp = download_url( $file );

	// Устанавливаем переменные для размещения
	$file_array = [
		'name'     => basename( $file ),
		'tmp_name' => $tmp
	];

	// Удаляем временный файл, при ошибке
	if ( is_wp_error( $tmp ) ) {
		$file_array['tmp_name'] = '';
		if( $debug ) echo 'Ошибка нет временного файла! <br />';
	}

	// проверки при дебаге
	if( $debug ){
		//echo 'File array: <br />';
		//var_dump( $file_array );
		//echo '<br /> Post id: ' . $post_id . '<br />';
	}

	$id = media_handle_sideload( $file_array, $post_id, $desc );

	// Проверяем работу функции
	if ( is_wp_error( $id ) ) {
		var_dump( $id->get_error_messages() );
	} else {
		if($thumb){
			update_post_meta( $post_id, '_thumbnail_id', $id );	
		}
	}

	// удалим временный файл
	@unlink( $tmp );
}

add_action( 'wp_ajax_prok_action', 'prok_action_callback' );
function prok_action_callback(){
	error_log("---------------------------------------------------------------START--simple");

	$url = $_POST['url'];
	$begin = $_POST['beginCon'];
	$end = $_POST['endCon'];
	$beginw = $_POST['begin'];
	$endw = $_POST['end'];
	$test = $_POST['test'];
    $title = $_POST['title'];

	$process_arr = json_decode(stripslashes($_POST["process"]));
	
	$arr = getHrefs2($url,$begin,$end,$process_arr);
    echo "<div class=\"res-content\">";
	if($test){
		getContentToSave($arr[0],$beginw,$endw,$title,$process_arr,true);
	}else{
		getContentToSave($arr[0],$beginw,$endw,$title,$process_arr,false);
	}
    echo "</div>";	

	// выход нужен для того, чтобы в ответе не было ничего лишнего,
	// только то что возвращает функция
	wp_die();
}

add_action( 'wp_ajax_prok_save', 'prok_save' );
function prok_save(){
	$id = $_POST['id'];
	$url = $_POST['url'];
	$begin = $_POST['beginCon'];
	$end = $_POST['endCon'];
	$beginC = $_POST['begin'];
	$endC = $_POST['end'];
	$name = $_POST['name'];
    $title = $_POST['title'];
	$process_arr = json_decode(stripslashes($_POST["process"]));
	$begin = str_replace('\"','"',$begin);
	$end = str_replace('\"','"',$end);
	$beginC = str_replace('\"','"',$beginC);
	$endC = str_replace('\"','"',$endC);
    $title = stripslashes($title);
	updateData($id,$url,$begin,$end,$beginC,$endC,$name,$title);
	for($i=0;$i<50;$i++){
		$obj = getProcessData($id,$i);
		/*
		$process_arr[$i] = array(4) {
			[0]=>
			string(6) "search"
			[1]=>
			string(5) "index"
			[2]=>
			string(4) "name"
			[3]=>
			string(11) "replacement"
		 } 
		*/
		$val = str_replace('\"','"',$process_arr[$i][0]);
		$replacement = str_replace('\"','"',$process_arr[$i][3]);
		$status = $process_arr[$i][1];
		if(!is_null($obj) && $val != ""){
			updateProcessData($id,$val,$i,$status,$replacement);
		}else if(is_null($obj) && $val != ""){
			addProcessData($id,$val,$i,$status,$replacement);
		}else if(!is_null($obj) && $val == ""){
			deleteProcessData($id,$i);
		}
	}
	//var_dump($process_arr);
	echo "OK";
	wp_die();
}

function addProcessData($id_lent,$val,$num,$status,$replacement){
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





//$url = "https://www.simplyrecipes.com/dinner-recipes-5091433";

//getHrefs($url,$begin,$end);
function getHrefs2($url,$begin,$end,$arr){
    $buf=implode("",file($url));
	$begin = str_replace('\"','"',$begin);
	$end = str_replace('\"','"',$end);
    $begin = preg_quote($begin,"/");
    $end = preg_quote($end,"/");
    if(preg_match_all("/$begin.*?$end/is",$buf,$matches) != NULL){
        $content = $matches[0][0];
	
		$content = useProccess($arr,$content,'index');
		preg_match_all("/<a[^>]+href=\"(.*?)\"[^>]+>/i",$content,$matches2);
        //error_log(print_r($matches2[1]));
        echo "<br>Кол-во ссылок: ".count($matches2[1])."<br><b>Ссылки:</b><br>";
        for($i=0;$i<count($matches2[1]);$i++){
			echo $i.": ".$matches2[1][$i]."<br>";
			
		}
		
		return $matches2[1];
    }else{
		echo "Ссылки не найдены";
	}
	error_log("---------------------------------------------------------------END--simple");
}

function useOptionalProcess($str){
	$str = preg_replace('/<style.*?\/style>/is', ' ', $str);
		$str = preg_replace('/class=".*?"/is', ' ', $str);
		$str = preg_replace('/id=".*?"/is', ' ', $str);
		$str = preg_replace('/style=".*?"/is', ' ', $str);
		$str = preg_replace('/<svg.*?\/svg>/is', ' ', $str);
	return $str;
}

function savePost($title,$content,$post_categories){
	$post_data = array(
		'post_title'    => sanitize_text_field( $title ),
		'post_content'  => $content,
		'post_status'   => 'draft',
		'post_author'   => 1,
		'post_category' => $post_categories
	);
	$post_id = wp_insert_post( $post_data );	
	return $post_id;
}

function getContentToSave($url,$begin,$end,$title,$arr,$bool){
    $buf=implode("",file($url));
	$begin = str_replace('\"','"',$begin);
	$end = str_replace('\"','"',$end);
    $title = str_replace('\"','"',$title);
	$title = stripslashes($title);
    $begin = preg_quote($begin,"/");
    $end = preg_quote($end,"/");
	//var_dump($begin);
    preg_match("/$title/is",$buf,$title_preg);
	//var_dump($title_preg);
    if(preg_match_all("/$begin.*?$end/is",$buf,$matches) != NULL){
		$str= $matches[0][0];

		$str = useProccess($arr,$str,'page');
		$str = useOptionalProcess($str);
		$image_arr = getImageFromContent($str);
        $path =  $bool ? "prok-test-uploads" : "prok-uploads";
        $res = saveImages($image_arr, $path,$title_preg[1]);
		var_dump($title_preg);
		$str = replaceImages($image_arr,$res[1],$str);
		echo "<br><b>Заголовок: </b>";
		echo $title_preg[1];
		echo "<br><b>Текст:</b><br>";
		echo "$str";
		error_log("-----------------------------images=>".var_dump($res));
		// Вставляем запись в базу данных
		if(!$bool){
			$post_id = savePost($title_preg[1],$str,array( 1 ));
			$images_urls = $res[0];
			for($i = 0;$i < count($images_urls);$i++){
				if($i == 0){
					saveImagesAndAddToPost( $post_id, $images_urls[$i],null,true);
				}else{
					saveImagesAndAddToPost( $post_id, $images_urls[$i]);
				}
			}
		}
    }else{
		echo "NOT MATCH";
	}
	error_log("---------------------------------------------------------------END--simple");
}

function deleteAllTestImage($path){
    if (file_exists(wp_upload_dir()['basedir']."/$path/")) {
        foreach (glob(wp_upload_dir()['basedir']."/$path/*") as $file) {
            unlink($file);
        }
    }
}

function useProccess($arr,$text,$place){
	for($i=0;$i<50;$i++){
		if($arr[$i][0] != ""){
			if($arr[$i][1] == $place){
				$search = ($arr[$i][0]);
				error_log("---------------------------------------------------------------$search");
				$replacement = $arr[$i][3] != "" ? $arr[$i][3] : ' ';
				$text = preg_replace("/$search/is", $replacement, $text);
			}
		}
	}
	return $text;
}

function replaceImages($img_search_arr,$img_res_arr,$content){
	if(count($img_search_arr) == count($img_res_arr)){
		for($i=0;$i<count($img_search_arr);$i++){
			$content = str_replace($img_search_arr[$i],$img_res_arr[$img_search_arr[$i]],$content);
		}
		return $content;
	}else{
		return $content;
	}
}

// Hook for adding admin menus
add_action('admin_menu', 'mt_add_pages');

// action function for above hook
function mt_add_pages() {
	
	
    //-------- Add a new top-level menu (ill-advised):
    // Здесь устанавливается функция отображения
    add_menu_page('parcer resipes settings', 'Parcer resipes settings','activate_plugins','prok','prok_menu_display');
	//--------
	require(dirname ( __FILE__ )."/admin/partials/lib.php");
	//require(dirname ( __FILE__ )."/admin/partials/prok-parcer-admin-display.php");

}

add_action('admin_enqueue_scripts', 'prok_enqueue_custom_js');
function prok_enqueue_custom_js() {
    wp_enqueue_script('custom', plugins_url('prok-parcer/admin/js/prok-parcer-admin.js'),array('jquery'), false, true);
	wp_enqueue_style( "prok", plugins_url('prok-parcer/admin/css/prok-parcer-admin.css'));
	
}


add_action( 'init', 'prk_init' );
function prk_init() {

}



 function createMainTable(){
 	global $wpdb;
 	?>
 <button onclick="document.location='admin.php?page=prok&prk-action=add'">Добавить ленту</button>
 <button onclick="del()">Удалить выделенные ленты</button>
		
 <table class="wp-list-table widefat fixed striped table-view-list toplevel_page_wpgrabber-index">
     <thead>
         <tr>
             <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Выделить все</label><input id="cb-select-all-1" type="checkbox"></td>
             <th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=name&amp;order=asc"><span>Наименование ленты</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" id="type" class="manage-column column-type sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=type&amp;order=asc"><span>Тип</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" id="url" class="manage-column column-url sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=url&amp;order=asc"><span>URL</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" id="published" class="manage-column column-published sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=published&amp;order=asc"><span>Статус</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" id="catid" class="manage-column column-catid sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=catid&amp;order=asc"><span>Рубрики</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" id="id" class="manage-column column-id sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=id&amp;order=asc"><span>ID</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" id="last_update" class="manage-column column-last_update sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=last_update&amp;order=asc"><span>Обновление</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" id="count_posts" class="manage-column column-count_posts sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=count_posts&amp;order=asc"><span>Кол-во записей</span><span class="sorting-indicator"></span></a></th>
         </tr>
     </thead>

     <tbody id="the-list">
 <?php 
	
 		$count = $wpdb->get_var( "SELECT COUNT(*) FROM prok_table" );
 		for($i=0;$i<$count;$i++){
 			$obj = $wpdb->get_row( 'SELECT * FROM prok_table', OBJECT, $i );
 			createRow($obj->ID,$obj->name,$obj->index_url);
 		}
			
 ?>

     </tbody>

     <tfoot>
         <tr>
             <td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-2">Выделить все</label><input id="cb-select-all-2" type="checkbox"></td>
             <th scope="col" class="manage-column column-name column-primary sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=name&amp;order=asc"><span>Наименование ленты</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" class="manage-column column-type sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=type&amp;order=asc"><span>Тип</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" class="manage-column column-url sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=url&amp;order=asc"><span>URL</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" class="manage-column column-published sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=published&amp;order=asc"><span>Статус</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" class="manage-column column-catid sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=catid&amp;order=asc"><span>Рубрики</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" class="manage-column column-id sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=id&amp;order=asc"><span>ID</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" class="manage-column column-last_update sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=last_update&amp;order=asc"><span>Обновление</span><span class="sorting-indicator"></span></a></th>
             <th scope="col" class="manage-column column-count_posts sortable desc"><a href="https://recepty-prigotovleniya.com/wp-admin/admin.php?page=wpgrabber-index&amp;orderby=count_posts&amp;order=asc"><span>Кол-во записей</span><span class="sorting-indicator"></span></a></th>
         </tr>
     </tfoot>

 </table>
 <?php
 }

 function createRow($ID,$name,$url){
 ?>
         <tr>
 			 <th scope="row" class="check-column">
                 <input type="checkbox" name="row" value="<?php echo $ID; ?>">
             </th>
             <td class="name column-name has-row-actions column-primary" data-colname="Наименование ленты"><?php echo $name; ?>
 				<div class="row-actions"><span class="edit"><a href="?page=prok&prk-action=edit&prk-id=<?php echo $ID; ?>">Изменить</a></span>
 <!--                 <div class="row-actions"><span class="edit"><a href="?page=prok&prk-action=edit&prk-id=<?php echo $ID; ?>">Изменить</a> | </span><span class="test"><a href="?page=prok&amp;prk-action=list&amp;id=<?php echo $ID; ?>" onclick="wpgrabberRun(<?php echo $ID; ?>, true); return false;">Тест&nbsp;импорта</a> | </span> 
                     <span
                         class="import"><a href="?page=prok&amp;prk-action=list&amp;id=<?php echo $ID; ?>" onclick="wpgrabberRun(<?php echo $ID; ?>, false); return false;">Импорт</a></span>-->
                 </div><button type="button" class="toggle-row"></button><button type="button" class="toggle-row"></button></td>
             <td
                 class="type column-type" data-colname="Тип">html</td>
                 <td class="url column-url" data-colname="URL"><a target="_blank" href="<?php echo $url; ?>"><?php echo $url; ?></a></td>
                 <td class="published column-published" data-colname="Статус"><a href="?page=wpgrabber-index&amp;rows[]=42&amp;action=Off"><span style="color:blue;">Вкл.</span></a></td>
                 <td class="catid column-catid" data-colname="Рубрики">0</td>
                 <td class="id column-id" data-colname="ID"><?php echo $ID; ?></td>
                 <td class="last_update column-last_update" data-colname="Обновление">0</td>
                 <td class="count_posts column-count_posts" data-colname="Кол-во записей">0</td>
         </tr>
 <?php
 }


 function prok_menu_display(){
 	global $wpdb;
 	$actionState = '';
 	$actionState = $_GET['prk-action'];
 	if($actionState=='default'){
 		createMainTable();
 	}else if($actionState=='add'){
 		$wpdb->query( "INSERT INTO prok_table (ID, name, title_preg ,index_url, prok_begin_index, prok_end_index, prok_begin, prok_end) VALUES (NULL, '0', '0','0', '0', '0', '0', '0')" );
 		$lastid = $wpdb->insert_id;
 		//addProcessData($lastid);
 		$obj = getData($lastid);
		
		
 		lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end,$obj->ID,$obj->name,$obj->title_preg,null);
		
 	}else if($actionState=='edit'){
 		$id = $_GET['prk-id'];
 		$obj = getData($id);
 		$arr = [];
 		for($i=0;$i<50;$i++){
 			$arr[] = getProcessData($id,$i);
 		}
 		lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end,$obj->ID,$obj->name,$obj->title_preg,$arr);
 	}else{
 		createMainTable();
 	}
 }



function getData($id){
	global $wpdb;
	$query = $wpdb->prepare('SELECT * FROM prok_table where ID=%d',$id);
	$obj = $wpdb->get_row( $query, OBJECT, 0 );
	return $obj;
}

function updateData($id,$index_url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$name,$title){
	global $wpdb;
	$query = $wpdb->prepare('UPDATE prok_table SET index_url=%s,prok_begin_index=%s,prok_end_index=%s,prok_begin=%s,prok_end=%s,name=%s,title_preg=%s where ID=%d',
        [$index_url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$name,$title,$id]);
	$wpdb->query( $query);
	//lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}

function deleteData($id){
	global $wpdb;
	$query = $wpdb->prepare('DELETE FROM prok_table where ID=%d',$id);
	$wpdb->query( $query);
	//lent_from($obj->index_url,$obj->prok_begin_index,$obj->prok_end_index,$obj->prok_begin,$obj->prok_end);
}


function lent_from($url,$prok_begin_index,$prok_end_index,$prok_begin,$prok_end,$id,$name,$title,$process_arr){
		?>
		<style>
		.updated{
			display: none;
		}
	</style>
<!-- 	<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div> -->
	<div class="wrap">
		<h2>Настройки</h2>
		<div class="container">
			<div style="display: flex;align-items: baseline;">
				<div class="label-input">
					<span >Наименование ленты</span>
				</div>
				<input id="name" type="text" name="" value="<?php echo $name; ?>" size="100">
			</div>
			<div style="display: flex;align-items: baseline;">
				<div class="label-input">
					<span >URL индексной страницы</span>
				</div>
				<input id="url" type="text" name="" value="<?php echo $url; ?>" size="70">
			</div>
			<div style="display: flex;align-items: baseline;">
				<div class="label-input">
					<span >Начальная точка индексной страницы</span>
				</div>
				<input id="one" type="text" name="" value="<?php echo htmlentities( $prok_begin_index); ?>" size="120">
			</div>
			<div style="display: flex;align-items: baseline;">
				<div class="label-input">
					<span  class="label-input">Конечная точка индексной страницы</span>	
				</div>
				<input id="two" type="text" name="" value="<?php echo htmlentities( $prok_end_index); ?>" size="120">
			</div>
            <div style="display: flex;align-items: baseline;">
                <div class="label-input">
                    <span >Заголовок</span>
                </div>
                <input id="title" type="text" name="" value="<?php echo htmlentities( $title); ?>" size="120">
            </div>
			<div style="display: flex;align-items: baseline;">
				<div class="label-input">
					<span >Начальная точка страницы</span>
				</div>
				<input id="oneContent" type="text" name="" value="<?php echo htmlentities( $prok_begin); ?>" size="120">
			</div>
			<div style="display: flex;align-items: baseline;">
				<div class="label-input">
					<span  class="label-input">Конечная точка страницы</span>	
				</div>
				<input id="twoContent" type="text" name="" value="<?php echo htmlentities( $prok_end); ?>" size="120">
			</div>
		</div>
		<?php
			prok_process_display($process_arr,$id);
		?>
		<button id="dddd" onclick="getContent()">OK</button>
		<button id="dddd" onclick="getTestContent()">TEST</button>
		<button id="save" onclick="saveOptions(<?php echo $id; ?>)">SAVE</button>
<!-- 		<button id="test" onclick="test()">TEST</button> -->
	</div>
	<div id="response"></div>
<?php
	 
}


function prok_row_process_display($row,$i){
?>
<tr align="center">
                                            <td><?php echo $i; ?></td>
                                            <td>
												<select name="params[usrepl][<?php echo $i; ?>][type]" style="width:150px;">
										<?php
												   prok_option_selector($row->status);
										?>
												</select>
											</td>
                                             <td>
												 <input size="30" type="text" name="params[usrepl][<?php echo $i; ?>][name]" value="">
		</td>
                                            <td>
												<input size="60" type="text" name="params[usrepl][<?php echo $i; ?>][search]" placeholder="Введите регулярные выражения для поиска" value="<?php echo htmlentities($row->value); ?>">
		</td>
                                            <td>
												<input size="50" type="text" name="params[usrepl][<?php echo $i; ?>][replace]" placeholder="Введите шаблон замены, если нужно" value="<?php echo htmlentities($row->replacement); ?>">
		</td>
                                            <td>
												<input style="text-align:center;" size="5" type="text" name="params[usrepl][<?php echo $i; ?>][limit]" value="">
		</td>                                        
</tr>
<?php
}


function prok_option_selector($val){
	$def="";
	$index ="";
	$page="";
	$title="";
	$text="";
	if($val == '0'){
		$def="selected";
	}else if($val == 'index'){
		$index="selected";
	}else if($val == 'page'){
		$page="selected";
	}else if($val == 'title'){
		$title="selected";
	}else if($val == 'text'){
		$text="selected";
	}
?>
	<option <?php echo $def; ?> value="0">выключен</option>
	<option <?php echo $index; ?> value="index">индексная html-страница (rss-контент или vk-лента)</option>
	<option <?php echo $page; ?> value="page">страница контента до парсинга</option>
<!-- 	<option value="intro">анонс</option> -->
<!-- 	<option <?php echo $text; ?> value="text">полный текст</option> -->
<!-- 	<option <?php echo $title; ?> value="title">заголовок</option> -->
<?php
}


function prok_process_display($process_arr,$id){
	?>
<td colspan="2">
                        <fieldset style="width: 1000px;border: 1px solid;padding: 5px;">
<legend>Дополнительные шаблоны обработки:</legend>
                            <p><span style="color:red;"><a href="https://wpgrabber.ru.com/regexp.png" target="_blank"><b>Азбука регулярных выражений</b></a></span></p>
							<p>Примеры частого построения: [^&gt;]+ ; [\w-]+ ; [\w\d-_]{1,}</p>
                            <div style="overflow: auto; height: 400px;">                                <style>
                                    .truser tr td, tr th {
                                        padding: 3px;
                                        background: #e7e7e7;
                                        font-size: 12px;
                                    }

                                    .truser input, .truser select {
                                        font-size: 12px;
                                    }

                                    .truser tr th {
                                        text-align: center;
                                    }
                                </style>
                                 <table class="truser">
                                    <tbody><tr>
                                        <th width="30px">#</th>
                                        <th>Объект применения</th>
                                        <th>Наименование шаблона</th>
                                        <th>Шаблон поиска</th>
                                        <th>Шаблон замены</th>
                                        <th>Кол-во замен</th>
                                    </tr>
										<?php
										for($i=0;$i<50;$i++){
											$row = getProcessData($id,$i);
											if(!is_null($row)){
												prok_row_process_display($row,$i);
											}else{
												prok_row_process_display(new ProccesObj(),$i);
												//prok_row_process_display(["","",""],$i);
											}
											
										}
										
										?>
                                                                        </tbody></table>
                            </div>
                        </fieldset>
                    </td><?php
}
class ProccesObj{
	public $value = "";
	public $number_list = "";
	public $id_lent = "";
	public $status = "0";
	public $replacement="";
    public $title="";
	function __constructor(){
		$this->value = "";
		$this->number_list = "";
		$this->id_lent = "";
		$this->status = "0";
		$this->replacement = "";
        $this->title = "";
	}
}

?>