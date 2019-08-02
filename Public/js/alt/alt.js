var alt_title = '';
var affirm_function_name;
var cancel_function_name;
var timer;

//确认框
function ConfirmBox(content,affirm,cancel){
	affirm_function_name = affirm;
	cancel_function_name = cancel;
	var content = '<div class="alt_bg" style="position:fixed;left:0; right:0; top:0; bottom:0; z-index: 2;background-color:#000000;filter:alpha(opacity=10); -moz-opacity:0.1; -khtml-opacity: 0.1; opacity: 0.1;"></div><div class="alt_box" style="display:none;"><div class="alt_top"><span>'+alt_title+'</span><div class="alt_x" onClick="CloseBox()"><div class="alt_x_img"></div></div></div><div class="alt_conter"><div class="alt_img_box"><div class="alt_img_url1"></div></div><span class="prompt_message">'+content+'</span></div><div class="alt_bottom"><div class="alt_confirm" onClick="CloseBox()"></div><div class="alt_close" onClick="AffirmPromptBox()"></div></div></div>';
	$("body").prepend(content);
	$('.alt_box').slideDown('normal');
	//Scroll();
}
//提示框
function PromptBox(content,cancel){
	cancel_function_name = cancel;
	var content = '<div class="alt_bg" style="position:fixed;left:0; right:0; top:0; bottom:0; z-index: 2;background-color:#000000;filter:alpha(opacity=10); -moz-opacity:0.1; -khtml-opacity: 0.1; opacity: 0.1;"></div><div class="alt_box" style="display:none;"><div class="alt_top"><span>'+alt_title+'</span><div class="alt_x" onClick="CloseBox()"><div class="alt_x_img"></div></div></div><div class="alt_conter"><div class="alt_img_box"><div class="alt_img_url1"></div></div><span class="prompt_message">'+content+'</span></div><div class="alt_bottom"><div class="confirm_options" onClick="CloseBox()"></div></a></div>';
	$("body").prepend(content);
	$('.alt_box').slideDown('normal');
	//Scroll();
}
//确认框确认操作
function AffirmPromptBox(){
	$('.alt_box').slideUp('normal',RemoveBox);
	setTimeout(affirm_function_name, 600);
}
//关闭box
function CloseBox(){
	$('.alt_box').slideUp('normal',RemoveBox);
    $('.alt_box1').slideUp('normal',RemoveBox);
	setTimeout(cancel_function_name, 600);
}
//销毁
function RemoveBox(){
	$("body").css('overflow','auto');
	$('.alt_bg').remove();
	$('.alt_box').remove();
}
//固定调整高度
/*function Scroll(){
	clearInterval(timer);
    var topScroll=getScroll();
    var topDiv=$(window).height()/2;
    var top=topScroll+parseInt(topDiv);
    $(".alt_box").animate({"top":top},300);
    $("body").css('overflow','hidden');
}*/
//移动调整高度
$(function(){
    $(window).scroll(function(){
        clearInterval(timer);
        var topScroll=getScroll();
        var topDiv=$(window).height()/2;
        var top=topScroll+parseInt(topDiv);
        timer=setInterval(function(){
        	$(".alt_box").animate({"top":top},500);
            $(".alt_box1").animate({"top":top-200},500);
        },500)
    })
})
function getScroll(){
    var bodyTop = 0;  
    if (typeof window.pageYOffset != 'undefined') {  
        bodyTop = window.pageYOffset;  
    } else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {  
        bodyTop = document.documentElement.scrollTop;  
    }  
    else if (typeof document.body != 'undefined') {  
        bodyTop = document.body.scrollTop;  
    }  
    return bodyTop
}

function SelBox(datas,title){
    var date = new Date();
    var seperator1 = "-";
    var month = date.getMonth() + 1;
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    var nym = date.getFullYear() + seperator1 + month;
    
    var content = '<div class="alt_bg" style="position:fixed;left:0; right:0; top:0; bottom:0; z-index: 2;background-color:#000000;filter:alpha(opacity=10); -moz-opacity:0.1; -khtml-opacity: 0.1; opacity: 0.1;width:100%;"></div><div class="alt_box1" style="display:none;left:40%"><div class="alt_top" style="width:150%"><span>选择团队</span><div class="alt_x" onClick="CloseBox()"><div class="alt_x_img"></div></div></div><div class="alt_conter" style="height:auto;width:150%;border-radius: 0 0 15px 15px;">';
    var select = '<div class="templatemo-flex-row">';
    select += '<div class="templatemo-content-container" style="width:150%"><div class="row form-group"><div class="col-lg-1 col-md-2 form-group"></div><div class="col-lg-4 col-md-3 form-group"><label class="control-label templatemo-block">选择团队</label><select multiple="" id="select1" class="templatemo-multi-select form-control" style="overflow-y: scroll;">';
    for(i in datas){
        select += '<option value="'+datas[i].id+'">'+datas[i].name+'</option>';
    }
    select += '</select></div><div class="col-lg-1 col-md-1 form-group"><div><label class="control-label templatemo-block">&nbsp;</label><div class="btn-bar"><p id="add"><input type="button" class="btn" style="min-width:44px;" value=">" title="移动选择项到右侧"/></p><p id="remove"><input type="button" class="btn" style="min-width:44px;" value="<" title="移动选择项到左侧"/></p>';
    select += '</div></div></div><div class="col-lg-4 col-md-3 form-group"><div><label class="control-label templatemo-block">&nbsp;</label><select multiple="" id="select2" class="templatemo-multi-select form-control" style="overflow-y: scroll;"></select></div></div></div>';
    select += '<div class="row form-group"><div class="col-lg-1 col-md-2 has-warning form-group"></div><div class="col-lg-3 col-md-3 has-warning form-group"><label class="control-label" for="">开始时间</label><input type="text" class="form-control" onClick="setmonth(this)" readonly="readonly" id="st" value="'+nym+'"></div><div class="col-lg-3 col-md-3 has-warning form-group"><label class="control-label" for="">结束时间</label><input type="text" class="form-control" onClick="setmonth(this)" readonly="readonly" id="et" value="'+nym+'"></div><div class="col-lg-3 col-md-3 has-warning form-group"></div></div></div>';
    content += select;
    content += '</div><div class="alt_bottom" style="width:100%"><div class="alt_confirm" onClick="CloseBox()"></div><div class="alt_close" onClick="choseTeam()"></div></div>';
    
    $("body").prepend(content);
    //移到右边
    $('#add').click(function(){
        //先判断是否有选中
        if(!$("#select1 option").is(":selected")){      
          // alert("请选择需要移动的选项")
        }
        //获取选中的选项，删除并追加给对方
        else{
          $('#select1 option:selected').appendTo('#select2');
        } 
    });
  
      //移到左边
    $('#remove').click(function(){
        //先判断是否有选中
        if(!$("#select2 option").is(":selected")){      
          // alert("请选择需要移动的选项")
        }
        else{
          $('#select2 option:selected').appendTo('#select1');
        }
    });
  
    //双击选项
    $('#select1').dblclick(function(){ //绑定双击事件
        //获取全部的选项,删除并追加给对方
        $("option:selected",this).appendTo('#select2'); //追加给对方
    });
  
    //双击选项
    $('#select2').dblclick(function(){
        $("option:selected",this).appendTo('#select1');
    });

    $(".alt_box1").animate({"top":200},500);
    $('.alt_box1').slideDown('normal');

}