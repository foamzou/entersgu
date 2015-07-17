<?php
namespace Handler\Model;
use Think\Model;

/**
 * 图书馆模型
 * 这里使用了开源的QueryList类作为页面抓取工具。该类可以在本项目ThinkPHP/Library/Org/Jae中找到。
 */
class LibraryModel extends Model{
	protected $autoCheckFields = false;
	
	const PREFIX_URL = 'http://210.38.207.15:169/web/';	 	//图书馆基础链接
	const SEARCH_URL = 'searchresult.aspx?';	 	//检索链接
	const BOOK_INFO_URL = 'bookinfo.aspx?';	 	//图书详细页链接
	private $searchTypeArr = array('anywords','title','author','isbn_f');

	public function _initialize(){
		//导入QueryList类
		import('Org.Jae.QueryList');
	}


	/**
	 * 检索方法
	 * @param  [type] $searchType [查询类型，参数={0,1,2,3}分别表示任意字段、题名、作者和ISBN]
	 * @param  [type] $keyword    [查询关键字]
	 * @param  [type] $page   	  [页码，默认为1]
	 * @return [type]             [0检索成功，1结果数为0，-1检索系统出现故障]
	 */
	public function search($searchType,$keyword,$page=1){
		// 初始化一个 cURL 对象 
		header("Content-type: text/html; charset=utf-8"); 
		$keyword = iconv("utf-8", "gb2312",$keyword);
		$keyword = urlencode($keyword);
		$type = $this->searchTypeArr[$searchType];
		$url = self::PREFIX_URL.self::SEARCH_URL."$type=$keyword&dt=ALL&cl=ALL&dp=20&sf=M_PUB_YEAR&ob=DESC&sm=table&dept=ALL&page=$page";

		//抓取图书基本信息(结果数,页码和总页数)
		$reg = array(
			'rsSum' => array('#ctl00_ContentPlaceHolder1_countlbl','text'),
			'currentPage'=>array('#ctl00_ContentPlaceHolder1_dplblfl2','text'),
			'pageSum'=>array('#ctl00_ContentPlaceHolder1_gplblfl2','text'),
			'notFound'=>array('#ctl00_ContentPlaceHolder1_notfoundcountlbl','text'),
			);
		$query = \QueryList::Query($url,$reg);
		$etcInfo = $query->jsonArr[0];
		//没有获取到图书馆的数据，可能要检查下能否连接到网址。希望不要变成校园局域网
		if(empty($etcInfo)) return json_encode(array('code'=>-1,'message'=>'The server cannot connect sguLibrary'));
		//检索结果数为0。
		if($etcInfo['notFound']=='0') return json_encode(array('code'=>1,'message'=>'nothing about your search'));
		//列表
		$reg = array(
				'bookId' => array('td a','href','',function($url){return str_replace('bookinfo.aspx?ctrlno=','',$url);}),
				'bookName' => array('td:eq(1)','text'),
				'author' => array('td:eq(2)','text'),
				'publishingHouse' => array('td:eq(3)','text'),
				'publishTime' => array('td:eq(4)','text'),
				'ISBN' => array('td:eq(5)','text'),
				'bookSum' => array('td:eq(6)','text'),
				'bookRemain' => array('td:eq(7)','text')
			);
		$rang = '#searchresultpagefl tbody tr:not([class])';
		$query->setQuery($reg,$rang);
		$bookInfo = $query->jsonArr;

		$rs = array('code'=>0,'message'=>'successfully','etcInfo'=>$etcInfo,'bookInfo'=>$bookInfo);
		$rs = json_encode($rs);
		return $rs;
		
	}



	/**
	 * 获取图书信息
	 * @param  [type] $bookId [description]
	 * @return [type]         [description]
	 */
	public function getBookInfo($bookId){
		
		//首先是抓取图书基本信息
		$reg = array(
			'baseInfo' => array('#ctl00_ContentPlaceHolder1_bookcardinfolbl','text'),
			);
		$query = \QueryList::Query(self::PREFIX_URL.self::BOOK_INFO_URL.'ctrlno='.$bookId,$reg);
		$baseInfo = $query->jsonArr;
		//没有获取到图书馆的数据，可能要检查下能否连接到网址。或者图书ID不存在
		if(empty($baseInfo)) return json_encode(array('code'=>-1,'message'=>'The server cannot connect sguLibrary'));
		//抓取借阅情况
		$reg = array(
				'address' => array('td:eq(0)','text'),
				'getNO' => array('td:eq(1)','text'),
				'status' => array('td:eq(5)','text')
			);
		$rang = '#bardiv tbody tr:not([class])';
		$query->setQuery($reg,$rang);
		$borrowInfo = $query->jsonArr;
		$rs['code'] = 0;
		$rs['message'] = 'Success';
		$rs['baseInfo'] = $baseInfo[0]['baseInfo'];
		$rs['borrowInfo'] = $borrowInfo;
		return json_encode($rs);
	}

	/**
	 * [Get 请求]
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	private function http_get($url){
		$curl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($curl);
		$aStatus = curl_getinfo($curl);
		curl_close($curl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	/**
	 * POST 请求
	 * @param  [type] $url  [description]
	 * @param  [type] $data [POST的数据]
	 * @return [type]       [description]
	 */
	private function http_post($url,$data){
		$curl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($curl, CURLOPT_POST,true);
		curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
		$sContent = curl_exec($curl);
		$aStatus = curl_getinfo($curl);
		curl_close($curl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}


}
