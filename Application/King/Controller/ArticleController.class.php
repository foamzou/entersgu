<?php
/**
 * 资讯管理控制器
 */
namespace King\Controller;
use Think\Controller;
class ArticleController extends CommonController{
	const COVERS_PATH = 'Public/UploadFiles/covers/';	//封面存储地址
	const COVERS_UPLOAD_PATH = './Public/UploadFiles/covers/';	//封面上传地址
	/**
	 * 发表资讯页面
	 * @return [type] [description]
	 */
	public function write(){
		$type_group = I("type_group");
		if($type_group==1 || empty($type_group)){
			$condition['art_type_group'] = 1;
		}
		else{
			$condition['art_type_group'] = 2;
		}
		$this->art_type_group = $condition['art_type_group'];
		$this->typeList = M('art_type')->where($condition)->select();
		$this->display();
	}

	/**
	 * 提交事件
	 * @return [type] [description]
	 */
	public function sendArticle(){
		//上传封面
		if(!empty($_FILES['cover']['name'])){
			$config = array(
				'maxSize' => 1048576, //1M
				'exts' => array('jpg','png','jpeg'),
				'rootPath' => self::COVERS_UPLOAD_PATH,
				'autoSub' => false,
				'saveName' => 'time',
			);
			$upload = new \Think\Upload($config);
			$info = $upload->uploadOne($_FILES['cover']);
			if(!$info){
				echo $upload->getError();exit;
			}
			$data['art_cover'] = $info['savename'];
		}

		
		$data['art_type_group'] = I('art_type_group');
		$data['art_type_id'] = I('type_id');
		$data['art_title'] = I('title');
		$data['art_author'] = 0; //0代表官方
		$data['art_status'] = 1; //1代表正常，0代表删除
		$data['art_up'] = 0;
		$data['art_cmt_count'] = 0;
		$data['art_content'] = html_entity_decode(I('content'));
		$data['art_time'] = date('Y-m-d H:i:s',time());
		//活动类
		if($data['art_type_group'] == 2){
			$data['art_address'] = I('address');
			$data['art_start_time'] = I('startTime');
			$data['art_end_time'] = I('endTime');
		}

		foreach ($data as $key => $value) {
			if($value === '' && $key!='art_cover'){
				echo "<script>alert('请将必填项填完');history.go(-1)</script>";
				exit;		
			}
		}
		if(M('article')->add($data)){
			$this->success('发表成功');
		}
		
	}



	/**
	 * 资讯列表
	 * @return [type] [description]
	 */
	public function articleList(){
		$type_group = I("type_group");
		if($type_group==1 || empty($type_group)){
			$condition['entersgu_article.art_type_group'] = 1;
		}
		else{
			$condition['entersgu_article.art_type_group'] = 2;
		}
		$condition['art_status'] = 1;
		$condition['art_author'] = 0;//0代表官方（环创电脑工作室）
		$this->list = M('article')->order('art_id desc')->
						field('art_id,art_title,art_time,entersgu_art_type.art_type_name')
						->join('entersgu_art_type on entersgu_art_type.art_type_id = entersgu_article.art_type_id')
						->where($condition)->select();
		$this->display();
	}

	/**
	 * 编辑资讯页面
	 * @return [type] [description]
	 */
	public function edit(){
		$art_id = I('art_id');
		if(is_numeric($art_id)){
			
			$this->article = M('article')->where('art_id='.$art_id)->find();
			$this->art_type_group = $this->article['art_type_group'];
			$this->typeList = M('art_type')->where(array('art_type_group'=>$this->art_type_group))->select();
			$this->display();
		}
	}

	/**
	 * 更新资讯事件
	 * @return [type] [description]
	 */
	public function updateArticle(){
		$data['art_id'] = I('art_id');
		//上传封面
		if(!empty($_FILES['cover']['name'])){
			$config = array(
				'maxSize' => 1048576, //1M
				'exts' => array('jpg','png','jpeg'),
				'rootPath' => self::COVERS_UPLOAD_PATH,
				'autoSub' => false,
				'saveName' => 'time',
			);
			$upload = new \Think\Upload($config);
			$info = $upload->uploadOne($_FILES['cover']);
			if(!$info){
				echo $upload->getError();exit;
			}
			$data['art_cover'] = $info['savename'];
		}

		$data['art_type_id'] = I('type_id');
		$data['art_type_group'] = I('art_type_group');
		$data['art_title'] = I('title');
		$data['art_content'] = html_entity_decode(I('content'));

		//活动类
		if($data['art_type_group'] == 2){
			$data['art_address'] = I('address');
			$data['art_start_time'] = I('startTime');
			$data['art_end_time'] = I('endTime');
		}

		foreach ($data as $key => $value) {
			if($value === '' && $key!='art_cover'){
				echo "<script>alert('请将必填项填完');history.go(-1)</script>";
				exit;		
			}
		}
		//将之前的封面删掉
		if(isset($data['art_cover'])){
			$img = M('article')->where(array('art_id'=>$data['art_id']))->getField('art_cover');
			if($img!="") @unlink(DISK_ROOT_PATH . self::COVERS_PATH . $img);
		}
		M('article')->save($data);
		$this->redirect('King/Article/articleList');
	}

	/**
	 * 删除资讯
	 * @return [type] [description]
	 */
	public function deleteArticle(){
		$data['art_id'] = I('art_id');
		$data['art_status'] = 0;
		if(M('article')->save($data)){
			$this->ajaxReturn('ok',JSON);
		}
	}

	/**
	 * 展示资讯类别列表
	 * @return [type] [description]
	 */
	public function articleType(){
		$type_group = I("type_group");
		if($type_group==1 || empty($type_group)){
			$condition['art_type_group'] = 1;
		}
		else{
			$condition['art_type_group'] = 2;
		}
		$this->art_type_group = $condition['art_type_group'];
		$this->type = M('art_type')->where($condition)->select();
		$this->display();
	}

	/**
	 * 修改分类
	 * @return [type] [description]
	 */
	public function editType(){
		$data['art_type_id'] = I('art_type_id');
		$data['art_type_name'] = I('art_type_name');
		if(M('art_type')->save($data)){
			$this->ajaxReturn('ok',JSON);
		}
	}

	/**
	 * 删除分类
	 * @return [type] [description]
	 */
	public function deleteType(){
		$condition['art_type_id'] = I('art_type_id');
		if(M('art_type')->where($condition)->delete()){
			$this->ajaxReturn('ok',JSON);
		}
	}

	/**
	 * 添加分类
	 */
	public function addType(){
		$data['art_type_group'] = I("art_type_group");
		$data['art_type_name'] = I('art_type_name');
		if($art_type_id = M('art_type')->add($data)){
			$this->ajaxReturn(array('status'=>'ok','art_type_id'=>$art_type_id),JSON);
		}
	}

}