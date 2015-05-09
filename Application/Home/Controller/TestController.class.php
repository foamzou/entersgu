<?php
namespace Home\Controller;
use Think\Controller;
class TestController extends Controller {
    public function index(){
      /*  $_user = new \Home\Model\UserModel();
        $_lib = new \Home\Model\LibraryModel();
        echo($_lib->search(0,'foam',0,1));*/
/*$u_account_type,$u_email,$u_password,$u_nickname,$u_avatar*/

        $data = array(
            'command' => '50002',
            'data' => array(
                'u_account_type' => '1',
                'u_email' => 'dasd@das.com',
                'u_password' => 'dasdasfefergsdfsadfd'
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