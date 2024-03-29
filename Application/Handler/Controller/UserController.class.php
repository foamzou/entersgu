<?php
namespace Handler\Controller;
use Think\Controller;
/**
 * 用户控制器类
 */
class UserController extends CommonController{
	//客户端发送的数据
	private static $_clientData;
	//用户类
	private static $_user;


	public function _initialize(){
		parent::_initialize();
		self::$_clientData = parent::$data;
		self::$_user = new \Handler\Model\UserModel();
	}
	
	/**
	 * 提交注册
	 * @return [type] [description]
	 */
	public function regSumit(){
		$u_account_type = self::$_clientData['u_account_type'];
		$u_email = self::$_clientData['u_email'];
		$u_password = self::$_clientData['u_password'];
		$u_nickname = self::$_clientData['u_nickname'];
		$u_avatar = self::$_clientData['u_avatar'];
		$u_openid = self::$_clientData['u_openid'];
		
		$returnData = self::$_user->regSumit($u_account_type,$u_email,$u_password,$u_nickname,$u_avatar,$u_openid);
		exit($returnData);
	}

	//验证登录信息
	public function isLegal(){
		$u_account_type = self::$_clientData['u_account_type'];
		$u_email = self::$_clientData['u_email'];
		$u_password = self::$_clientData['u_password'];
		$u_openid = self::$_clientData['u_openid'];

		$returnData = self::$_user->isLegal($u_account_type,$u_email,$u_password,$u_openid);
		exit($returnData);
	}

	/**
	 * 获取用户信息
	 * @return [type] [description]
	 */
	public function getUserInfo(){
		$to_u_id = self::$_clientData['to_u_id'];
		$from_u_id = self::$_clientData['from_u_id'];

		$returnData = self::$_user->getUserInfo($to_u_id,$from_u_id);
		exit($returnData);
	}

	/**
	 * 添加关注
	 * @return [type] [description]
	 */
	public function addFollow(){
		$u_id = self::$_clientData['u_id'];
		$to_u_id = self::$_clientData['to_u_id'];

		$returnData = self::$_user->addFollow($u_id,$to_u_id);
		exit($returnData);
	}

	/**
	 * 取消关注
	 * @return [type] [description]
	 */
	public function cancelFollow(){
		$u_id = self::$_clientData['u_id'];
		$to_u_id = self::$_clientData['to_u_id'];

		$returnData = self::$_user->cancelFollow($u_id,$to_u_id);
		exit($returnData);
	}

	/**
	 * 获取关注用户信息(关注和被关注)
	 * @return [type] [description]
	 */
	public function getFollowInfo(){
		$action = self::$_clientData['action'];
		$u_id = self::$_clientData['u_id'];

		$returnData = self::$_user->getFollowInfo($action,$u_id);
		exit($returnData);
	}
}