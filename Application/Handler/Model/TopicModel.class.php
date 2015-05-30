<?php
namespace Handler\Model;
use Think\Model;
/**
 * 动态模型
 */
class TopicModel extends Model{
	protected $autoCheckFields = false;
	const IMG_SAVE_PATH = 'Public/Handler/TopicImage/';	//动态图片存储路径
	const PAGE_SIZE = 10;	//每页的数量

	/**
	 * 添加动态
	 * @param [type] $tp_content   [description]
	 * @param [type] $u_id         [description]
	 * @param [type] $tp_img       [description]
	 * @param [type] $tp_anonymous [description]
	 */
	public function addTopic($tp_content,$u_id,$tp_img,$tp_anonymous){
		//是否存在图片
		if(isset($tp_img)){
			//将BASE64解码转换为图片
			$imgName = md5(time().$u_id);
			//存储到指定的路径
			file_put_contents(self::IMG_SAVE_PATH.$imgName,base64_decode($tp_img));
			$data['tp_img'] = $imgName;
		}
		$data['tp_content'] = $tp_content;
		$data['u_id'] = $u_id;
		$data['tp_anonymous'] = $tp_anonymous;
		$data['tp_time'] = date('Y-m-d H:i:s',time());
		$data['tp_up'] = $tp_up;
		if(M('topic')->add($data)){
			$rs['code'] = '0';
			$rs['message'] = 'Success';
		}
		else{
			$rs['code'] = '-1';
			$rs['message'] = 'Exception error';
		}
		return json_encode($rs);
	}

	/**
	 * 删除指定动态
	 * @param  [type] $u_id  [description]
	 * @param  [type] $tp_id [description]
	 * @return [type]        [description]
	 */
	public function delTopic($u_id,$tp_id){
		$condition['u_id'] = $u_id;
		$condition['tp_id'] = $tp_id;
		//检查动态是否存在和权限问题
		$topic = M('topic')->where($condition)->find();
		if($topic){
			if(M('topic')->where($condition)->delete()){
				$rs['code'] = 0;
				$rs['message'] = 'Success';
			}
			//未知错误
			else{
				$rs['code'] = -1;
				$rs['message'] = 'Exception error';
			}
		}
		//该条动态不存在或没有删除权限
		else{
			$rs['code'] = 1;
			$rs['message'] = 'The topic is not exist or the user had not permission to do this';
		}
		return json_encode($rs);
	} 

}