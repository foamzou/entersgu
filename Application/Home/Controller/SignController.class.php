<?php
/**
 * 登录注销控制器
 */
namespace Home\Controller;
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
		$condition['u_email'] = $adminName;
		$condition['u_password'] = $password;
		$u_id = M('users')->where($condition)->getField('u_id');

		$admin = M('team_member')->where(array('u_id'=>$u_id,'mb_admin'=>1))->find();
		if(!$admin){
			echo "<script>alert('账号密码不正确或非管理员账号');history.go(-1)</script>";
			exit;
		}
		else{
			$team = M('team')->where(array('team_id'=>$admin['team_id']))->find();
			session('team_name',$team['team_name']);
			session('team_id',$team['team_id']);
			$this->redirect('Index/index');
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