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
		$returnData = self::$_topic->addTopic($tp_content,$u_id,$tp_img,$tp_anonymous);
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
}