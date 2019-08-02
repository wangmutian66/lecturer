/**
 * 
 */

$(document).ready(function(e) {
    getDepartment();
	$("#add").hide();
	allduties(1);
	interlaced();
	
});

function interlaced(){
	$("#nums div:odd").css("background-color","rgb(240, 240, 240)");
}

//选择部门后发生的事件
function department(page){
   $(".loding").show();	
   var deparVal=$("#department").val();
   if(deparVal==0){
      allduties(1);
	  return false;
   }
   
   
   $.ajax({
     type:"POST",
	 dataType:"json",
	 url:fee_getStation,
	 data:{"id":deparVal,"page":page},
	 success: function(data){
		 var stationData=data.station;
		 var dutiesData= data.duties;
		 var countpage=data.dutiesConPage;
		 $("#station option:gt(0)").remove();
		 for(var i in stationData){
			var row=stationData[i];
			$("<option value='"+row["id"]+"'>"+row["title"]+"</option>").appendTo($("#station"));
	   	 }
		 
		 $("#nums").html("");
		 for(var j in dutiesData){
			 var css="";
			 if(j%2==0){
				 css+="odd";
			 }
			 var str="";
			 str+="<div class=\""+css+" contentspages office"+dutiesData[j]["id"]+"\" id=\"pageheader\" onmouseover=\"rowover(this)\"  onmouseout=\"rowout(this)\">";
			 str+="<span class=\"width-50\">"+dutiesData[j]["title"]+"</span> ";
			 str+="<span class=\"width-50\">";
			 str+="<input type=\"text\" class=\"score\" value=\""+dutiesData[j]["score"]+"\" > ";
			 str+="<div class=\"Feebtn\"><input type=\"button\" class=\"posafterBtn posamove\" value=\"修改\"  onClick=\"upeffice(this,"+dutiesData[j]["id"]+")\"> </div> ";
			 str+="</span></div> ";
			 $("#nums").append(str);
	     }
		 
	
		 $(".tcdPageCodes").remove();
		 $(".width100felt").after("<div class='tcdPageCodes'></div>");	   
		   $(".tcdPageCodes").createPage({
				pageCount:countpage,
				current:page,
				backFn:function(p){
					department(p); 
					
				}
		   });
		 
		 $(".loding").hide();	
     },
	 error:function(data){
     }
	 
   });
   
   
}


function station1(page){
   
   $(".loding").show();	
   var station=$("#station").val();

   if(station==0){
	   department(1);
	   return false;
   }
   
   
   $.ajax({
     type:"POST",
	 dataType:"json",
	 url:fee_getStation,
	 data:{"id":station,"page":page},
	 success: function(data){
		
		 var dutiesData= data.duties;
		 var countpage=data.dutiesConPage;
		 $(".nums").html("");
		 for(var j in dutiesData){
			 var css="";
			 if(j%2==0){
				 css+="odd";
			 }
			 var str="";
			 str+="<div class=\""+css+" contentspages office"+dutiesData[j]["id"]+"\" id=\"pageheader\" onmouseover=\"rowover(this)\"  onmouseout=\"rowout(this)\">";
			 str+="<span class=\"width-50\">"+dutiesData[j]["title"]+"</span> ";
			 str+="<span class=\"width-50\">";
			 str+="<input type=\"text\" class=\"score\" value=\""+dutiesData[j]["score"]+"\" > ";
			 str+="<div class=\"Feebtn\"><input type=\"button\" class=\"posafterBtn posamove\" value=\"修改\" onClick=\"upeffice(this,"+dutiesData[j]["id"]+")\"> </div> ";
			 str+="</span></div> ";
			 $(".nums").append(str);
	     }
		
		 $(".tcdPageCodes").remove();
		 $(".width100felt").after("<div class='tcdPageCodes'></div>");	   
		 $(".tcdPageCodes").createPage({
			pageCount:countpage,
			current:page,
			backFn:function(p){
			
				station1(p); 
			}
		 });
		 
		 
		 $(".loding").hide();
     },
	 error:function(data){
		 
     }
	 
   });
}

function upeffice(ele,id){
   var oidVal=$(".office"+id).find(".score").val();
   var param=new Object();
   param["id"]=id;
   param["score"]=oidVal;

   $.ajax({
      type:"POST",
	  url:fee_editOfficeScore,
	  data:param,
	  dataType:"json",
	  success: function(data){
	      if(data.error==0){
			  Showbo.Msg.alert("修改成功");
		  }else{
		      Showbo.Msg.alert("未做任何修改");
		  }
	  },
	  error:function(data){}
   });
}





//获取部门信息
function getDepartment(){
   $.ajax({
	   type:"POST",
	   url:fee_getDepartment,
	   dataType:"json",
	   success: function(data){
		    for(var i in data){
				var row=data[i];
				$("<option value='"+row["id"]+"'>"+row["title"]+"</option>").appendTo($("#department"));
			}
	   },
	   error: function(){}
	   
	});
}


function allduties(page){
   $(".loding").show();
   $("#nums").html("");
   var param=new Object();
   param['type']=1;
   param['page']=page;	
	$.ajax({
	   type:"POST",
	   data:param,
	   dataType:"json",
	   url:fee_getAllDuties,
	   success: function(data){
		  $("#nums").html("");
		  for(var j in data.duties){
			 row=data.duties;
			 var css="";
			 if(j%2==0){
				 css+="odd";
			 }
			 var str="";
			 str+="<div class=\""+css+" contentspages office"+row[j]["id"]+"\" id=\"pageheader\" onmouseover=\"rowover(this)\"  onmouseout=\"rowout(this)\">";
			 str+="<span class=\"width-50\">"+row[j]["title"]+"</span> ";
			 str+="<span class=\"width-50\">";
			 str+="<input type=\"text\" class=\"score\" value=\""+row[j]["score"]+"\" > ";
			 str+="<div class=\"Feebtn\"><input type=\"button\" class=\"posafterBtn posamove\" value=\"修改\" onClick=\"upeffice(this,"+row[j]["id"]+")\"> </div> ";
			 str+="</span></div> ";
			 $("#nums").append(str);
		  }
		  
		  dutiesCount=data.dutiesCount; 
		  
		   $(".tcdPageCodes").remove();
		   $(".width100felt").after("<div class='tcdPageCodes'></div>");	   
		   $(".tcdPageCodes").createPage({
				pageCount:dutiesCount,
				current:page,
				backFn:function(p){
					allduties(p); 
				}
		   });
		   $(".loding").hide();
	   },
	   error:function(){}
	});  
}

function fee(ele,id,page){
	
	$(ele).addClass("hover").siblings().removeClass("hover");
	$(".loding").show();
	if(id==4 || id==5 || id==6){
		$("#edi"+id).show().siblings(".edit").hide();
	}else{
		$(".edit").hide();
	}
	
	
	if(id==0){//职务
	    $(".select").show();
	    $(".borderrim").show();
	    $(".menu1").show();
		$(".menu2").hide();
		$(".gl").hide();
		allduties();
		$("#add").hide();
	}else if(id==2){ //2 表示工龄
	    $(".select").hide();
		$("#nums").html("");
		$(".borderrim").hide();
		$(".gl").show();
		$(".tcdPageCodes").html("");
		
		$.ajax({
		   type:"POST",
		   dataType:"json",
		   url:fee_userseniorty,
		   success: function(data){
			   var is_sen=data[0]["seniorty"]; //开关
			   var seniorty_con=data[0]["seniorty_con"];
			
			   if(is_sen==0){
				  $("#isopen").attr("checked",false); 
				  $("#isclose").attr("checked",true);
			   }else{
			      $("#isopen").attr("checked",true);
				  $("#isclose").attr("checked",false);
			   }
			
			   $("#sco").val(seniorty_con);
			   $(".loding").hide();
		   },
		   error:function(data){}
		});
	}else{	
     	$(".borderrim").show();
	    $(".select").hide();
	    $("#add").show();
	    $(".menu1").hide();
		$(".menu2").show();
		$(".gl").hide();
		var param=new Object();
		param["eid"]=id;
		param["page"]=page;
		$("#nums").html("");
		$(".tcdPageCodes").html("");
		$.ajax({
		   type:"POST",
		   data:param,
		   dataType:"json",
		   url:fee_index,
		   success: function(data){
			   if(data["fee"]=="" && page>1){
			      fee(ele,id,page-1);
				  return false;
			   }
			   
			   var str="";
			   for(var i in data["fee"]){
				   var css="";
				   if(i%2==0){
					  css+="odd";
				   }
				   var row=data["fee"][i];
				   str+="<div class=\""+css+" contentspages cfee"+row["id"]+"\" id=\"pageheader\" onmouseover=\"rowover(this)\"  onmouseout=\"rowout(this)\">";
				   str+="<span class=\"width-33\"><input type=\"text\" value=\""+row["name"]+"\" class=\"feeName \"></span> ";
				   str+="<span class=\"width-33\"><input type=\"text\" value=\""+row["score"]+"\" class=\"feeScore\"> ";
				   str+="<div class=\"Feebtn\"><input type=\"button\" class=\"posafterBtn posamove\" value=\"修改\" onClick=\"feeEdit("+row["id"]+")\"></div></span>";
				   str+="<span class=\"width-33\"><input type=\"button\" class=\"costup\" value=\"删除\" onClick=\"feeDelete("+row["id"]+")\"></span>";
				   str+="</div>";
			   }
			  
			   $("#nums").append(str);
			   
			   //分页部分
			   $(".tcdPageCodes").remove();
			   $(".width100felt").after("<div class='tcdPageCodes'></div>");	   
			   $(".tcdPageCodes").createPage({
					pageCount:data["pageCount"],
					current:page,
					backFn:function(p){
						fee(ele,id,p); 
						$(".hover").attr("page",p);
					}
			   });
			   $(".loding").hide();
		   },
		   error:function(data){}
		});
	
	}
	
}


function rowover(ele){
  $(ele).find(".Feebtn").show();
}

function rowout(ele){
 $(ele).find(".Feebtn").hide();
}


//修改单条数据
function feeEdit(id){
    //获取修改的数据
	var element=$(".cfee"+id);
	var feeName=element.find(".feeName").val();
	var feeScore=element.find(".feeScore").val();
	
	if(feeName==""){
	   Showbo.Msg.alert("职位名称不能为空");
	   return false;
	}
	
	if(feeScore==""){
	   Showbo.Msg.alert("积分不能为空");
	   return false;
	}
	
	if(!(/^\d+$/.test(feeScore))){
		Showbo.Msg.alert("请添加有效数字");
		return false;
	}
	
	var param=new Object();
	param["id"]=id;
	param["name"]=feeName;
	param["score"]=feeScore;	
	$.ajax({
	   type:"POST",
	   url:fee_feeEdit,
	   dataType:"json",
	   data:param,
	   success:function(data){
	      if(data.error==0){
			  Showbo.Msg.alert("修改成功");
		  }else{
		      Showbo.Msg.alert("未做任何修改");
		  }
	   },
	   error:function(data){}
	  
	});
}

//删除单条数据
function feeDelete(id){
	var param=new Object();
	param["id"]=id;
	 Showbo.Msg.confirm('确认删除?',function(a){

	 		if(a == 'yes')
	 		{
			$.ajax({
		   type:"POST",
		   url:fee_feeDelete,
		   dataType:"json",
		   data:param,
		   success:function(data){		   
			   if(data.error==0){
				  Showbo.Msg.alert("删除成功");
				  
				  $(".btn").click(function(){
					  $(".cfee"+id).fadeOut(function(){
						  fee($(".hover"),$(".hover").attr("data-index"),$(".hover").attr("page"));
					  });
					  
				  });
				  
			   }else{
			      Showbo.Msg.alert("删除失败");
			   }
		   },
		   error:function(data){
		   }
		});

	 		}
	 })
	
}


function add(){
	$education=$('.feeAddName').val();
	$integral=$('.feeAddscore').val();
	
	
	if($education==""){
		Showbo.Msg.alert("添加名称不能为空");
		return false;
	}
	
	if($integral==""){
	    Showbo.Msg.alert("添加积分不能为空");
		return false;
	}
	
	if(!(/^\d+$/.test($integral))){
		Showbo.Msg.alert("请添加有效数字");
		return false;
	}
	
	$eid=$(".hover").attr("data-index");
	var param=new Object();
	param['name']=$education;
	param['score']=$integral;
	param['type']=$eid;
	
	$.ajax({
	   type:"POST",
	   url:fee_feeAdd,
	   dataType:"json",
	   data:param,
	   success:function(data){	
	
			if(data.error==0){
				  $('.feeAddName').val("");
				  $('.feeAddscore').val("");
				  Showbo.Msg.alert("添加成功");
				  
				  $(".btn").click(function(){
					  fee($(".hover"),$(".hover").attr("data-index"),1);
					  /*
					  var str="";
					  
					  str+="<div class=\"contentspages cfee"+data.id+"\" id=\"pageheader\" onmouseover=\"rowover(this)\" onmouseout=\"rowout(this)\">" +
					  	   "<span class=\"width-33\"><input type=\"text\" value=\""+$education+"\" class=\"feeName\"></span> " +
					  	   "<span class=\"width-33\"><input type=\"text\" value=\""+$integral+"\" class=\"feeScore\"> " +
					  	   "<div class=\"Feebtn\" style=\"display: none;\">" +
					  	   "<input type=\"button\" class=\"posafterBtn posamove\" value=\"修改\" onclick=\"feeEdit("+data.id+")\"></div></span>" +
					  	   "<span class=\"width-33\"><input type=\"button\" class=\"costup\" value=\"删除\" onclick=\"feeDelete("+data.id+")\">" +
					  	   "</span></div>";
					  
					  $("#nums").prepend(str);*/
					  
				  });
				  
			}else{
			   Showbo.Msg.alert(data.msg);
			}
	   },
	   error:function(){
	    
	   }
	});
}

function edit(){//修改
	var param=new Object();
	param['integral']=$("#sco").val();
	param['is_open']=$('input[name=seniority]:checked').val(); 	
	
	$.ajax({
	   type:"POST",
	   url:fee_editUserseniorty,
	   dataType:"json",
	   data:param,
	   success:function(data){
		   if(data.error==0){
		      Showbo.Msg.alert("修改成功");
		   }else{
			  Showbo.Msg.alert("修改失败");
		   }
	   },
	   error:function(data){}
	  
	});
}

	            
	            
	            
	        