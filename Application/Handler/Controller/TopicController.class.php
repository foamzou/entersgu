<?php
namespace Handler\Controller;
use Think\Controller;
/**
 * 动态控制器类
 */
class TopicController extends CommonController{
	//客户端发送的数据
	private static $_clientData;
	//动态模型类
	private static $_topic;

	public function _initialize(){
		parent::_initialize();
		self::$_clientData = parent::$data;
		self::$_topic = new \Handler\Model\TopicModel();
	}

	/**
	 * 发表动态
	 */
	public function addTopic(){
		$tp_content = self::$_clientData['tp_content'];
		$u_id = self::$_clientData['u_id'];
		$tp_img = self::$_clientData['tp_img'];
		$tp_anonymous = self::$_clientData['tp_anonymous'];
		$returnData = self::$_topic->addTopic($tp_content,$u_id,$tp_img,$tp_thumb_img,$tp_anonymous);
		exit($returnData);
	}

	/**
	 * 删除动态
	 */
	public function delTopic(){
		$u_id = self::$_clientData['u_id'];
		$tp_id = self::$_clientData['tp_id'];
		$returnData = self::$_topic->delTopic($u_id,$tp_id);
		exit($returnData);
	}

	/**
	 * 获取动态
	 */
	public function getTopic(){
		$u_id = self::$_clientData['u_id'];
		$action = self::$_clientData['action'];
		$tp_id = self::$_clientData['tp_id'];
		$returnData = self::$_topic->getTopic($u_id,$action,$tp_id);
		exit($returnData);
	}

	/**
	 * 点赞
	 */
	public function addUp(){
		$u_id = self::$_clientData['u_id'];
		$type_id = self::$_clientData['type_id'];
		$obj_id = self::$_clientData['obj_id'];
		$returnData = self::$_topic->addUp($u_id,$type_id,$obj_id,$belong_u_id);
		exit($returnData);
	}

	/**
	 * 取消赞
	 */
	public function cancelUp(){
		$u_id = self::$_clientData['u_id'];
		$type_id = self::$_clientData['type_id'];
		$obj_id = self::$_clientData['obj_id'];
		$returnData = self::$_topic->cancelUp($u_id,$type_id,$obj_id);
		exit($returnData);
	}

	/**
	 * 发表动态评论
	 */
	public function addTopicComment(){
		$tp_id = self::$_clientData['tp_id'];
		$tp_cmt_content = self::$_clientData['tp_cmt_content'];
		$u_id = self::$_clientData['u_id'];
		$tp_cmt_anonymous = self::$_clientData['tp_cmt_anonymous'];
		$returnData = self::$_topic->addTopicComment($tp_id,$tp_cmt_content,$u_id,$tp_cmt_anonymous);
		exit($returnData);
	}

	/**
	 * 删除动态评论
	 */
	public function delTopicComment(){
		$u_id = self::$_clientData['u_id'];
		$tp_cmt_id = self::$_clientData['tp_cmt_id'];
		$tp_id = self::$_clientData['tp_id'];
		$returnData = self::$_topic->delTopicComment($u_id,$tp_cmt_id,$tp_id);
		exit($returnData);
	}

	/**
	 * 获取动态评论
	 */
	public function getTopicComment(){
		$u_id = self::$_clientData['u_id'];
		$tp_id = self::$_clientData['tp_id'];
		$returnData = self::$_topic->getTopicComment($u_id,$tp_id);
		exit($returnData);
	}

	/**
	 * 获取指定用户动态
	 * @return [type] [description]
	 */
	public function getUserTopic(){
		$u_id = self::$_clientData['u_id'];
		$visit = self::$_clientData['visit'];
		$action = self::$_clientData['action'];
		$tp_id = self::$_clientData['tp_id'];
		$returnData = self::$_topic->getUserTopic($u_id,$visit,$action,$tp_id);
		exit($returnData);
	}
}