<?php
namespace Home\Model;
use Think\Model;
/**
 * 图书馆模型
 */
class LibraryModel extends Model{
	protected $autoCheckFields = false;
	private $searchTypeArr = array('anywords','title','author','isbn_f');

	
	/**
	 * 检索方法
	 * @param  [type] $type       [查询类型，参数={0,1,2,3}分别表示任意字段、题名、作者和ISBN]
	 * @param  [type] $keyword    [查询关键字]
	 * @param  [type] $returnMode [返回类型，参数={0,1}分别表示Json和数组]
	 * @param  [type] $page   	  [页码，默认为1]
	 * @return [type]             [description]
	 */
	public function search($searchType,$keyword,$returnMode,$page=1){
		// 初始化一个 cURL 对象 
		header("Content-type: text/html; charset=utf-8"); 
		$curl = curl_init();
		$keyword = iconv("utf-8", "gb2312",$keyword);
		$keyword = urlencode($keyword);
		$type = $this->searchTypeArr[$searchType];
		$url = "http://210.38.207.15:169/web/searchresult.aspx?$type=$keyword&dt=ALL&cl=ALL&dp=20&sf=M_PUB_YEAR&ob=DESC&sm=table&dept=ALL&page=$page";
		// 设置你需要抓取的URL 
		curl_setopt($curl, CURLOPT_URL, $url); 
		// 设置header 
		// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。 
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4'));
		// 运行cURL，请求网页 
		$data = curl_exec($curl); 

		//匹配结果正文 
		$pattern = '#href="bookinfo\.aspx\?ctrlno\=([^"]+)" target="_blank">([^"]+)</a></span></td>.*<td>([^"]+)</td>.*<td>([^"]+)</td>.*<td>([^"]+)</td>.*<td class="tbr">([^"]+)</td>.*<td class="tbr">([^"]+)</td>.*<td class="tbr">([^"]+)</td>#iUs';
		preg_match_all($pattern, $data, $rsContent);
		
		//匹配页码、结果数
		$pattern = '#<span id="ctl00_ContentPlaceHolder1_countlbl"><font color="Red">([^"]+)</font></span>.*第<span class="rf"><span id="ctl00_ContentPlaceHolder1_dplblfl1">([^"]+)</span></span>/<span id="ctl00_ContentPlaceHolder1_gplblfl1">([^"]+)</span>页#iUs';
		preg_match($pattern, $data, $rsEtc);

		// 关闭URL请求 
		curl_close($curl);
		//去掉数组中第一项冗余值
		unset($rsContent[0]);
		unset($rsEtc[0]);
		$rs = array($rsEtc,$rsContent);

		if($returnMode==1){
			return $rs;
		}
		else{
			$rs = array('code=0',$rs);
			$rs = json_encode($rs);
			return $rs;
		}
	} 




}