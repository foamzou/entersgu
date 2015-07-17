<?php
namespace Handler\Controller;
use Think\Controller;
/**
 * 社团协会控制器类
 */
class TeamController extends CommonController{
	//客户端发送的数据
	private static $_clientData;
	//社团协会类
	private static $_team;


	public function _initialize(){
		parent::_initialize();
		self::$_clientData = parent::$data;
		self::$_team = new \Handler\Model\TeamModel();
	}
	
	/**
	 * 获取社团列表
	 * @return [type] [description]
	 */
	public function getTeamList(){
		$returnData = self::$_team->getTeamList();
		exit($returnData);
	}

	/**
	 * 获取社团信息
	 * @return [type] [description]
	 */
	public function getTeamInfo(){
		$team_id = self::$_clientData['team_id'];
		$returnData = self::$_team->getTeamInfo($team_id);
		exit($returnData);

	}

}