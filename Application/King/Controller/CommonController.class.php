<?php
/**
 * King分组下的所有Controller都继承该类
 */
namespace King\Controller;
use Think\Controller;

class CommonController extends Controller{
	/**
	 * 未登录，跳转到登录页
	 * @return [type] [description]
	 */
	public function _initialize(){
		if(!session('?adminName')){
			$this->redirect('King/Sign/index');
		}
	}

}