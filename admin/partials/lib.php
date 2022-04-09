<?php
function prok_get_special_array_format_ingridients($arr){

    //$arr = explode("\n", $text);
    $arr3 = [];
    for($i=0;$i<count($arr);$i++){
        $arr[$i] = cleanStepOrIngr($arr[$i]);
        // teaspoon ounce pounds kg mg ml cup cups tablespoon
        preg_match('/([0-9\/to\-]{1,})/is',$arr[$i],$mmm);
        $c = preg_quote($mmm[1],"/");
        preg_match("/$c (.*?) /is",$arr[$i],$mmm2);
        $metr = $mmm2[1];
        preg_match("/$metr ([\w\s\S]+)/is",$arr[$i],$mmm3);
        //$Debug->addDebugData($arr3);
        $arr3[] = array(
            "name"=>trim($mmm3[1]),
            "term"=>"",
            "count"=>$mmm[1],
            "text"=>trim($mmm2[1]),
        );
    }
    return $arr3;
}

function prok_get_special_array_format_step($arr,$imgs_ids){
    global $Debug;
    $Debug->addDebugData($arr);
    $arr3= [];

    for($i=0;$i<count($arr);$i++){
        preg_match("/src=\"(.*?)\"/is",$arr[$i],$match);
        $Debug->addDebugData( $arr[$i]);
        $Debug->addDebugData( $imgs_ids[$match[1]]);
        $arr[$i] = cleanStepOrIngr($arr[$i]);
        $arr[$i] = preg_replace("/<.*?>/is","",$arr[$i]);
        $arr3[] = array(
            "text"=>$arr[$i],
            "photo"=>$imgs_ids[$match[1]],

        );
    }
    return $arr3;
}

function addMetaArr($arr,$title_meta,$post_id){
    global $Debug;
    $Debug->addDebugData($arr);

    update_post_meta( $post_id, $title_meta, $arr);
}

function getStepOrIngr($str,$pattern){

    preg_match("/$pattern/is",$str,$match);
    preg_match_all("/<li.*?>(.*?)<\/li>/is",$match[0],$match2);
    //var_dump($match2[1]);
    return $match2[1];
}

function getDataHtml($str,$pattern){
    global $Debug;
    $Debug->addDebugData($pattern);
    preg_match("/$pattern/is",$str,$match);
    $Debug->addDebugData($match);

    return cleanStepOrIngr($match[1]);
}

function cleanStepOrIngr($val) :string {
    $val = preg_replace("/<.*?>/is","",$val);
    return preg_replace("/<\/.*?>/is","",$val);
}

function removeStepOrIngr($str,$pattern): string
{
    return preg_replace("/$pattern/is","",$str);
}

function deleteAllTestImage($path){
    if (file_exists(wp_upload_dir()['basedir']."/$path/")) {
        foreach (glob(wp_upload_dir()['basedir']."/$path/*") as $file) {
            unlink($file);
        }
    }
}

function replaceImages($img_search_arr,$img_res_arr,$content){
    global $Debug;
    $Debug->addDebugData(["2",$img_search_arr]);
    $Debug->addDebugData(["3",$img_res_arr]);
    for($i=0;$i<count($img_search_arr);$i++){
        $content = str_replace($img_search_arr[$i],$img_res_arr[$img_search_arr[$i]],$content);
    }
    return $content;
}

function saveImages($arr_images_urls, $path, $prefix_name){
    $images_urls = [];
    $images_urls_assoc = [];
    if($path == "prok-test-uploads"){

    }
    for($i=0;$i<count($arr_images_urls);$i++){
        $img_url = $arr_images_urls[$i];

        $prefix_name = str_replace(" ","-",$prefix_name);
        $name_tmp = $prefix_name."-".time()."-".$i.".png";
        saveImgCurl($img_url,wp_upload_dir()['basedir']."/$path",$name_tmp);
        error_log($name_tmp);
        $images_urls[] = wp_upload_dir()['baseurl']."/$path/$name_tmp";
        $images_urls_assoc[$img_url] = wp_upload_dir()['baseurl']."/$path/$name_tmp";
    }
    //error_log("-----------------------------images=>".var_dump($images_urls));
    $arr = array($images_urls,$images_urls_assoc);
    return $arr;
}

function saveImagesAndAddToPost($post_id, $file, $desc = null , $thumb = false){
    global $debug; // определяется за пределами функции как true
    global $Debug;
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
        if( $debug ) $Debug->addDebugData( "Ошибка нет временного файла! <br />");
    }

    // проверки при дебаге
    if( $debug ){
        $Debug->addDebugData( 'File array: <br />');
        $Debug->addDebugData( $file_array );
        $Debug->addDebugData( '<br /> Post id: ' . $post_id . '<br />');
    }

    $id = media_handle_sideload( $file_array, $post_id, $desc );

    // Проверяем работу функции
    if ( is_wp_error( $id ) ) {
        $Debug->addDebugData( $id->get_error_messages() );
    } else {
        if($thumb){
            update_post_meta( $post_id, '_thumbnail_id', $id );
        }
    }

    // удалим временный файл
    @unlink( $tmp );
    return $id;
}

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

function loadtext_prok( $atts ) {
	$atts = shortcode_atts( [
		'znak' => 'Noname',
		'dayofweek' => 'сегодня',
	], $atts );
	$DOFW = $atts['dayofweek'];

	$ZNAK = $atts['znak'];

	switch($DOFW){
		case "понедельник": $DOFW = "monday"; break;
		case "вторник": $DOFW = "tuesday"; break;
		case "среда": $DOFW = "wednesday"; break;
		case "четверг": $DOFW = "thursday"; break;
		case "пятница": $DOFW = "friday"; break;
		case "выходные": $DOFW = "weekend"; break;
	}

	switch($ZNAK){
		case "овен": $ZNAK = "aries"; break;
		case "телец": $ZNAK = "taurus"; break;
		case "близнецы": $ZNAK = "gemini"; break;
		case "рак": $ZNAK = "cancer"; break;
		case "лев": $ZNAK = "leo"; break;
		case "дева": $ZNAK = "virgo"; break;
		case "весы": $ZNAK = "libra"; break;
		case "скорпион": $ZNAK = "scorpio"; break;
		case "стрелец": $ZNAK = "sagittarius"; break;
		case "козерог": $ZNAK = "capricorn"; break;
		case "водолей": $ZNAK = "aquarius"; break;
		case "рыбы": $ZNAK = "pisces"; break;
	}
	if( $DOFW === 'сегодня'){
		$urlor = "https://astrostyle.com/horoscopes/daily/".$ZNAK."/";
	}else if( $DOFW === 'еженедельный'){
		$urlor = "https://astrostyle.com/horoscopes/weekly/".$ZNAK."/";
	}else{
		$urlor = "https://astrostyle.com/horoscopes/daily/".$ZNAK."/".$DOFW."/";
	}

	error_reporting(E_ALL);
	global $wpdb;
	$buf=implode("",file($urlor));
	if($DOFW !== 'еженедельный'){
			if(preg_match("/<div class=\"horoscope-content\">.*?<\/p>/is",$buf,$matches) != NULL){
			$str = preg_replace("/<div itemprop='description'>/is","<div>",$matches[0]);
			$query = $wpdb->prepare("SELECT * FROM wp_horoscope_date WHERE znak = %s AND day_of_week= %s ",$ZNAK,$atts['dayofweek']);
			$obj = $wpdb->get_row( $query );
			$bdtext = $obj->before_tran_text;
			if($bdtext == $str)
			{
				$query = $wpdb->prepare("SELECT * FROM wp_horoscope_date WHERE znak = %s AND day_of_week= %s",$ZNAK,$atts['dayofweek']);

				$obj = $wpdb->get_row( $query );
				$text = $obj->text;
				return addAbz($text);
			}
			else
			{
				$original = $str;
				$str = translate_yandex($str);
				if($str !== false)
				{
					$query = $wpdb->prepare("UPDATE wp_horoscope_date SET text = %s, before_tran_text = %s WHERE wp_horoscope_date.znak = %s AND day_of_week= %s",$str,$original,$ZNAK,$atts['dayofweek']);
					$rrr = $wpdb->query($query);
					return addAbz($str);
				}
				else
				{
					$query = $wpdb->prepare("SELECT * FROM wp_horoscope_date WHERE znak = %s AND day_of_week= %s",$ZNAK,$atts['dayofweek']);
					$obj = $wpdb->get_row( $query );
					$text = $obj->text;
					if($text != ""){
						return addAbz($text);
					}else{
						return "Упс, видимо связь с источником потеряна";
					}

				}
			}
		}else{
			return "Упс, видимо связь с источником потеряна";
		}
	}else{
			if(preg_match("/<article class=\"horoscope-content\">.*?<div class=\"dropdown-inline\">/is",$buf,$matches) != NULL){
			$str = preg_replace("/<div itemprop='description'>/is","<div>",$matches[0]);
			$query = $wpdb->prepare("SELECT * FROM wp_horoscope_date WHERE znak = %s AND day_of_week= %s ",$ZNAK,$atts['dayofweek']);
			$obj = $wpdb->get_row( $query );
			$bdtext = $obj->before_tran_text;
			if($bdtext == $str)
			{
				$query = $wpdb->prepare("SELECT * FROM wp_horoscope_date WHERE znak = %s AND day_of_week= %s",$ZNAK,$atts['dayofweek']);

				$obj = $wpdb->get_row( $query );
				$text = $obj->text;
				return addAbz($text);
			}
			else
			{
				$original = $str;
				$str = translate_yandex($str);
				if($str !== false)
				{
					$query = $wpdb->prepare("UPDATE wp_horoscope_date SET text = %s, before_tran_text = %s WHERE wp_horoscope_date.znak = %s AND day_of_week= %s",$str,$original,$ZNAK,$atts['dayofweek']);
					$rrr = $wpdb->query($query);
					return addAbz($str);
				}
				else
				{
					$query = $wpdb->prepare("SELECT * FROM wp_horoscope_date WHERE znak = %s AND day_of_week= %s",$ZNAK,$atts['dayofweek']);
					$obj = $wpdb->get_row( $query );
					$text = $obj->text;
					if($text != ""){
						return addAbz($text);
					}else{
						return "Упс, видимо связь с источником потеряна";
					}

				}
			}
		}else{
			return "Упс, видимо связь с источником потеряна";
		}
	}
}

function addAbz($str){
	$str = str_replace("...", " ", $str);
    $pred=explode(".", $str); //разбиваем по предложениям

	$c = 0;
    $i=0;   //параметр для цикла
    $str="";    //обнуляем исходную строку

	for($j = 0;$j < count($pred);$j++){
		 $i++;   //при каждой итерации увеличиваем параметр(счетчик предложений в абзаце)
        if($i===3 && $j < count($pred)-1){ //если у нас 3 предложения в абзаце
            $i=0;   //обнуляем счетчик
            $pred[$j].=".<br/><br/>"; //разделяем абзац
        }else if($j < count($pred)-1){
			$pred[$j].=".";
		}
	}
    return implode(" ",$pred);
}

function translate_yandex($text) {
    global $Debug;
	$API_KEY = 'AQVN1rvXcknkrNZSONqMOv-BUNxC1c31r2ihdSJ8';
	$folder_id = 'b1gmftogfp2uck1rvei0';
	$target_language = 'ru';
	$texts = [$text];
	$url = 'https://translate.api.cloud.yandex.net/translate/v2/translate';
	$headers = [
		'Content-Type: application/json',
		"Authorization: Api-Key $API_KEY"
	];
	$post_data = [
		"sourceLanguageCode" => 'en',
		"targetLanguageCode" => $target_language,
		"format"=> 'HTML',
		"texts" => $texts,
		"folderId" => $folder_id,
	];
	$data_json = json_encode($post_data);
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	// curl_setopt($curl, CURLOPT_VERBOSE, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	$result = curl_exec($curl);
	curl_close($curl);

	if($result === false){
		return false;
	}

	$res_text = json_decode($result)->translations[0]->text;
    $Debug->addDebugData(json_decode($result));
    return $res_text;

}

function getPost($nameData){
    return $_POST[$nameData];
}
?>