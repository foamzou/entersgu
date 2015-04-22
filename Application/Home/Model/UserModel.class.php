<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
	//this is a virtual model,haha~~
	protected $autoCheckFields = false;

	/**
	 * 获取注册初始化信息
	 * @return [type] [description]
	 */
	public function getRegInitData(){
		//计算注册的年级上限
        $month = date("m",time());
        $year = date("Y",time());
        //当前若小于7月，则不允许注册当前年。
        if($month<7)
            $year -= 1;

		$college = M('college')->field('clg_id,clg_name')->select();
		$major = M('major')->select();
		$rs = array('code' => 0 , 'data' => array('year'=>$year,'clgmajor'=>array()));

		//组装学院和专业数据
		foreach ($college as $key1 => $v1) {
			//组装专业数据
			$theMajor = array();
			foreach ($major as $key2 => $v2) {
				if($v1['clg_id'] == $v2['clg_id']){
					$theMajor[] = array('m_id'=>$v2['m_id'],'m_name'=>$v2['m_name']);
					
				}
			}
			$rs['data']['clgmajor'][] = array('clg_id'=>$v1['clg_id'],
											'clg_name'=>$v1['clg_name'],
											'major'=>$theMajor);
			
		}
		return json_encode($rs);
	}

	/**
	 * 提交用户注册信息
	 * @param  [type] $u_name     [description]
	 * @param  [type] $u_password [description]
	 * @param  [type] $u_sex      [description]
	 * @param  [type] $u_class    [description]
	 * @param  [type] $clg_id     [description]
	 * @param  [type] $m_id       [description]
	 * @return [type]             [description]
	 */
	public function regSumit($u_name,$u_password,$u_sex,$u_class,$clg_id,$m_id){
		$data['u_name'] = $u_name;
		$data['u_password'] = $u_password;
		$data['u_sex'] = $u_sex;
		$data['u_class'] = $u_class;
		$data['clg_id'] = $clg_id;
		$data['m_id'] = $m_id;
		$data['u_regtime'] = date("Y-m-d H:i:s",time());
		$data['u_temp_exp'] = 0;
		$data['u_exp'] = 0;
		$data['u_permission'] = 0;
		$data['u_status'] = 1;

		$isExist = M('users')->where("u_name='".$u_name."'")->find();
		//该用户名已被注册
		if($isExist)
			$rs['code'] = 1;
		else
		{
			$isOk = M('users')->add($data);
			if($isOk){
				$rs['code'] = 0;
			}
			else{
				$rs['code'] = -1;
			}
		}
		return json_encode($rs);
	}

}

