<?php
namespace Handler\Controller;
use Think\Controller;
/**
 * 资讯控制器
 */
class ArticleController extends CommonController{
	//客户端发送的数据
	private static $_clientData;
	//资讯类
	private static $_article;

	public function _initialize(){
		parent::_initialize();
		self::$_clientData = parent::$data;
		self::$_article = new \Handler\Model\ArticleModel();
	}

	/**
	 * 获取资讯
	 * @return [type] [description]
	 */
	public function getArticleList(){
		$type = self::$_clientData['type'];
		$art_type_id = self::$_clientData['art_type_id'];
		$action = self::$_clientData['action'];
		$art_id = self::$_clientData['art_id'];

		$returnData = self::$_article->getArticleList($type,$art_type_id,$action,$art_id);
		exit($returnData);

	}
	/**
	 * 获取活动分类
	 * @return [type] [description]
	 */
	public function getActivityType(){
		$returnData = self::$_article->getActivityType();
		exit($returnData);

	}

	/**
	 * 获取指定的资讯内容
	 * @return [type] [description]
	 */
	public function getArticle(){
		$art_id = self::$_clientData['art_id'];
		$returnData = self::$_article->getArticle($art_id);
		exit($returnData);
	}

	/**
	 * 获取资讯分享链接
	 * @return [type] [description]
	 */
	public function getShareLink(){
		$art_id = self::$_clientData['art_id'];
		$returnData = self::$_article->getShareLink($art_id);
		exit($returnData);
	}
}