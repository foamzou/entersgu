<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 用户管理控制器
 */
class UserController extends CommonController{
	//用户列表
	public function userList(){
		$this->user = M()->query("SELECT u.u_id,u.u_nickname,u.u_email,u.u_account_type,u.u_sex,u.u_class,c.clg_name,m.m_name,
								u.u_status
								FROM entersgu_users u 
								LEFT JOIN entersgu_college c ON u.clg_id=c.clg_id 
								LEFT JOIN entersgu_major m ON u.m_id=m.m_id");
		$this->display();
	}

	//删除用户
	public function deleteUser(){
		$u_id = I('u_id');
		if(M('users')->where(array('u_id'=>$u_id))->delete()){
			$this->ajaxReturn('ok',JSON);
		}
		else{
			$this->ajaxReturn('fail',JSON);
		}

	}
}