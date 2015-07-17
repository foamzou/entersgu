<?php
/**
 * 登录注销控制器
 */
namespace King\Controller;
use Think\Controller;
class SignController extends Controller{
	/**
	 * 显示登录页面
	 * @return [type] [description]
	 */
	public function index(){
		$this->display();
	}

	/**
	 * 登录
	 */
	public function signIn(){
		$adminName = I('adminName');
		$password = I('password','','md5');
		$condition['admin_name'] = $adminName;
		$condition['admin_password'] = $password;
		$admin = M('admin')->where($condition)->find();

		if($admin){
			$ip = $_SERVER["REMOTE_ADDR"];
			$now = date('Y-m-d H:i:s',time());
			//将当前登录信息写入数据库
			$data['admin_last_time'] = $now;
			$data['admin_last_ip'] = $ip;

			M('admin')->where(array('admin_id'=>$admin['admin_id']))->save($data);

			//将登录信息写入session
			session('adminName',$adminName);
			session('loginTime',$now);
			session('loginIp',$ip);
			session('lastLoginTime',$admin['admin_last_time']);
			session('lastLoginIp',$admin['admin_last_ip']);

			$this->redirect('Index/index');
		}
		else{
			redirect('index');
		}
	}

	/**
	 * 注销
	 */
	public function signOut(){
		session(null);
		redirect('index');
	}
}