/**
 * 
 */


(function($){
	$.fn.extend({
		//双击放大
		
		
		
		dbclickEnlarge:function(element){
		    var obj= $(this);
			var initWidth=obj.width();
			var initHeight=obj.height();
			
			
			
			var element=$(element);
			var winwidth=$(window).width();
			var winheight=$(window).height();
			var borderWidth=parseInt($(element).css('border-width'));
			var initleft=0;
			var inittop=0;
			var initwidth=0;
			
			
			
			obj.dblclick(function(){
			     
				if($(this).hasClass('enlarge')){
					$(this).removeClass('enlarge');
					$('.movebtn').hide();
					$(".movebox").removeAttr("style");
					element.animate({
						"left":initleft,
						"top":inittop,
						//"width":initwidth,
						//"height":"200px"
					},50);
					$('.movediv .table-box').show();
					$('.xuanfukuang1').hide();
				}else{
					$(this).addClass('enlarge');
				    $('.movebtn').show();
				    $('.movediv .table-box').hide();
				    $('.treeimg').hide();
				   
				    $('.xuanfukuang1').show();
					var contop=$('.table-box').offset().top;
					var conleft=$('.table-box').offset().left;
					var conwidth=$('.table-box').width();
					var conheight=$('.table-box').height();
					
					
					element.animate({
						"left":"10px",
						"top":"50px",
						"width":conwidth-40,
						"height":conheight
					},30);
					
					
					
					initleft=parseInt($(element).css('left'));
					inittop=parseInt($(element).css('top'));
					initwidth=parseInt($(element).css('width'));
					/*
				    element.animate({
						"left":"0",
						"top":"0",
						"width":winwidth-borderWidth*2,
						"height":winheight-borderWidth*2
					},30);*/
				}
				
			});
			
			
			
		},
		
		//移动
	    mtmove:function(element){
		    var obj=$(this); 
		   
		    
			var borderWidth=2;
			obj.css({"position":"absolute","top":"0","left":"0"});
			
			var moveObj=obj;
			
			if(typeof(element)!="undefined"){
			   moveObj=$(element)
			}
			
		
			
			moveObj.mousedown(function(e){
				
				if($(this).hasClass('enlarge')){
					return false;
				}else{
				
					var pagex1=e.pageX-parseInt(moveObj.offset().left)+$('.movediv').offset().left;
					var pagey1=e.pageY-parseInt(moveObj.offset().top)+$('.movediv').offset().top;
					
					$(document).mousemove(function(e){
						$('.treeimg').hide();
						var pagex=e.pageX;
						var pagey=e.pageY;
						
						var panduan1=((pagex-pagex1)>=0);
						var panduan2=((pagex-pagex1)<=($('.movediv').width()-obj.width()-borderWidth*2));
						var panduan3=((pagey-pagey1)>=0);
						var panduan4=((pagey-pagey1)<=($('.movediv').height()-borderWidth*2-obj.height()));
					   
					  
					    
						if(panduan1 && panduan2 && panduan3	&& panduan4){
							 
						     obj.css({"top":pagey-pagey1,"left":pagex-pagex1});
						}
						
						
					}).mouseup(function(){
						
					   $(this).unbind("mousemove"); 
					  
					   if($(".movebox").hasClass('enlarge')){
							return false;
					   }else{//粘边效果
						   upleft=parseInt(obj.css('left'));
						   uptop=parseInt(obj.css('top'));
						   
						   boundaryWidth=($('.movediv').width()-obj.width())/2;
						   boundaryHeight=($('.movediv').height()-obj.height())/2;
						   
						   if(upleft<boundaryWidth){
							   obj.css({"left":"0"});
						   }else{
						       obj.css({"left":$('.movediv').width()-obj.width()-borderWidth*2});
						   }
						   
						   if(uptop<boundaryHeight){
						       obj.css({"top":"0"});
						   }else{
						       obj.css({"top":$('.movediv').height()-obj.height()-borderWidth*2});
						   }  
					   }
					   
					  
					});
				}
			})
		}

		
	});
})(jQuery);



$(document).ready(function(){
	var i=0;
	var j=0;
	arrwidth=new Array();
	
	$(window).resize(function(){
		$('.movediv').css("width",$(this).width()-50);
		$('.enlarge').css("width",$(this).width()-100);
		//$('.movebox').css({"top":0,"left":0});
		
	  
		windowSize();
	});
	
	function windowSize(){
		if($('.enlarge').length>0){
			if($('.enlarge').width()<=($('.tree').width()+1)){
				Showbo.Msg.alert("树状图已经超出屏幕,请选择下载Excel");
				
			}
		}
	}
	
	
	
	
});


/**
 * 在本地进行文件保存
 * @param  {String} data     要保存到本地的图片数据
 * @param  {String} filename 文件名
 */
var saveFile = function(data, filename){
    var save_link = document.createElementNS('http://www.w3.org/1999/xhtml', 'a');
    save_link.href = data;
    save_link.download = filename;
   
    var event = document.createEvent('MouseEvents');
    event.initMouseEvent('click', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
    save_link.dispatchEvent(event);
};




function savepic() {
    if (document.all.a1 == null) {
        objIframe = document.createElement("IFRAME");
        document.body.insertBefore(objIframe);
        objIframe.outerHTML = "<iframe name=a1 style='width:400px;hieght:300px' src=" + imageName.href + "></iframe>";
        re = setTimeout("savepic()", 1)
    }
    else {
        clearTimeout(re)
        pic = window.open(imageName.href, "a1")
        pic.document.execCommand("SaveAs")
        document.all.a1.removeNode(true)
    }
} 


