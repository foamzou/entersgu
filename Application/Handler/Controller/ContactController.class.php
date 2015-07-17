<?php
namespace Handler\Controller;
use Think\Controller;
/**
 * 校园电话簿控制器类
 */
class ContactController extends CommonController{
	//客户端发送的数据
	private static $_clientData;
	//校园电话簿
	private static $_contact;


	public function _initialize(){
		parent::_initialize();
		self::$_clientData = parent::$data;
		self::$_contact = new \Handler\Model\ContactModel();
	}
	
	/**
	 * 获取校园电话簿列表
	 * @return [type] [description]
	 */
	public function getContactList(){
		$sort = self::$_clientData['sort'];
		$returnData = self::$_contact->getContactList($sort);
		exit($returnData);
	}

	/**
	 * 增加拨打次数
	 * @return [type] [description]
	 */
	public function addCallCount(){
		$contact_id = self::$_clientData['contact_id'];
		$returnData = self::$_contact->addCallCount($contact_id);
		exit($returnData);
	}
}