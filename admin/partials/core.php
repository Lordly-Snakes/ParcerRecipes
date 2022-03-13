<?php
class Debug{
    private $debug_data;
    public function __construct()
    {
        $this->debug_data = [];
    }

    public function addDebugData($data){
        $this->debug_data[] = $data;
    }

    public function getDebugData(){
        return $this->debug_data;
    }
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

class Response{
    public $code;
    public $title_code;
    public  $data;
    public $error_message;
    public $debug_data;

    public function __construct($code,$title_code,$data,$error_message = null,$debug_data = null)
    {
        $this->code = $code;
        $this->title_code = $title_code;
        $this->data = $data;
        $this->error_message = $error_message;
        $this->debug_data = $debug_data;
    }

    public function toJSONconv($obj){
        //var_dump($this);
        return json_encode($obj);
    }
}


function standartResponse($code,$title_code,$data,$error_message = null){
    global $Debug;
    $res = new Response($code,$title_code,$data,$error_message,$Debug->getDebugData());
    echo  $res->toJSONconv($res);
    wp_die();
}


?>