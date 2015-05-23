<?php
namespace Handler\Controller;
use Think\Controller;
class TestController extends Controller {
    public function index(){
        // $_user = new \Handler\Model\UserModel();
        // $_lib = new \Handler\Model\LibraryModel();
        // echo($_lib->search(0,'jay',0,1));


        $data = array(
                'searchType' => '1',
                'keyword' => '龙珠',
                'page' => '1'
            );
        $url = "Library/search";
        $curl = curl_init();
         curl_setopt($curl,CURLOPT_URL,'127.0.0.10/index.php/Handler/'.$url);
        //curl_setopt($curl,CURLOPT_URL,'entersgu.hclab.cn/entersgu/index.php/Handler/Handler');
        $postData = json_encode($data);

        //打印发送实例
        echo '发送实例：<br/>'.$postData.'<br/>';


        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$postData);

        $rs = curl_exec($curl);
        curl_close($curl);
         echo '<br/>结果：<br/>'.$rs.'<br/>';
    }
}