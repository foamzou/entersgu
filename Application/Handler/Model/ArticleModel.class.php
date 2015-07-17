<?php
/**
 * 资讯实体类
 */
namespace Handler\Model;
use Think\Model;
class ArticleModel extends Model{
	//this is a virtual model,haha~~
	protected $autoCheckFields = false;
	public static $COVER_SHOW_PATH ; //封面显示路径
	public static $SHARE_PAGE_URL = '';	//分享页面的URL
	public static $CONTENT_PAGE_URL = '';	//正文页面的URL
	const TRANSLATE_SIZE = 10;	//每次传输的资讯数量
	const ESSAY_GROUP = 1;		//文章分类为1
	const ACTIVITY_GROUP = 2;	//活动分类为2

	public function _initialize(){
		self::$COVER_SHOW_PATH = C('ROOT_PATH').'Public/UploadFiles/covers/';
		self::$SHARE_PAGE_URL = C('ROOT_PATH').'index.php/App/Article/share/art_id/';
		self::$CONTENT_PAGE_URL = C('ROOT_PATH').'index.php/App/Article/detail/art_id/';
	}

	/**
	 * 获取资讯
	 * @param  [type] $type   [资讯分类。0最新资讯，1文章杂烩，2韶院活动，3校务通知]
	 * @param  [type] $art_type_id   [活动分类ID,当type为2时，才可指定该参数，不指定则为获取所有类别的韶院活动]
	 * @param  [type] $action [1.获取最新的10条资讯(无需指定资讯id)；2.获取指定资讯id早前的10条资讯；3.获取比指定资讯ID还要新的所有资讯]
	 * @param  [type] $art_id [资讯ID]
	 * @return [type]         [description]
	 */
   	public function getArticleList($type,$art_type_id,$action,$art_id){
   		//资讯分类条件
   		$typeMap = array('0'=>'1',								//最新资讯
   						'1'=>'art_type_id=2',					//文章杂烩
   						'2'=>'art_type_group='.self::ACTIVITY_GROUP,	//韶院活动
   						'3'=>'art_type_id=1'					//校务通知
   						);
   		//如果检索的是具体分类的韶院活动
   		if($type==2 && is_numeric($art_type_id)){
   			$condition = 'art_type_id='.$art_type_id;
   		}
   		else{
   			$condition = $typeMap[$type];
   		}
   		
   		if(!isset($typeMap[$type])){
   			return json_encode(array('code'=>2,'message'=>'Param of type is invalid'));
   		}
		//获取方式
		switch ($action) {
			case '1':
				$data = M()->query('SELECT art_id,art_title,art_cover,art_time,team_name
									FROM entersgu_article a INNER JOIN entersgu_team t ON a.art_author=t.team_id 
									WHERE art_status=1 AND '.$condition.' ORDER BY a.art_id DESC LIMIT 0,'.self::TRANSLATE_SIZE);
				break;
			
			case '2':
				$data = M()->query('SELECT art_id,art_title,art_cover,art_time,team_name
									FROM entersgu_article a INNER JOIN entersgu_team t ON a.art_author=t.team_id 
									WHERE art_id<'.$art_id.' AND art_status=1 AND '.$condition.'  ORDER BY a.art_id DESC LIMIT 0,'.self::TRANSLATE_SIZE);
				break;

			case '3':
				$data = M()->query('SELECT art_id,art_title,art_cover,art_time,team_name
									FROM entersgu_article a INNER JOIN entersgu_team t ON a.art_author=t.team_id 
									WHERE art_id>'.$art_id.' art_status=1 AND '.$condition.' ORDER BY a.art_id DESC');
				break;

			//action参数不合法
			default:
				$rs['code'] = 1;
				$rs['message'] = 'param of action is invalid,please send 1 to 3';
				return json_encode($rs);
		}

		foreach ($data as $key => $value) {
			//给封面加上链接前缀
			if($value['art_cover']!=''){
				$data[$key]['art_cover'] = self::$COVER_SHOW_PATH.$data[$key]['art_cover'];
			}
			//添加页面链接
			$data[$key]['art_url'] = self::$CONTENT_PAGE_URL . $data[$key]['art_id'];
			//友好化时间
			$data[$key]['art_time'] = toUIDate(strtotime($value['art_time']));
		}
		$rs['code'] = 0;
		$rs['message'] = 'Success';
		$rs['articleInfo'] = $data;
		return json_encode($rs);
   	}

   	/**
   	 * 获取活动分类
   	 * @return [type] [description]
   	 */
   	public function getActivityType(){
   		$typeList = M('art_type')->field('art_type_id,art_type_name')->where(array('art_type_group'=>ACTIVITY_GROUP))->select();
   		if($typeList){
   			return json_encode(array('code'=>1,'message'=>'Success','typeList'=>$typeList));
   		}
   		else{
   			return json_encode(array('code'=>-1,'message'=>'Exception error'));
   		}
   	}

   	/**
   	 * 获取指定的资讯内容
   	 * @param  [type] $art_id [description]
   	 * @return [type]         [description]
   	 */
   	public function getArticle($art_id){
   		$articleInfo = M('article')->field('art_title,t.team_name,art_time,art_cover,art_type_group,art_start_time,art_end_time,art_address')
   						->join('entersgu_team t on entersgu_article.art_author = t.team_id')
   						->where(array('art_id'=>$art_id))
   						->find();
   		if(!$articleInfo){
   			return json_encode(array('code'=>-1,'message'=>'Exception error'));
   		}
   		//非活动资讯则去掉冗余信息
   		if($articleInfo['art_type_group'] == self::ESSAY_GROUP){
   			unset($articleInfo['art_start_time']);
			unset($articleInfo['art_end_time']);
			unset($articleInfo['art_address']);
			$type = self::ESSAY_GROUP;
   		}
   		else{
   			$type = self::ACTIVITY_GROUP;
   		}

   		//时间友好化
   		$articleInfo['art_time'] = toUIDate(strtotime($articleInfo['art_time']));
   		unset($articleInfo['art_type_group']);

   		//正文链接
   		$articleInfo['content_link'] = self::$CONTENT_PAGE_URL . $art_id;

   		return json_encode(array('code'=>0,'message'=>'Success','type'=>$type,'articleInfo'=>$articleInfo));

   	}

   	/**
   	 * 获取资讯分享链接
   	 * @param  [type] $art_id [description]
   	 * @return [type]         [description]
   	 */
   	public function getShareLink($art_id){
   		return json_encode(array('code'=>0,'message'=>'Success','link'=>self::$SHARE_PAGE_URL.$art_id));
   	}

}

