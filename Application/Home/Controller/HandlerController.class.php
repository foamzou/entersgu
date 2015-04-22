<?php
namespace Home\Controller;
use Think\Controller;

class HandlerController extends Controller{
	public function index(){
		//实例化用户类
		$_user = new \Home\Model\UserModel();
		//获取并解析客户端传过来的json
		$clientData = json_decode(file_get_contents('php://input'),true);
		//命令和数据
		$command = $clientData['command'];
		$data = $clientData['data'];

		//let us do something wonderful just now
		switch ($command) {
			//获取注册基本信息
			case 'USER_REG_INIT':
				$returnData = $_user->getRegInitData();
				exit($returnData);

			//提交注册信息
			case 'USER_REG_SUBMIT':
				$u_name = $data['u_name'];
				$u_password = $data['u_password'];
				$u_sex = $data['u_sex'];
				$u_class = $data['u_class'];
				$clg_id = $data['clg_id'];
				$m_id = $data['m_id'];
				
				$returnData = $_user->getRegInitData($u_name,$u_password,$u_sex,$u_class,$clg_id,$m_id);
				exit($returnData);
		}
	}
}