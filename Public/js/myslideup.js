// JavaScript Document
	 var wheight =$(window).height();
	$(function(){
	    var heig = wheight - 140; //140
	    $('.container').css('height',heig);
	})
	$(document).ready(function(){      
	  $(".openable a").each(function(){       
	    $this = $(this);          
	    if($this[0].href==String(window.location)){              
	      $this.addClass("active");    
	    }
	  });  
	}); 

	$(function(){ 							//控制导航栏滑动效果
		var menuhe =  wheight - 72;
		$(".menu").css('height',menuhe);  //高度防止小屏下导航栏目不滚动 
	  	$(".menu").mouseenter(function(){  //鼠标移动效果
		  $(".menu").css("right","0px");
			}); 
	  $(".menu").mouseleave(function(){  	//鼠标移动效果
		  $(this).css("right","-160px");
			}); 
	}); 


	   // $(document).ready(function(){
	   //       $(".accordion a").each(function(){
	   //                 $(this).click(function(){
	   //                 $(".accordion .active").removeClass("active");              
	   //                 $(this).addClass("active");                
	   //                 return false;   
	   //               });      
	   //          });  
	   //     });  
//兼容safais
	  $(function(){
        var explorer =navigator.userAgent ;
          //ie 
          if (explorer.indexOf("MSIE") >= 0) {
          // alert("ie");
          }
          // //firefox 
          // else if (explorer.indexOf("Firefox") >= 0) {
          // alert("Firefox");
          // }
          //Chrome
          else if(explorer.indexOf("Chrome") >= 0){
          // alert("Chrome");
          }
          // //Opera
          // else if(explorer.indexOf("Opera") >= 0){
          // alert("Opera");
          // }
          //Safari
          else if(explorer.indexOf("Safari") >= 0){
           $('body').addClass('Safari');
          } 
          // //Netscape
          // else if(explorer.indexOf("Netscape")>= 0) { 
          // alert('Netscape'); 
          // } 
  })
