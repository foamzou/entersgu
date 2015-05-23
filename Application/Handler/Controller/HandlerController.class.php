<?php
namespace Handler\Controller;
use Think\Controller;

class HandlerController extends Controller{
	public function index(){
		//实例化用户、图书馆类
		$_user = new \Handler\Model\UserModel();
		$_lib = new \Handler\Model\LibraryModel();
		//获取并解析客户端传过来的json
		$clientData = json_decode(file_get_contents('php://input'),true);
		//命令和数据
		$command = $clientData['command'];
		$data = $clientData['data'];

		//let us do something wonderful just now
		switch ($command) {
			//提交注册信息
			case '50001':
				$u_account_type = $data['u_account_type'];
				$u_email = $data['u_email']; 
				$u_password = $data['u_password'];
				$u_nickname = $data['u_nickname'];
				$u_avatar = $data['u_avatar'];
				$u_openid = $data['u_openid'];
				$returnData = $_user->regSumit($u_account_type,$u_email,$u_password,$u_nickname,$u_avatar,$u_openid);
				exit($returnData);

			//提交登录信息
			case '50002':
				$u_account_type = $data['u_account_type'];
				$u_email = $data['u_email'];
				$u_password = $data['u_password'];
				$u_openid = $data['u_openid'];
				$returnData = $_user->isLegal($u_account_type,$u_email,$u_password,$u_openid);
				exit($returnData);

			//检索图书
			case '60001':
				$searchType = $data['searchType'];
				$keyword = $data['keyword'];
				$page = $data['page'];
				$returnData = $_lib->search($searchType,$keyword,0,$page);
				exit($returnData);

			//命令错误
			default:
				exit(json_encode('error'=>'please check your command id :)'));
		}
	}
}