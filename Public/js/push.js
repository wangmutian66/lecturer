window.onload = function()
{ 

	pushshow();
} 

function pushshow(p)
{
	if(p==undefined) 
 {
		p = 1;
		page(p);
 }
	 var str = "";
	  $.post(push_pushshow,{'page':p,'type':'0'},function(t)
			  {	
		  		 var datacan = t;
		  		 var str  = '';
		  		 var btn="";
		  		 for(i in datacan)
		  		 {
		  			if(datacan[i].state == '1')
		  			{
		  					str += "<tr><td>"+datacan[i].content+"</td><td>"+datacan[i].time+"</td><td>推送成功</td><td><input type='button' class='costup secc'  value='推送' disabled='disabled' onclick='saveconet("+datacan[i].id+")'  /></td></tr>";	
		  				//$("#tuisong").attr("disabled",true); 
		  			}
		  			else
		  			{
		  				if(datacan[i].reason == false || datacan[i].reason == null)
		  				{
		  					datacan[i].reason ="未找到";
		  				}
		  				str += "<tr><td>"+datacan[i].content+"</td><td>"+datacan[i].time+"</td><td style='color:#e6412c'>推送失败:"+datacan[i].reason+"</td><td><input type='button' class='costup'  value='推送' onclick='saveconet("+datacan[i].id+")'  /></td></tr>";	
		  				//btn=" disabled=false ";
		  				//$("#tuisong").attr("disabled",false); 
		  			}
		  			// str += "<tr><td>"+datacan[i].content+"</td><td>"+datacan[i].time+"</td><td>"+state+"</td><td><input class='costup' type='button' id='tuisong' value='推送' "+btn+" onclick=saveconet("+datacan[i].id+")></td></tr>";
		  		}
		  		$('#pushtml').html(str) 
		  	    $('#pushtml').prepend('<tr><td>内容</td><td>时间</td><td>状态</td><td>操作</td></tr> <tr><td><input type="text" class="push_content" placeholder="请输入您要推送的内容....." id="coentval"></td><td></td><td></td><td><input class="costup" type="button" value="推送" onclick="addconent()" ></td></tr>');


		  	  $(function(){
					$("tr:even").css("background","rgb(245, 245, 245);");
					$("tr:odd").css("background","white");
					})

		  
			  },'json');
	
	
}

function page(page)
{
 var nums ='';
	$.ajax({
	type: "GET",
	url:push_page,
	dataType:"json",
	async:false,
	error: function(data) {
	},
	success: function(data) {
		nums = eval(data);
	}
	});

	$(".tcdPageCode").remove();
	$("#pushtml").after("<div class='tcdPageCode'></div>");
	$(".tcdPageCode").createPage({
				pageCount:nums,
				current:page,
				backFn:function(p)
				{
					pushshow(p);
			}
		 });
}
function saveconet(id)
{
   $.ajax({
      type: "POST",
      url:push_savestage,
      data: {id:id},
      dataType:'json', 
      success:function(data)
      {
      	if(data.errcode == 1)
      	{
  		  Showbo.Msg.alert('推送成功');
  		  $('#coentval').val(' ');
  		  $('#tuisong').prop('disabled',true);
  		  pushshow();
      	}
      	else
      	{
      	 Showbo.Msg.alert('推送失败');
      	}
      }
      });

}
function addconent()
{
	var val = $('#coentval').val();
	if(val == '')
	{
		 Showbo.Msg.alert('推送内容不能为空');
		 return false;
	}
      $.ajax({
      type: "POST",
      url:push_addstage,
      data: {type:0,content:val},
      dataType:'json', 
      success:function(data)
      {
      	if(data.errcode == 1)
      	{
  		  Showbo.Msg.alert('推送成功');
  		  $('#coentval').val(' ');
  		  pushshow();
      	}
      	else
      	{
      	 Showbo.Msg.alert('推送失败');
      	}
      }
      });
	
}