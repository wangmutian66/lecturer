$(function(){
		
    /*tab标签切换*/
    function tabs(tabTit,on,tabCon){
	$(tabCon).each(function(){
	  $(this).children().eq(0).show();
	 
	  });
	$(tabTit).each(function(){
	  $(this).children().eq(0).addClass(on);
	  });
     $(tabTit).children().click(function(){
        $(this).addClass(on).siblings().removeClass(on);
         var index = $(tabTit).children().index(this);
         $(tabCon).children().eq(index).show().siblings().hide();
    });
     }
  tabs(".investment_title","on",".investment_con");
  
 })

//记忆选中的导航栏目
$(document).ready(function(){      
  $(".menu").each(function(){       
    $this = $(this);          
    if($this[0].href==String(window.location)){              
      $this.addClass("color-white");    
    }
  });  
}); 

//小屏模式下导航栏自动收起
$(document).ready(function(){
  var scroll_top = $(window).scrollTop();
  $(window).scroll(function(){
      if($(window).scrollTop() >= 80){
         $('.yui-header').css('top','-70px'); 
         $('.yui-foottext').css('bottom','-70px');
        };
        if($(window).scrollTop() <= 60){

         $('.yui-header').css('top','0px'); 
         $('.yui-foottext').css('bottom','0px');
        };
     });
});


	
