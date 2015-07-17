<?php
/**
 * 资讯管理控制器
 */
namespace App\Controller;
use Think\Controller;
class ArticleController extends Controller{

	/**
	 * 详细页，分享出去的网页也是用这个方法
	 * @return [type] [description]
	 */
	public function detail(){
		$art_id = I('art_id');
		$this->article = M('article')->where(array('art_id'=>$art_id))
									->join('entersgu_team t on entersgu_article.art_author = t.team_id')
									->find();
		$this->display();
	}

	/**
	 * 展示正文，用于在客户端上显示
	 * @return [type] [description]
	 */
	public function showContent(){
		$art_id = I('art_id');
		$this->content = M('article')->where(array('art_id'=>$art_id))->getField('art_content');
		$this->display();
	}




}