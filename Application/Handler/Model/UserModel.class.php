<?php
/**
 * 用户实体类
 */
namespace Handler\Model;
use Think\Model;
class UserModel extends Model{
	//this is a virtual model,haha~~
	protected $autoCheckFields = false;

	/**
	 * 提交用户注册信息
	 * @param  [type] $u_account_type [用户类型]
	 * @param  [type] $u_email        [用户邮箱]
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
		
		//本APP账户
		if($u_account_type == 1){
			//用户已存在
			if($this->isExist($u_email)){
				$rs = array('code'=>1,'message'=>'The user already exist');
				return json_encode($rs);
			}
			$data['u_email'] = $u_email;
			$data['u_password'] = $u_password;
			$data['u_status'] = 2; //未激活

			$isOk = M('users')->add($data);
		}
		//第三方授权
		else{
			$data['u_openid'] = $u_openid;
			$data['u_nickname'] = $u_nickname;
			$data['u_avatar'] = $u_avatar;
			$data['u_status'] = 1; //已激活

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
	
		//注册成功
		if($isOk){
			$rs['code'] = 0;
			$rs['message'] = 'Registered success';
		}
		//异常错误
		else{
			$rs['code'] = -1;
			$rs['message'] = 'Exception error';
		}

		return json_encode($rs);
	}

	
	/**
	 * 用户账号密码是否正确且合法
	 * @param  [type]  $u_account_type [description]
	 * @param  [type]  $u_email        [description]
	 * @param  [type]  $u_password     [description]
	 * @param  [type]  $u_openid       [description]
	 * @return boolean                 [-1用户名或密码错误,0黑名单，1已激活，2未激活]
	 */
	public function isLegal($u_account_type,$u_email,$u_password,$u_openid){
		//账号不存在
		if(! ($this->isExist($u_email) || $this->isExist($u_openid)) ){
			return json_encode(array('code'=>3,'message'=>'The account does not exist'));
		}
		//本App的账号
		if($u_account_type == 1){
			$account = $u_email;
			$codition['u_email'] = $u_email;
			$condition['u_password'] = $u_password;
			$user = M('users')->where($condition)->find();
			if(!$user){
				//用户账号或密码错误
				$rs['code'] = -1;
				$rs['message'] = 'Account name or password is wrong';
				return json_encode($rs);
			}
		}
		//第三方账号
		else{
			$account = $u_openid;
		}

		//判断用户状态
		switch (self::getAccountStatus($account)) {
			//已进小黑屋
			case '0':
				$rs['code'] = 0;
				$rs['message'] = 'The account is illegal';
				break;
			//已激活的用户
			case '1':
				$rs['code'] = 1;
				$rs['message'] = 'The account is active';
				break;
			//未激活的用户
			case '2':
				$rs['code'] = 2;
				$rs['message'] = 'The account is inactive';
				break;
		}

		return json_encode($rs);

	}

	/**
	 * 用户是否存在
	 * @param  [type]  $account [description]
	 * @return boolean          [description]
	 */
	public function isExist($account){
		//本App账号
		if(self::isEmailAccount($account)){
			$condition['u_email'] = $account;
		}
		//第三方账号
		else{
			$condition['u_openid'] = $account;
		}
		
		$user = M('users')->where($condition)->find();
		if($user){
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * 返回用户状态。
	 * @param  [string] $account [description]
	 * @return [int]          [0黑名单，1已激活，2未激活]
	 */
	public function getAccountStatus($account){
		//本App账号
		if(self::isEmailAccount($account)){
			$condition['u_email'] = $account;
		}
		//第三方账号
		else{
			$condition['u_openid'] = $account;
		}
		return M('users')->where($condition)->getField('u_status');
	}

	/**
	 * 是否为本App账号。第三方账号则返回false
	 * @param  [type]  $account [description]
	 * @return boolean          [description]
	 */
	public function isEmailAccount($account){
		return (ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+",$account)); 
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

