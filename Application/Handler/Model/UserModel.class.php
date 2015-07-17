<?php
/**
 * 用户实体类
 */
namespace Handler\Model;
use Think\Model;
class UserModel extends Model{
	//this is a virtual model,haha~~
	protected $autoCheckFields = false;
	public static $USER_AVATAR_SHOW_PATH ; //用户头像显示路径
	public function _initialize(){
		self::$USER_AVATAR_SHOW_PATH = C('ROOT_PATH').'Public/Handler/UserAvatar/';
	}

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
		$isOk = false;
		$data['u_account_type'] = $u_account_type;
		$data['u_regtime'] = date("Y-m-d H:i:s",time());
		$data['u_auth_status'] = 0;//隐私状态默认为完全私密
		//本APP账户
		if($u_account_type == 1){
			//用户已存在
			if($this->isExist($u_email)){
				$rs = array('code'=>1,'message'=>'The user already exist');
				return json_encode($rs);
			}
			//参数错误
			if(!self::isEmailAccount($u_email) || empty($u_password)){
				return json_encode(array('code'=>2,'message'=>'The param is invaild'));
			}
			$data['u_email'] = $u_email;
			$data['u_nickname'] = $u_email;
			$data['u_password'] = $u_password;
			$data['u_avatar'] = 'default_avatar.png';
			$data['u_status'] = 2; //未激活

			$isOk = M('users')->add($data);
		}
		//第三方授权
		else{
			$data['u_openid'] = $u_openid;
			$data['u_nickname'] = $u_nickname;
			$data['u_avatar'] = $u_avatar;
			$data['u_status'] = 1; //已激活
			//参数不能为空
			if(empty($u_openid) || empty($u_nickname) || empty($u_avatar)){
				return json_encode(array('code'=>3,'message'=>'The param is empty'));
			}
			if(!$this->isExist($u_openid)){
				$isOk = M('users')->add($data);
			}
			else{
				$updateData['u_nickname'] = $u_nickname;
				$updateData['u_avatar'] = $u_avatar;
				$condition['u_openid'] = $u_openid;
				M('users')->where($condition)->save($updateData);
				$isOk = true;
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
			$condition['u_email'] = $u_email;
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
			$thirdMap['u_openid'] = $u_openid;
			$thirdMap['u_account_type'] = $u_account_type;
			$user = M('users')->where($thirdMap)->find();
		}

		//判断用户状态
		switch (self::getAccountStatus($account)) {
			//已进小黑屋
			case '0':
				$rs['code'] = 0;
				$rs['message'] = 'The account is illegal';
				$rs['u_id'] = $user['u_id'];
				break;
			//已激活的用户
			case '1':
				$rs['code'] = 1;
				$rs['message'] = 'The account is active';
				$rs['u_id'] = $user['u_id'];
				break;
			//未激活的用户
			case '2':
				$rs['code'] = 2;
				$rs['message'] = 'The account is inactive';
				$rs['u_id'] = $user['u_id'];
				break;
		}

		return json_encode($rs);

	}

	/**
	 * 用户是否存在
	 * @param  [type]  $account [用户邮件或OpenID]
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
	 * 是否存在指定的用户ID
	 * @param  [type]  $u_id [description]
	 * @return boolean       [description]
	 */
	public function isExistByUID($u_id){
		if(M('users')->where('u_id='.$u_id)->find()){
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
	/*public function getRegInitData(){
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
	}*/

	/**
	 * 获取用户信息
	 * @param  [type] $to_u_id   [信息所有者的用户ID]
	 * @param  [type] $from_u_id [发起请求的用户ID，游客用0表示]
	 * @return [type]            [description]
	 */
	public function getUserInfo($to_u_id,$from_u_id){
		//用户是否存在
		if(!self::isExistByUID($to_u_id)){
			return json_encode(array('code'=>1,'message'=>'The user is not exist'));
		}
		$userMap['u_id'] = $to_u_id;
		$userInfo = M()->query("SELECT u.u_nickname,u.u_email,u.u_account_type,u.u_sex,u.u_class,c.clg_name,m.m_name,
								u.u_avatar,u.u_status,u.u_stu_no,u.u_lib_pwd,u.u_edusys_pwd,u.u_lphone,u.u_sphone,
								u.u_auth_status,u.u_brief,u.u_sign
								FROM entersgu_users u 
								LEFT JOIN entersgu_college c ON u.clg_id=c.clg_id 
								LEFT JOIN entersgu_major m ON u.m_id=m.m_id
								WHERE u_id=$to_u_id");
		$userInfo = $userInfo[0];
		//给用户头像加链接前缀
		if($userInfo['u_account_type'] == 1)
			$userInfo['u_avatar'] = self::$USER_AVATAR_SHOW_PATH.$userInfo['u_avatar'];

		//他人查看自己
		if($to_u_id != $from_u_id){
			//先统一过滤不必要的信息
			unset($userInfo['u_stu_no']);
			unset($userInfo['u_lib_pwd']);
			unset($userInfo['u_edusys_pwd']);
			//根据用户的隐私状态再次过滤信息
			switch ($userInfo['u_auth_status']) {
				//完全私密
				case '0':
					unset($userInfo['u_lphone']);
					unset($userInfo['u_sphone']);
					break;
				//社团成员可见
				case '1':
					//判断社团成员的逻辑代码暂未写，暂且不过滤
					break;
			}
		}
		$rs['code'] = 0;
		$rs['message'] = 'Success';
		$rs['userInfo'] = $userInfo;
		return json_encode($rs);
	}

	/**
	 * 添加关注
	 * @param [type] $u_id    [description]
	 * @param [type] $to_u_id [description]
	 */
	public function addFollow($u_id,$to_u_id){
		//用户是否存在
		if(!self::isExistByUID($u_id)){
			return json_encode(array('code'=>1,'message'=>'The user who request is not exist'));
		}
		if(!self::isExistByUID($to_u_id)){
			return json_encode(array('code'=>2,'message'=>'The user who was request is not exist'));
		}
		//不能自己关注自己
		if($u_id==$to_u_id){
			return json_encode(array('code'=>4,'message'=>'You can not follow yourself'));
		}
		$data['u_id'] = $u_id;
		$data['to_u_id'] = $to_u_id;
		//是否有该记录
		if(M('follow')->where($data)->find()){
			return json_encode(array('code'=>3,'message'=>'The user had follow the other user'));
		}
		//添加关注记录
		if(M('follow')->add($data)){
			return json_encode(array('code'=>0,'message'=>'Success'));
		}
		else{
			return json_encode(array('code'=>-1,'message'=>'Exception error'));
		}

	}

	/**
	 * 取消关注
	 * @param  [type] $u_id    [description]
	 * @param  [type] $to_u_id [description]
	 * @return [type]          [description]
	 */
	public function cancelFollow($u_id,$to_u_id){
		//用户是否存在
		if(!self::isExistByUID($u_id)){
			return json_encode(array('code'=>1,'message'=>'The user who request is not exist'));
		}
		if(!self::isExistByUID($to_u_id)){
			return json_encode(array('code'=>2,'message'=>'The user who was request is not exist'));
		}
		$linkMap['u_id'] = $u_id;
		$linkMap['to_u_id'] = $to_u_id;
		//是否有该记录
		if(!M('follow')->where($linkMap)->find()){
			return json_encode(array('code'=>3,'message'=>'The user had not follow the other user'));
		}
		$data['u_id'] = $u_id;
		$data['to_u_id'] = $to_u_id;
		//删除关注记录
		if(M('follow')->where($linkMap)->delete()){
			return json_encode(array('code'=>0,'message'=>'Success'));
		}
		else{
			return json_encode(array('code'=>-1,'message'=>'Exception error'));
		}
	}

	/**
	 * 获取关注用户信息(关注和被关注)
	 * @param  [type] $action [1获取“我关注的人”，2获取“关注我的人”]
	 * @param  [type] $u_id   [用户ID]
	 * @return [type]         [description]
	 */
	public function getFollowInfo($action,$u_id){
		//用户是否存在
		if(!self::isExistByUID($u_id)){
			return json_encode(array('code'=>1,'message'=>'The user who request is not exist'));
		}
		//获取“我关注的人”
		if($action==1){
			$map['entersgu_follow.u_id'] = $u_id;
			$who = 'to_u_id';
		}
		//获取“关注我的人”
		elseif($action==2){
			$map['entersgu_follow.to_u_id'] = $u_id;
			$who = 'u_id';
		}
		else{
			return json_encode(array('code'=>2,'message'=>'The param of action is invaild'));
		}
		$userList = M('follow')->where($map)
			->field('u.u_id,u.u_nickname,u.u_avatar,u.u_account_type,u.u_sign')
			->join('entersgu_users u on entersgu_follow.'.$who .' = u.u_id')
			->select();
		if(!$userList){
			return json_encode(array('code'=>3,'message'=>'The information of follow is empty'));
		}
		foreach ($userList as $key => $value) {
			//给非第三方用户头像加上前缀链接
			if($value['u_account_type'] == 1 && $value['u_avatar']!=''){
				$userList[$key]['u_avatar'] = self::$USER_AVATAR_SHOW_PATH . $userList[$key]['u_avatar'];
			}
			unset($userList[$key]['u_account_type']);
		}
		$rs['code'] = 0;
		$rs['message'] = 'Success';
		$rs['count'] = count($userList);
		$rs['userList'] = $userList;
		return json_encode($rs);
	}

}

