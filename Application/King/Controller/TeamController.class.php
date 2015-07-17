<?php
namespace King\Controller;
use Think\Controller;
/**
 * 团队协会管理控制器
 */
class TeamController extends CommonController{
	//团队协会列表
	public function teamList(){
		$this->team = M('team')
				->field('entersgu_team.team_id,entersgu_team.team_name,entersgu_team.team_logo,
					entersgu_team.team_notice,entersgu_team.team_sign,entersgu_team.team_brief,tt.team_type_name,tm.u_id')
				->where("team_status=1")
				->join('entersgu_team_type tt on tt.team_type_id = entersgu_team.team_type_id')
				->join('LEFT JOIN entersgu_team_member tm on entersgu_team.team_id = tm.team_id AND tm.mb_admin=1')
				->order('entersgu_team.team_type_id ASC,entersgu_team.team_id ASC')
				->select();
		$this->display();
	}

	//添加社团
	public function addTeam(){
		if(isset($_POST['submit'])){
			$data['team_name'] = I('team_name');
			$data['team_type_id'] = I('team_type_id');
			$data['team_brief'] = I('team_brief');
			$data['team_status'] = 1;
			foreach ($data as $key => $value) {
				if($value === ''){
					echo "<script>alert('请将必填项填完');history.go(-1)</script>";
					exit;
				}
			}
			M('team')->add($data);
			$this->redirect('King/Team/teamList');
		}
		else{
			$this->teamType = M('team_type')->select();
			$this->display();
		}
	}

	/**
	 * 删除社团
	 * @return [type] [description]
	 */
	public function delTeam(){
		$data['team_id'] = I('team_id');
		$data['team_status'] = 0;	//将状态置为0
		if(M('team')->save($data)){
			$this->ajaxReturn('ok',JSON);
		}
	}

	/**
	 * 更改管理员
	 * @return [type] [description]
	 */
	public function changeAdmin(){
		$u_id = I('u_id');
		$team_id = I('team_id');
		//用户不是该协会成员
		if(!M('team_member')->where(array('u_id'=>$u_id,'team_id'=>$team_id))->find()){
			$this->ajaxReturn("1",JSON);
		}
		//该用户已经是该协会或其他协会的管理员了
		if(M('team_member')->where(array('u_id'=>$u_id,'mb_admin'=>1))->find()){
			$this->ajaxReturn('2',JSON);
		}
		if(
			M()->execute("UPDATE entersgu_team_member SET mb_admin=0 WHERE team_id=$team_id")
			&
			M()->execute("UPDATE entersgu_team_member SET mb_admin=1 WHERE team_id=$team_id AND u_id=$u_id")
		){
			$this->ajaxReturn('ok',JSON);
		}
	}
}