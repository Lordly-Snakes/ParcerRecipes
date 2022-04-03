<?php

/**
 * Plugin Name: prok-parcer
 * Description: Парсинг рецептов
 * Author:      Прокофьев Антон
 * Version:     Версия плагина, например 1.0
 */

require(dirname ( __FILE__ )."/admin/partials/prok-parcer-admin-display.php");
require(dirname ( __FILE__ )."/admin/partials/core.php");
require(dirname ( __FILE__ )."/prok-action.php");
require(dirname ( __FILE__ )."/admin/partials/database-core.php");
require(dirname ( __FILE__ )."/admin/partials/lib.php");

$Debug = new Debug();

function getImageFromContent($content){
	if(preg_match_all("/src=\"(.*?)\"/is",$content,$matches) != NULL){
		return $matches[1];
	}else{
		return NULL;
	}
}


function test(){
    echo "test";
}

function getUrlsInDocument($url, $begin, $end, $arr): ?array{
    global $Debug;
    $res_str = "";
    $buf=implode("",file($url));
	$begin = str_replace('\"','"',$begin);
	$end = str_replace('\"','"',$end);
    $begin = preg_quote($begin,"/");
    $end = preg_quote($end,"/");
    preg_match_all("/$begin.*?$end/is",$buf,$matches);
    if(!is_null($matches)){
        $content = $matches[0][0];
		$content = useProccess($arr,$content,'index');
		preg_match_all("/<a[^>]+href=\"(.*?)\"[^>]+>/i",$content,$matches2);
        if(!is_null($matches2)) {
            $res_str .= "<br>Кол-во ссылок: " . count($matches2[1]) . "<br><b>Ссылки:</b><br>";
            for ($i = 0; $i < count($matches2[1]); $i++) {
                $res_str .= $i . ": " . $matches2[1][$i] . "<br>";
            }
            return $matches2[1];
        }else{
            $Debug->addDebugData("NOT_FOUND_URLS_IN_SEARCH_ZONE");
            standartResponse(200,"NOT_FOUND_URLS_IN_SEARCH_ZONE",null,"Ссылки не найдены, попробуйте сменить зону поиска");
        }
    }else{
        $Debug->addDebugData("NOT_FOUND_SEARCH_ZONE");
        standartResponse(200,"NOT_FOUND_SEARCH_ZONE",null,"Зона поиска задана неверно, попробуйте сменить зону поиска");
	}
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
	$post_id = wp_insert_post( $post_data ,true);
	return $post_id;
}

function getContentToSave($url,$begin,$end,$title,$arr,$bool,$ingr,$step): ?string
{
    global $Debug;
    $buf=implode("",file($url));
    $Debug->addDebugData($title);
	$begin = str_replace('\"','"',$begin);
	$end = str_replace('\"','"',$end);
    $title = str_replace('\"','"',$title);
	//$title = stripslashes($title);
    $begin = preg_quote($begin,"/");
    $end = preg_quote($end,"/");
    $cat = getPost('cat');

    preg_match("/$title/is",$buf,$title_preg);

    if(preg_match_all("/$begin.*?$end/is",$buf,$matches) != NULL){
       // $Debug->addDebugData($matches);
        $res_str = "";
        // Получение текста
		$str= $matches[0][0];
        // Обработка шаблонами обработки
		$str = useProccess($arr,$str,'page');
		$str = useOptionalProcess($str);
        // Работа с изображениями
		$image_arr = getImageFromContent($str);
        $path =  $bool ? "prok-test-uploads" : "prok-uploads";
        $res = saveImages($image_arr, $path,$title_preg[1]);
		$str = replaceImages($image_arr,$res[1],$str);


        // Формируем вывод
        $res_str =$res_str."<br><b>Заголовок: </b>";
        $res_str =$res_str.$title_preg[1];
        $res_str =$res_str."<br><b>Текст:</b><br>";
        $res_str =$res_str."$str";

		// Вставляем запись в базу данных
		if(!$bool){

            // Сохраняем и вставляем в бд запись
			$post_id = savePost($title_preg[1],$str,[$cat]);

            $ingridients_arr = getStepOrIngr(useProccess($arr,$buf,'page'),$ingr);
            $ingridients_arr = prok_get_special_array_format_ingridients($ingridients_arr);
            $step_arr = getStepOrIngr($buf,$step);
            $step_arr = prok_get_special_array_format_step($step_arr);
            $Debug->addDebugData($post_id);
            addMetaArr($ingridients_arr,'recipe_ingredients',$post_id);
            addMetaArr($step_arr,"recipe_steps",$post_id);

			$images_urls = $res[0];
            // Сохраняем изображения в бд и медиатеке
			for($i = 0;$i < count($images_urls);$i++){
				if($i == 0){
                    // Первое изображение пойдет на изоюражение-миниатюру
					saveImagesAndAddToPost( $post_id, $images_urls[$i],null,true);
				}else{
					saveImagesAndAddToPost( $post_id, $images_urls[$i]);
				}
			}
		}
        return $res_str;
    }else{
		return null;
	}
}

function useProccess($arr,$text,$place){
	for($i=0;$i<50;$i++){
		if($arr[$i][0] != ""){
			if($arr[$i][1] == $place){
				$search = ($arr[$i][0]);
				$replacement = $arr[$i][3] != "" ? $arr[$i][3] : ' ';
				$text = preg_replace("/$search/is", $replacement, $text);
			}
		}
	}
	return $text;
}

function prok_menu_display(){
 	global $wpdb;
 	$actionState = '';
 	$actionState = $_GET['prk-action'];
 	if($actionState=='default'){
 		createMainTable();
 	}else if($actionState=='add'){
 		$wpdb->query( "INSERT INTO prok_table (ID, name, title_preg ,index_url, prok_begin_index, prok_end_index, prok_begin, prok_end,ingr_pr,step_pr,autoupdate) VALUES (NULL, '0', '0','0', '0', '0', '0', '0','0','0','0')" );
 		$lastid = $wpdb->insert_id;
 		//addProcessData($lastid);
 		$obj = getData($lastid);
		
		
 		prokDisplayLentForm($obj);
		
 	}else if($actionState=='edit'){
 		$id = $_GET['prk-id'];
 		$obj = getData($id);
 		prokDisplayLentForm($obj);
 	}else{
 		createMainTable();
 	}
 }
?>
