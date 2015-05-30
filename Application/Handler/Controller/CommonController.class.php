<?php
namespace Handler\Controller;
use Think\Controller;
/**
 * 所有Controller均继承该控制器
 */
class CommonController extends Controller{
	//从客户端获取到的数据
	protected static $data;

	public function _initialize(){
		//POST
		//对json数据包解码
		self::$data = json_decode(file_get_contents('php://input'),true);
		
		//GET
		if(!isset(self::$data)){
			self::$data = $_GET;
		}
	}
}