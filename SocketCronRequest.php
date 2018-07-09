<?php

/**
 */
class SocketCronRequest extends BaseAbstractClass
{
    protected $in_str = '';
    protected $rtasks = [];
    protected $wtasks = [];
    protected $results = [];

    public function getParsedParams(){
        $res = [];
        if((md5($_SERVER["REMOTE_ADDR"]) === md5(Cfg::${get_called_class()}['allowed_addr']))
            && array_key_exists('params',$_GET)){
            $res = unserialize(stripcslashes(base64_decode($_GET['params'])));
        }
        return $res;
    }

    public function StartSocketsRequest($data){

        foreach ($data as $key=>$value) {
            $sh = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!$sh) continue;
            socket_set_option($sh, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 10, "usec" => 0]);
            socket_set_option($sh, SOL_SOCKET, SO_SNDTIMEO, ["sec" => 10, "usec" => 0]);
            socket_set_nonblock($sh);
            socket_connect($sh, Cfg::${get_called_class()}["url_addr"], Cfg::${get_called_class()}["url_port"]);
            $this->wtasks[$key] = $sh;
        }
    }

    public function WaitSocketsRequestData($data, $class){
        $rtasks_ = [];
        $wtasks_ = $this->wtasks; $e=null;

        $n = socket_select($rtasks_, $wtasks_, $e, null);
        if ($n > 0) {
            foreach ($wtasks_ as $sh) {
                $key = array_search($sh, $this->wtasks);
                unset($wtasks_[$key]);
                $post_str = base64_encode(addslashes(serialize([$class, [$key => $data[$key]]])));
                $headers = sprintf(implode('', Cfg::${get_called_class()}["headers"]), $post_str);
                if (socket_write($sh, $headers) === false){
                    fclose($sh);
                }else{
                    $rtasks_[$key] = $sh;
                }
            }
        }
//        $rtasks_ = $wtasks_;

        while(true){
            $f = fopen('tmp/rtasks_'.get_called_class().'_' . uniqid() , 'w+');
            fwrite($f, serialize($rtasks_));
            fclose($f);

            if(count($rtasks_)<1) break;

            $wtasks_ = []; $rtasks__ = $rtasks_;
            $n = socket_select($rtasks__, $wtasks_, $e, null);
            if ($n > 0) {
                foreach ($rtasks__ as $sh) {
                    $key = array_search($sh, $rtasks_);
                    if (!$key) continue;
                    $result = '';
                    while ($r = socket_read($sh, 1024)) $result .= $r;

                    $f = fopen('tmp/socket_' . $sh, 'w+');
                    fwrite($f, $result);
                    fclose($f);

                    socket_close($sh);
                    unset($rtasks_[$key]);
                }
            }
        }
    }
}