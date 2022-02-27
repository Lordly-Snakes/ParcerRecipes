<?php
function loadtext_shortcode2( $atts ) {
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


function getHrefs($url,$begin,$end){
    $buf=implode("",file($url));
    $begin = preg_quote($begin,'/');
    $end = preg_quote($end,'/');
    if(preg_match("/$begin.*?$end/is",$buf,$matches) != NULL){
        $content = $matches[0];
        preg_match("/<a[^>]+href=\".*?\"[^>]+>/is",$buf,$matches);
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

function translate_yandex($text){
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
    return $res_text;

}
?>