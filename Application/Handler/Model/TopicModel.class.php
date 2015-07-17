<?php
namespace Handler\Model;
use Think\Model;
/**
 * 动态模型
 */
class TopicModel extends Model{
	protected $autoCheckFields = false;
	const TOPIC_IMG_SAVE_PATH = 'Public/Handler/TopicImage/';	//动态图片存储路径
	const TRANSLATE_SIZE = 10;	//每次传输的动态数量

	private static $TOPIC_IMG_SHOW_PATH ; //动态图片显示路径
	
	private static $_user;	//用户类

	public function _initialize(){
		self::$TOPIC_IMG_SHOW_PATH = C('ROOT_PATH').self::TOPIC_IMG_SAVE_PATH; 

		self::$_user = new \Handler\Model\UserModel();
	}
	/**
	 * 添加动态
	 * @param [type] $tp_content   [description]
	 * @param [type] $u_id         [description]
	 * @param [type] $tp_img       [description]
	 * @param [type] $tp_anonymous [description]
	 */
	public function addTopic($tp_content,$u_id,$tp_img,$tp_thumb_img,$tp_anonymous){
		//用户不存在
		if(!self::$_user->isExistByUID($u_id)){
			return json_encode(array('code'=>1,'message'=>'The user is not exist'));
		}
		//是否存在图片
		if(isset($tp_img) && $tp_img!=''){
			//将BASE64解码转换为图片
			$imgName = md5(time().$u_id);
			//存储到指定的路径
			file_put_contents(self::TOPIC_IMG_SAVE_PATH.$imgName,base64_decode($tp_img));
			$data['tp_img'] = $imgName;
		}
		//是否存在缩略图
		if(isset($tp_thumb_img) && $tp_thumb_img!=''){
			//将BASE64解码转换为图片
			$imgName = md5(time().$u_id);
			//存储到指定的路径
			file_put_contents(self::TOPIC_IMG_SAVE_PATH.$imgName,base64_decode($tp_thumb_img));
			$data['tp_thumb_img'] = $imgName;
		}
		$data['tp_content'] = $tp_content;
		$data['u_id'] = $u_id;
		$data['tp_anonymous'] = $tp_anonymous;
		$data['tp_time'] = date('Y-m-d H:i:s',time());
		$data['tp_up'] = $tp_up;
		if(M('topic')->add($data)){
			$rs['code'] = 0;
			$rs['message'] = 'Success';
		}
		else{
			$rs['code'] = -1;
			$rs['message'] = 'Exception error';
		}
		return json_encode($rs);
	}

	/**
	 * 删除指定动态
	 * @param  [type] $u_id  [description]
	 * @param  [type] $tp_id [description]
	 * @return [type]        [description]
	 */
	public function delTopic($u_id,$tp_id){
		$condition['u_id'] = $u_id;
		$condition['tp_id'] = $tp_id;
		//检查动态是否存在和权限问题
		$topic = M('topic')->where($condition)->find();
		if($topic){
			//如果有图片则删除
			if($topic['tp_img']!="") @unlink(DISK_ROOT_PATH . self::TOPIC_IMG_SAVE_PATH . $topic['tp_img']);
			if($topic['tp_thumb_img']!="") @unlink(DISK_ROOT_PATH . self::TOPIC_IMG_SAVE_PATH . $topic['tp_thumb_img']);

			if(M('topic')->where($condition)->delete()){
				$rs['code'] = 0;
				$rs['message'] = 'Success';
			}
			//未知错误
			else{
				$rs['code'] = -1;
				$rs['message'] = 'Exception error';
			}
		}
		//该条动态不存在或没有删除权限
		else{
			$rs['code'] = 1;
			$rs['message'] = 'The topic is not exist or the user had not permission to do this';
		}
		return json_encode($rs);
	}

	/**
	 * 获取动态
	 * @param  [type] $u_id   [用户id。游客用0表示]
	 * @param  [type] $action [获取方式：数字。1.获取最新的10条动态(无需指定动态id)；2.获取指定动态id早前的10条动态；3.获取比指定动态ID还要新的10条动态]
	 * @param  [type] $tp_id  [指定的动态id]
	 * @return [type]         [description]
	 */
	public function getTopic($u_id,$action,$tp_id){
		//获取方式
		switch ($action) {
			case '1':
				$data = M()->query('SELECT u.u_id,u_nickname,u.u_account_type,u.u_avatar,t.tp_anonymous,t.tp_id,t.tp_content,t.tp_img,t.tp_thumb_img,t.tp_time,t.tp_up,t.tp_cmt_count 
									FROM entersgu_topic t INNER JOIN entersgu_users u ON t.u_id=u.u_id ORDER BY t.tp_id DESC LIMIT 0,'.self::TRANSLATE_SIZE);
				break;
			
			case '2':
				$data = M()->query('SELECT u.u_id,u_nickname,u.u_account_type,u.u_avatar,t.tp_anonymous,t.tp_id,t.tp_content,t.tp_img,t.tp_thumb_img,t.tp_time,t.tp_up,t.tp_cmt_count 
									FROM entersgu_topic t INNER JOIN entersgu_users u ON t.u_id=u.u_id WHERE tp_id<'.$tp_id.' ORDER BY t.tp_id DESC LIMIT 0,'.self::TRANSLATE_SIZE);
				break;

			case '3':
				$data = M()->query('SELECT u.u_id,u_nickname,u.u_account_type,u.u_avatar,t.tp_anonymous,t.tp_id,t.tp_content,t.tp_img,t.tp_thumb_img,t.tp_time,t.tp_up,t.tp_cmt_count  
									FROM entersgu_topic t INNER JOIN entersgu_users u ON t.u_id=u.u_id WHERE tp_id>'.$tp_id.' ORDER BY t.tp_id DESC');
				break;

			//action参数不合法
			default:
				$rs['code'] = 1;
				$rs['message'] = 'param of action is invalid,please send 1 to 3';
				return json_encode($rs);
		}

		foreach ($data as $key => $value) {
			//给用户头像(只对本App账号)和图片加上链接前缀
			if($value['u_avatar']!='' && $value['u_account_type']==1) 	$data[$key]['u_avatar'] = UserModel::$USER_AVATAR_SHOW_PATH.$data[$key]['u_avatar'];
			if($value['tp_img']!='') 	$data[$key]['tp_img'] = self::$TOPIC_IMG_SHOW_PATH.$data[$key]['tp_img'];
			if($value['tp_thumb_img']!='') 	$data[$key]['tp_thumb_img'] = self::$TOPIC_IMG_SHOW_PATH.$data[$key]['tp_thumb_img'];

			//将匿名用户数据擦掉
			if($value['tp_anonymous']==1 && $value['u_id']!=$u_id){
				$data[$key]['u_nickname'] = 0;
				$data[$key]['u_avatar'] = 0;
			}
			//该用户是否已为该动态点过赞
			if(M('up')->where(array('u_id'=>$u_id,'type_id'=>3,'obj_id'=>$value['tp_id']))->find()){
				$data[$key]['had_up'] = 1;
			}
			else{
				$data[$key]['had_up'] = 0;
			}
			//友好化时间
			$data[$key]['tp_time'] = toUIDate(strtotime($value['tp_time']));
		}
		$rs['code'] = 0;
		$rs['message'] = 'Success';
		$rs['topicInfo'] = $data;
		return json_encode($rs);
	}

	/**
	 * 给动态或动态评论点赞
	 * @param [type] $u_id    [用户ID]
	 * @param [type] $type_id [类型(3动态，4动态评论)]
	 * @param [type] $obj_id  [动态ID或动态评论ID]
	 */
	public function addUp($u_id,$type_id,$obj_id){
		//用户不存在
		if(!self::$_user->isExistByUID($u_id)){
			return json_encode(array('code'=>1,'message'=>'The user is not exist'));
		}
		//用户已经点过赞了
		if(self::hadUp($u_id,$type_id,$obj_id)){
			return json_encode(array('code'=>2,'message'=>'The user had up'));
		}
		//动态
		if($type_id==3){
			//动态不存在
			if(!self::isTopicExist($obj_id)){
				return json_encode(array('code'=>3,'message'=>'The topic is not exist'));
			}
			//点赞+1
			M('topic')->where('tp_id='.$obj_id)->setInc('tp_up');
			//动态所有者ID
			$belong_u_id = M('topic')->where('tp_id='.$obj_id)->getField('u_id');

		}
		//动态评论
		elseif($type_id==4){
			//动态评论不存在
			if(!self::isTopicCommentExist($obj_id)){
				return json_encode(array('code'=>3,'message'=>'The comment is not exist'));
			}
			//点赞+1
			M('tp_comment')->where('tp_cmt_id='.$obj_id)->setInc('tp_cmt_up');
			//评论所有者ID
			$belong_u_id = M('tp_comment')->where('tp_cmt_id='.$obj_id)->getField('u_id');
		}
		else{
			return json_encode(array('code'=>5,'message'=>'The param of type_id is invaild'));
		}

		//写入点赞表
		$Data['u_id'] = $u_id;
		$Data['type_id'] = $type_id;
		$Data['obj_id'] = $obj_id;
		$Data['belong_u_id'] = $belong_u_id;
		if(M('up')->add($Data)){
			return json_encode(array('code'=>0,'message'=>'Success'));
		}
		else{
			return json_encode(array('code'=>-1,'message'=>'Exception error'));
		}
	}

	/**
	 * 给动态或动态评论取消赞
	 * @param [type] $u_id    [用户ID]
	 * @param [type] $type_id [类型(3动态，4动态评论)]
	 * @param [type] $obj_id  [动态ID或动态评论ID]
	 */
	public function cancelUp($u_id,$type_id,$obj_id){
		//用户不存在
		if(!self::$_user->isExistByUID($u_id)){
			return json_encode(array('code'=>1,'message'=>'The user is not exist'));
		}
		//用户未点过赞
		if(!self::hadUp($u_id,$type_id,$obj_id)){
			return json_encode(array('code'=>2,'message'=>'The user had not up'));
		}
		//动态
		if($type_id==3){
			//动态不存在
			if(!self::isTopicExist($obj_id)){
				return json_encode(array('code'=>3,'message'=>'The topic is not exist'));
			}
			//点赞-1
			M('topic')->where('tp_id='.$obj_id)->setDec('tp_up');

		}
		//动态评论
		elseif($type_id==4){
			//动态评论不存在
			if(!self::isTopicCommentExist($obj_id)){
				return json_encode(array('code'=>3,'message'=>'The comment is not exist'));
			}
			//点赞-1
			M('tp_comment')->where('tp_cmt_id='.$obj_id)->setDec('tp_cmt_up');
		}
		else{
			return json_encode(array('code'=>5,'message'=>'The param of type_id is invaild'));
		}

		//删除点赞表中对应的记录
		$condition['u_id'] = $u_id;
		$condition['type_id'] = $type_id;
		$condition['obj_id'] = $obj_id;
		if(M('up')->where($condition)->delete()){
			return json_encode(array('code'=>0,'message'=>'Success'));
		}
		else{
			return json_encode(array('code'=>-1,'message'=>'Exception error'));
		}
	}


	/**
	 * 动态是否存在
	 * @param  [type]  $tp_id [动态ID]
	 * @return boolean        [description]
	 */
	public function isTopicExist($tp_id){
		if(M('topic')->where('tp_id='.$tp_id)->find()){
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * 动态评论是否存在
	 * @param  [type]  $tp_cmt_id [动态评论ID]
	 * @return boolean        [description]
	 */
	public function isTopicCommentExist($tp_cmt_id){
		if(M('tp_comment')->where('tp_cmt_id='.$tp_cmt_id)->find()){
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * 动态或动态评论是否已经点过赞
	 * @param  [type] $u_id    [用户ID]
	 * @param  [type] $type_id [类型(3动态，4动态评论)]
	 * @param  [type] $obj_id  [动态ID或动态评论ID]
	 * @return [type]          [description]
	 */
	public function hadUp($u_id,$type_id,$obj_id){
		$condition['u_id'] = $u_id;
		$condition['type_id'] = $type_id;
		$condition['obj_id'] = $obj_id;

		if(M('up')->where($condition)->find()){
			return true;
		}
		else{
			return false;
		}
	}


	/**
	 * 添加动态评论
	 * @param [type] $tp_id            [description]
	 * @param [type] $tp_cmt_content   [description]
	 * @param [type] $u_id             [description]
	 * @param [type] $tp_cmt_anonymous [description]
	 */
	public function addTopicComment($tp_id,$tp_cmt_content,$u_id,$tp_cmt_anonymous){
		//用户不存在
		if(!self::$_user->isExistByUID($u_id)){
			return json_encode(array('code'=>1,'message'=>'The user is not exist'));
		}
		//动态不存在
		if(!self::isTopicExist($tp_id)){
			return json_encode(array('code'=>2,'message'=>'The topic is not exist'));
		}
		$data['tp_id'] = $tp_id;
		$data['tp_cmt_content'] = $tp_cmt_content;
		$data['u_id'] = $u_id;
		$data['tp_cmt_anonymous'] = $tp_cmt_anonymous;
		$data['tp_cmt_time'] = date('Y-m-d H:i:s',time());
		$data['tp_cmt_up'] = $tp_up;

		//添加评论内容及评论数量+1
		if(M('tp_comment')->add($data) && M('topic')->where('tp_id='.$tp_id)->setInc('tp_cmt_count')){
			$rs['code'] = 0;
			$rs['message'] = 'Success';
		}
		else{
			$rs['code'] = -1;
			$rs['message'] = 'Exception error';
		}
		return json_encode($rs);
	}

	/**
	 * 获取动态评论
	 * @param  [type] $u_id   [用户id。游客用0表示]
	 * @param  [type] $tp_id  [指定的动态id]
	 * @return [type]         [description]
	 */
	public function getTopicComment($u_id,$tp_id){
		//动态不存在
		if(!self::isTopicExist($tp_id)){
			return json_encode(array('code'=>1,'message'=>'The topic is not exist'));
		}
		$data = M()->query('SELECT u.u_id,u_nickname,u.u_avatar,u.u_account_type,tc.tp_cmt_anonymous,tc.tp_cmt_id,tc.tp_cmt_content,tc.tp_cmt_time,tc.tp_cmt_up
							FROM entersgu_tp_comment tc INNER JOIN entersgu_users u ON tc.u_id=u.u_id WHERE tc.tp_id = '.$tp_id.' ORDER BY tc.tp_cmt_id ASC');
		foreach ($data as $key => $value) {
			//给用户头像加上链接前缀
			if($value['u_avatar']!='' && $value['u_account_type']==1) 	$data[$key]['u_avatar'] = UserModel::$USER_AVATAR_SHOW_PATH.$data[$key]['u_avatar'];

			//将匿名用户数据擦掉
			if($value['tp_cmt_anonymous']==1 && $value['u_id']!=$u_id){
				$data[$key]['u_nickname'] = 0;
				$data[$key]['u_avatar'] = 0;
			}
			//该用户是否已为该评论点过赞
			if(M('up')->where(array('u_id'=>$u_id,'type_id'=>4,'obj_id'=>$value['tp_cmt_id']))->find()){
				$data[$key]['had_up'] = 1;
			}
			else{
				$data[$key]['had_up'] = 0;
			}
			//友好化时间
			$data[$key]['tp_cmt_time'] = toUIDate(strtotime($value['tp_cmt_time']));
		}
		$rs['code'] = 0;
		$rs['message'] = 'Success';
		$rs['commentInfo'] = $data;
		return json_encode($rs);
	}

	/**
	 * 删除指定动态评论
	 * @param  [type] $u_id      [description]
	 * @param  [type] $tp_cmt_id [description]
	 * @param  [type] $tp_id     [description]
	 * @return [type]            [description]
	 */
	public function delTopicComment($u_id,$tp_cmt_id,$tp_id){
		$condition['u_id'] = $u_id;
		$condition['tp_cmt_id'] = $tp_cmt_id;
		//检查评论是否存在和权限问题
		$comment = M('tp_comment')->where($condition)->find();
		if($comment){
			if(M('tp_comment')->where($condition)->delete() && M('topic')->where('tp_id='.$tp_id)->setDec('tp_cmt_count')){
				$rs['code'] = 0;
				$rs['message'] = 'Success';
			}
			//未知错误
			else{
				$rs['code'] = -1;
				$rs['message'] = 'Exception error';
			}
		}
		//该条评论不存在或没有删除权限
		else{
			$rs['code'] = 1;
			$rs['message'] = 'The comment is not exist or the user had not permission to do this';
		}
		return json_encode($rs);
	}

	/**
	 * 获取指定用户动态
	 * @param  [type] $u_id   [指定用户]
	 * @param  [type] $visit  [1本人访问，2他人访问]
	 * @param  [type] $action [1.获取最新的10条动态(无需指定动态id)；2.获取指定动态id早前的10条动态；3.获取比指定动态ID还要新的所有动态]
	 * @param  [type] $tp_id  [action为1时，该参数无需填写]
	 * @return [type]         [description]
	 */
	public function getUserTopic($u_id,$visit,$action,$tp_id){
		//用户不存在
		if(!self::$_user->isExistByUID($u_id)){
			return json_encode(array('code'=>2,'messgae'=>'The user is not exist'));
		}
		$userMap['u_id'] = $u_id;
		//他人访问则把匿名数据去掉
		if($visit==2){
			$sqlStr = " AND tp_anonymous=0";
			$userMap['u_tp_anonymous'] = 0;
		}
		else{
			$sqlStr = "";
		}
		//动态数量
		$topicCount = M('topic')->where($userMap)->getField('COUNT(tp_id)');
		//点赞
		$giveUpCount = M('up')->where($userMap)->getField('COUNT(u_id)');
		//获赞
		$getUpCount = M('up')->where(array('belong_u_id'=>$u_id))->getField('COUNT(belong_u_id)');

		//获取方式
		switch ($action) {
			case '1':
				$data = M('topic')->field('tp_time,tp_content')
								->where('u_id='.$u_id . $sqlStr) 
								->order('tp_id DESC')
								->limit('0,'.self::TRANSLATE_SIZE)
								->select();
				break;
			
			case '2':
				$data = M('topic')->field('tp_time,tp_content')
								->where("u_id=$u_id AND tp_id<$tp_id".$sqlStr)
								->order('tp_id DESC')
								->limit('0,'.self::TRANSLATE_SIZE)
								->select();
				break;

			case '3':
				$data = M('topic')->field('tp_time,tp_content')
								->where("u_id=$u_id AND tp_id>$tp_id".$sqlStr)
								->order('tp_id DESC')
								->select();
				break;

			//action参数不合法
			default:
				$rs['code'] = 1;
				$rs['message'] = 'param of action is invalid,please send 1 to 3';
				return json_encode($rs);
		}

		//没有动态
		if($data == null){
			return json_encode(array('code'=>3,'messgae'=>'The topic of the user is empty'));
		}

		foreach ($data as $key => $value) {
			//友好化列表时间
			$data[$key]['tp_time'] = toListDate(strtotime($value['tp_time']));
		}

		$rs['code'] = 0;
		$rs['message'] = 'Success';
		$rs['topicCount'] = $topicCount;
		$rs['giveUpCount'] = $giveUpCount;
		$rs['getUpCount'] = $getUpCount;
		$rs['topicInfo'] = $data;
		return json_encode($rs);
	}


}