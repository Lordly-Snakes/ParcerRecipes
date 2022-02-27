
<?php
$url = "https://www.simplyrecipes.com/dinner-recipes-5091433";
$begin = '<div id="mntl-taxonomysc-article-list-group_1-0" class="comp l-container mntl-taxonomysc-article-list-group mntl-block" data-scroll-offset="80">';
$end = '<div id="mntl-taxonomysc-child-block_1-0" class="comp mntl-taxonomysc-child-block">';
//getHrefs($url,$begin,$end);
function getHrefs($url,$begin,$end){
	error_log("---------------------------------------------------------------START");
    $buf=implode("",file($url));
    $begin = preg_quote($begin,'/');
    $end = preg_quote($end,'/');
    if(preg_match("/$begin.*?$end/is",$buf,$matches) != NULL){
        $content = $matches[0];
		preg_match("/<a[^>]+href=\".*?\"[^>]+>/is",$buf,$matches2);
        error_log(print_r($matches));
    }
	error_log("---------------------------------------------------------------END");
}

?>