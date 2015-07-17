/**
 * 
 * @authors Your Name (you@example.org)
 * @date    2015-07-07 12:51:46
 * @version 1.0
 */
 //响应式设置(Zepto/JQ版)
$(document).ready(function(){
	var $windowWidth = $(window).width();
	setTimeout(function(){
		$windowWidth = $(window).width();
		if($windowWidth > 640){
			$windowWidth = 640;            		//限定最大宽度为640
		}
		$("html").css("font-size",(100/320) * $windowWidth + "px");
	},100);
	
	$(window).resize(function(){
		$windowWidth = $(window).width();
		if($windowWidth > 640){
			$windowWidth = 640;
		}
		$("html").css("font-size",(100/320) * $windowWidth + "px");
	});
	//淡入图片
	$("img").fadeIn("3000");
});


