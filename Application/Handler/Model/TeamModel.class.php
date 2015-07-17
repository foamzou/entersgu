<?php
/**
 * 社团协会模型类
 */
namespace Handler\Model;
use Think\Model;
class TeamModel extends Model{
	//this is a virtual model,haha~~
	protected $autoCheckFields = false;
	public static $TEAM_LOGO_SHOW_PATH ; //协会头像显示路径
	public function _initialize(){
		self::$TEAM_LOGO_SHOW_PATH = C('ROOT_PATH').'Public/UploadFiles/team_logo/';
	}
	/**
	 * 获取社团协会列表
	 * @return [type] [description]
	 */
	public function getTeamList(){
		$teamType = M('team_type')->select();
		foreach ($teamType as $key => $value) {
			static $index = 0;
			$teamList[$index]['typeName'] = $value['team_type_name'];
			$teamList[$index]['teamList'] =M('team')->Field('team_id,team_name')
						->where(array('team_type_id'=>$value['team_type_id'],'team_status'=>1))
						->order('team_id ASC')
						->select();
			$index++;
		}
		if($teamList){
			$rs['code'] = 0;
			$rs['message'] = 'Success';
			$rs['teamInfo'] = $teamList;
		}
		else{
			$rs['code'] = -1;
			$rs['message'] = 'Exception error';
		}
		return json_encode($rs);
	}

	/**
	 * 获取指定社团信息
	 * @param  [type] $team_id [description]
	 * @return [type]          [description]
	 */
	public function getTeamInfo($team_id){
		//该社团不存在
		if(!self::isTeamExist($team_id)){
			return json_encode(array('code'=>1,'message'=>'The team is not exist'));
		}

		$teamInfo = M('team')->field('team_id,team_name,entersgu_team_type.team_type_name,team_logo,team_notice,team_sign,team_brief,team_join')
					->join('entersgu_team_type on entersgu_team_type.team_type_id=entersgu_team.team_type_id')
					->where(array('team_id'=>$team_id,'team_status'=>1))->find();
		
		if($teamInfo){
			$teamInfo['team_logo'] = self::$TEAM_LOGO_SHOW_PATH.$teamInfo['team_logo'];
			$rs['code'] = 0;
			$rs['message'] = 'Success';
			$rs['teamInfo'] = $teamInfo;
		}
		else{
			$rs['code'] = -1;
			$rs['message'] = 'Exception error';
		}
		return json_encode($rs);
	}

	/**
	 * 指定的社团是否存在
	 * @param  [type]  $team_id [description]
	 * @return boolean          [description]
	 */
	public function isTeamExist($team_id){
		$status =  M('team')->where(array('team_id'=>$team_id))->getField('team_status');
		return $status==1 ? true : false;
	}

}

