<?php
namespace Home\Controller;
use Think\Controller;
class TestController extends Controller {
    public function index(){
        // $_user = new \Home\Model\UserModel();
        // $_lib = new \Home\Model\LibraryModel();
        // echo($_lib->search(0,'jay',0,1));


        $data = array(
            'command' => '60001',
            'data' => array(
                'searchType' => '0',
                'keyword' => '中国',
                'page' => '122'
                )
            );

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,'127.0.0.10/index.php/Home/Handler');
        $postData = json_encode($data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$postData);

        $rs = curl_exec($curl);
        curl_close($curl);
        echo($rs);
    }
}