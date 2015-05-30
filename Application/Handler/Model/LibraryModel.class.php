<?php
namespace Handler\Model;
use Think\Model;
/**
 * 图书馆模型
 */
class LibraryModel extends Model{
	protected $autoCheckFields = false;
	
	const PREFIX_URL = 'http://210.38.207.15:169';	 	//图书馆基础链接
	const SEARCH_URL = '/web/searchresult.aspx?';	 	//检索链接
	private $searchTypeArr = array('anywords','title','author','isbn_f');

	/**
	 * 检索方法
	 * @param  [type] $searchType [查询类型，参数={0,1,2,3}分别表示任意字段、题名、作者和ISBN]
	 * @param  [type] $keyword    [查询关键字]
	 * @param  [type] $returnMode [返回类型，参数={0,1}分别表示Json和数组]
	 * @param  [type] $page   	  [页码，默认为1]
	 * @return [type]             [0检索成功，1结果数为0，-1检索系统出现故障]
	 */
	public function search($searchType,$keyword,$returnMode,$page=1){
		// 初始化一个 cURL 对象 
		header("Content-type: text/html; charset=utf-8"); 
		$curl = curl_init();
		$keyword = iconv("utf-8", "gb2312",$keyword);
		$keyword = urlencode($keyword);
		$type = $this->searchTypeArr[$searchType];
		$url = self::PREFIX_URL.self::SEARCH_URL."$type=$keyword&dt=ALL&cl=ALL&dp=20&sf=M_PUB_YEAR&ob=DESC&sm=table&dept=ALL&page=$page";

		$data = self::http_get($url);

		//没有获取到图书馆的数据，可能要检查下能否连接到网址。希望不要变成校园局域网、
		if(empty($data)) return json_encode(array('code'=>-1,'message'=>'The server cannot connect sguLibrary'));

		//匹配结果正文 
		$pattern = '#href="bookinfo\.aspx\?ctrlno\=([^"]+)" target="_blank">([^"]+)</a></span></td>.*<td>([^"]+)</td>.*<td>([^"]+)</td>.*<td>([^"]+)</td>.*<td class="tbr">([^"]+)</td>.*<td class="tbr">([^"]+)</td>.*<td class="tbr">([^"]+)</td>#iUs';
		preg_match_all($pattern, $data, $rsContentTemp);
		
		//匹配结果数,页码和总页数
		$pattern = '#<span id="ctl00_ContentPlaceHolder1_countlbl"><font color="Red">([^"]+)</font></span>.*第<span class="rf"><span id="ctl00_ContentPlaceHolder1_dplblfl1">([^"]+)</span></span>/<span id="ctl00_ContentPlaceHolder1_gplblfl1">([^"]+)</span>页#iUs';
		preg_match($pattern, $data, $rsEtc);

		//检索结果数为0。
		if(empty($rsEtc)) return json_encode(array('code'=>1,'message'=>'nothing about your search'));



		//去掉数组中第一项冗余值
		array_shift($rsContentTemp);
		array_shift($rsEtc);
		$rsEtc = array('rsSum'=>$rsEtc[0],'currentPage'=>$rsEtc[1],'pageSum'=>$rsEtc[2]);

		for($i=0;$i<count($rsContentTemp[0]);++$i){
			$rsContent[$i] = array('bookId'=>$rsContentTemp[0][$i],'bookName'=>$rsContentTemp[1][$i],'author'=>$rsContentTemp[2][$i],
				'publishingHouse'=>$rsContentTemp[3][$i],'publishTime'=>$rsContentTemp[4][$i],'ISBN'=>$rsContentTemp[5][$i],
				'bookSum'=>$rsContentTemp[6][$i],'bookRemain'=>$rsContentTemp[7][$i]);
		}


		$rs = array('etcInfo'=>$rsEtc,'bookInfo'=>$rsContent);

		if($returnMode==1){
			return $rs;
		}
		else{
			$rs = array('code'=>0,'message'=>'successfully','etcInfo'=>$rsEtc,'bookInfo'=>$rsContent);
			$rs = json_encode($rs);
			return $rs;
		}
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