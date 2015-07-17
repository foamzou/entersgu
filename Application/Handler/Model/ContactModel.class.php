<?php
/**
 * 校园电话簿模型类
 */
namespace Handler\Model;
use Think\Model;
class ContactModel extends Model{
	//this is a virtual model,haha~~
	protected $autoCheckFields = false;

	/**
	 * 获取校园电话簿列表
	 * @param  [type] $sort [1字典排序，2热门电话排序]
	 * @return [type]       [description]
	 */
	public function getContactList($sort){ 
		if($sort==1){
			$order = 'CONVERT(contact_name USING gbk) COLLATE gbk_chinese_ci ASC';
		}
		elseif($sort==2){
			$order = 'contact_count DESC';
		}
		else{
			return json_encode(array('code'=>1,'message'=>'The param is invaild'));
		}
		$contactList = M('contact')->order($order)->select();
		if($contactList){
			$rs['code'] = 0;
			$rs['message'] = 'Success';
			$rs['contactList'] = $contactList;
		}
		else{
			$rs['code'] = -1;
			$rs['message'] = 'Exception error';
		}
		return json_encode($rs);
	}

	/**
	 * 增加拨打次数
	 * @param [type] $contact_id [description]
	 */
	public function addCallCount($contact_id){
		if(M('contact')->where(array('contact_id'=>$contact_id))->setInc('contact_count')){
			return json_encode(array('code'=>0,'message'=>'Success'));
		}
		else{
			return json_encode(array('code'=>-1,'message'=>'Exception error'));
		}
	}


}

