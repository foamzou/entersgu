<?php
/**
 * 用户实体类
 */
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
	//this is a virtual model,haha~~
	protected $autoCheckFields = false;



	/**
	 * 提交用户注册信息
	 * @param  [type] $u_account_type [description]
	 * @param  [type] $u_email        [description]
	 * @param  [type] $u_password     [description]
	 * @param  [type] $u_nickname     [description]
	 * @param  [type] $u_avatar       [description]
	 * @param  [type] $u_openid       [description]
	 * @return [type]                 [description]
	 */
	public function regSumit($u_account_type,$u_email,$u_password,$u_nickname,$u_avatar,$u_openid){
		$isOk = true;
		$data['u_account_type'] = $u_account_type;
		$data['u_regtime'] = date("Y-m-d H:i:s",time());
		$data['u_permission'] = 0;
		$data['u_status'] = 1;

		//本APP账户
		if($u_account_type == 1){
			//用户已注册
			if($this->isExist($u_email)){
				$rs = array('code'=>1);
				return json_encode($rs);
			}
			$data['u_email'] = $u_email;
			$data['u_password'] = $u_password;

			$isOk = M('users')->add($data);
		}
		//第三方授权
		else{
			$data['u_openid'] = $u_openid;
			$data['u_nickname'] = $u_nickname;
			$data['u_avatar'] = $u_avatar;

			if(!$this->isExist($u_openid)){
				$isOk = M('users')->add($data);
			}
			else{
				$updateData['u_nickname'] = $u_nickname;
				$updateData['u_avatar'] = $u_avatar;

				$condition['u_openid'] = $u_openid;
				M('users')->where($condition)->save($updateData);
			}
		}
	
		if($isOk){
			$rs['code'] = 0;
		}
		else{
			$rs['code'] = -1;
		}

		return json_encode($rs);
	}

	
	/**
	 * 用户账号密码是否正确且合法
	 * @param  [type]  $u_account_type [description]
	 * @param  [type]  $u_email        [description]
	 * @param  [type]  $u_password     [description]
	 * @param  [type]  $u_openid       [description]
	 * @return boolean                 [description]
	 */
	public function isLegal($u_account_type,$u_email,$u_password,$u_openid){
		//账号不存在
		if(! ($this->isExist($u_email) || $this->isExist($u_openid)) ){
			return json_encode(array('code'=>3));
		}
		//本App的账号
		if($u_account_type == 1){
			$codition['u_email'] = $u_email;
			$condition['u_password'] = $u_password;
			$user = M('users')->where($condition)->find();
			if($user){
				if(M('users')->where($condition)->getField('u_status')){
					//用户账号密码正确，且合法
					$rs['code'] = 0;
				}
				else{
					//用户已经被拉进小黑屋
					$rs['code'] = 1;
				}
			}
			else{
				//用户账号或密码错误
				$rs['code'] = 2;
			}
		}
		//第三方授权登录
		else{
			//检查是否已经被拉进小黑屋
			$condition['u_openid'] = $u_openid;
			if(M('users')->where($condition)->getField('u_status')){
				$rs['code'] = 0;
			}
			else{
				$rs['code']= 1;
			}
		}
		return json_encode($rs);

	}

	public function isExist($account){
		$condition['u_email'] = $account;
		$condition['u_openid'] = $account;
		$condition['_logic'] = 'OR';
		$user = M('users')->where($condition)->find();
		if($user){
			return true;
		}
		else{
			return false;
		}
	}


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

}

