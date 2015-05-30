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

	public function search(){
		$searchType = self::$_clientData['searchType'];
		$keyword = self::$_clientData['keyword'];
		$page = self::$_clientData['page'];
		$returnData = self::$_lib->search($searchType,$keyword,0,$page);
		exit($returnData);
	}
}