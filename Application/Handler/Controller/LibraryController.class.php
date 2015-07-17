<?php
namespace Handler\Controller;
use Think\Controller;

/**
 * 图书馆控制器类
 */
class LibraryController extends CommonController{
	//客户端发送的数据
	private static $_clientData;
	//用户类
	private static $_lib;

	public function _initialize(){
		parent::_initialize();
		self::$_clientData = parent::$data;
		self::$_lib = new \Handler\Model\LibraryModel();
	}

	/**
	 * 图书检索
	 * @return [type] [description]
	 */
	public function search(){
		$searchType = self::$_clientData['searchType'];
		$keyword = self::$_clientData['keyword'];
		$page = self::$_clientData['page'];
		$returnData = self::$_lib->search($searchType,$keyword,$page);
		exit($returnData);
	}

	/**
	 * 获取图书详细信息
	 * @return [type] [description]
	 */
	public function getBookInfo(){
		$bookId = self::$_clientData['bookId'];
		$returnData = self::$_lib->getBookInfo($bookId);
		exit($returnData);
	}
}