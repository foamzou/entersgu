<?php
function p($data){
	echo '<pre/>';
	var_dump($data);
	echo '<pre/>';
}

/**
 * 友好化时间
 * @param  [type] $time [时间戳]
 * @return [type]       [超过一星期，直接显示时间。否则显示XX前]
 */
function toUIDate($time){  
    $dTime = time()-$time;  

    if($dTime>=604800){
      return date('Y-m-d',$time);
    }

    $f=array(  
        '31536000'=>'年',  
        '2592000'=>'个月',  
        '604800'=>'星期',  
        '86400'=>'天',  
        '3600'=>'小时',  
        '60'=>'分钟',  
        '1'=>'秒'  
    );  
    foreach ($f as $key=>$value)    {  
        if (0 !=$c=floor($dTime/(int)$key)) {  
            return $c.$value.'前';  
        }  
    }  
}

/**
 * 友好化列表时间
 * @param  [type] $time [description]
 * @return [type]       [今天则返回今天，否则返回X月XX]
 */
function toListDate($time){
    $today = date('n月d',time());
    $time = date('n月d',$time);
    return $today==$time ? '今天' : $time;
}