function StartLiDrag(){

	// img = $("#list li"),

	$("#lis li,#list li").draggable({
		helper:"clone",
		//revert: "invalid"
	});
	$("#container1").droppable({
		drop:function(event,ui){

			var oid=ui.draggable.context.id;
			var preg = /^ll\d+/;
			if(preg.test(oid)){
				return;
			}

			// alert(re);
			var i = document.getElementById("l"+oid);

			if(i != null)
			{
				alert("请勿重复添加");
				return;
			}

			$("#lis").append("<li id = l"+ui.draggable.context.id+">"+ui.draggable.context.innerHTML+"</li>");
			StartLiDrag();
		}
	});
	$("#container2").droppable({
		drop:function(event,ui){
			var oid=ui.draggable.context.id;
			// alert(re);
			/**
				执行删除操作
			**/
			var preg = /^ll\d+/;
			if(preg.test(oid)){
				$("#"+oid).remove();
			}

			//   var i = document.getElementById(re);
			// // if(i!= null)
			// // {
			// // 	alert("请勿重复添加");
			// // 	return;
			// // }
			// $("#list1").append("<li id = l"+ui.draggable.context.id+">"+ui.draggable.context.innerHTML+"</li>");
		}
	});
	
};

