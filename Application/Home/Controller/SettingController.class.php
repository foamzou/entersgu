<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 基本设置控制器
 */
class SettingController extends CommonController{
	const LOGO_PATH = 'Public/UploadFiles/team_logo/';	//Logo存储地址
	const LOGO_UPLOAD_PATH = './Public/UploadFiles/team_logo/';	//Logo上传地址
	/**
	 * 更改Logo
	 * @return [type] [description]
	 */
	public function changeLogo(){
		if(isset($_POST['submit'])){
			//上传Logo
			if(!empty($_FILES['team_logo']['name'])){
				$config = array(
					'maxSize' => 1048576, //1M
					'exts' => array('jpg','png','jpeg'),
					'rootPath' => self::LOGO_UPLOAD_PATH,
					'autoSub' => false,
					'saveName' => 'time',
				);
				$upload = new \Think\Upload($config);
				$info = $upload->uploadOne($_FILES['team_logo']);
				if(!$info){
					echo $upload->getError();exit;
				}
				$data['team_id'] = session('team_id');
				$data['team_logo'] = $info['savename'];
				$oldLogo = M('team')->where(array('team_id'=>session('team_id')))->getField('team_logo');
				M('team')->save($data);

				//将之前的Logo删掉
				if($oldLogo!='') @unlink(DISK_ROOT_PATH . self::LOGO_PATH . $oldLogo);
				$this->redirect('changeLogo');

			}else{
				echo "<script>alert('请选择一张图片');history.go(-1)</script>";
			}
		}
		else{	
			$this->logo = M('team')->where(array('team_id'=>session('team_id')))->getField('team_logo');
			$this->display();
		}
	}

	/**
	 * 更新信息
	 * @return [type] [description]
	 */
	public function updateInfo(){
		if(isset($_POST['submit'])){
			$data['team_id'] = session('team_id');
			$data['team_sign'] = I('team_sign');
			$data['team_brief'] = I('team_brief');
			$data['team_notice'] = I('team_notice');
			if(M('team')->save($data)){
				$this->redirect('updateInfo');
			}else{
				echo "<script>alert('Oh no.出现某个不知名的故障啦，快联系管理员吧。');history.go(-1);</script>";
				exit();
			}
		}
		else{	
			$this->team = M('team')->field('entersgu_team.team_name,entersgu_team.team_notice,entersgu_team.team_sign,
											entersgu_team.team_brief,tt.team_type_name')
								->where(array('team_id'=>session('team_id')))
								->join('entersgu_team_type tt on tt.team_type_id = entersgu_team.team_type_id')
								->find();
			$this->display();
		}
	}
}