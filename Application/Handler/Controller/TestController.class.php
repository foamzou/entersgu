<?php
namespace Handler\Controller;
use Think\Controller;
class TestController extends Controller {
    public function index(){
        $mode = 2;
        //$method = 'POST';
        $method = 'GET';
        $url = "Topic/delTopic?u_id=35&tp_id=2";

        if($mode == 1){
            $_user = new \Handler\Model\UserModel();
            $_lib = new \Handler\Model\LibraryModel();
            // echo($_lib->search(0,'jay',0,1));
            echo $_user->getAccountStatus('45sfsdfdgdfgfdg1d52asd');
        }
        else{
            $curl = curl_init();
             curl_setopt($curl,CURLOPT_URL,'127.0.0.10/index.php/Handler/'.$url);
             //curl_setopt($curl,CURLOPT_URL,'entersgu.hclab.cn/entersgu/index.php/Handler/'.$url);

            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);

            //POST方式
            if($method == 'POST'){
                $data = array(
                    'tp_content' => '久未放晴的天空，依旧留着你的笑容。哭过，却无法掩埋歉疚...',
                    'u_id' => '35',
                    'tp_img' => base64_encode(file_get_contents('Public/11.jpg')),
                    'tp_anonymous'=>'0'
                );
                $postData = json_encode($data);
                //打印发送实例
                echo '发送实例：<br/>'.$postData.'<br/>';
                curl_setopt($curl,CURLOPT_POST,1);
                curl_setopt($curl,CURLOPT_POSTFIELDS,$postData);
            }

            $rs = curl_exec($curl);
            curl_close($curl);
             echo '<br/>结果：<br/>'.$rs.'<br/>';
            }

    }
}