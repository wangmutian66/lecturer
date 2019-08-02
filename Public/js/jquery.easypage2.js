;(
	function($){
		$.extend({
				"easypage":function(options){
					options = $.extend({//��������
						contentclass:"contentlist",//Ҫ��ʾ�����ݵ�class
						navigateid:"navigatediv",//������ť���ڵ�dom��id
						everycount:"10",//ÿҳ��ʾ���ٸ�
						navigatecount:"5"//������ťһ����ʾ���ٸ�
						},
						options);
					var currentpage = 0;//��ǰҳ	
					var contentspages = $("."+options.contentclass);//Ҫ��ʾ������
					var contentcount = contentspages.length;//�õ����ݵĸ���
					var pagecount = Math.ceil(contentcount/options.everycount);//�����ҳ��
					//ƴ�ӵ�����ť  //<div id='pagefirst' class='pagefirst'><a href='javascript:void(0)'>First</a></div>
					var navigatehtml = "<div id='pagepre' class='pagepre'><a href='javascript:void(0)'> < </a></div>";
					for(var i = 1;i <= pagecount;i++){
					navigatehtml+='<div class="pagenavigate"><a href="javascript:void(0)">'+i+'</a></div>';
					}//<div id='pagelast' class='pagelast'><a href='javascript:void(0)'>List</a></div>
					navigatehtml+="<div id='pagenext' class='pagenext'><a href='javascript:void(0)'> > </a></div>";
					
					//���ص�����ť
					$("#"+options.navigateid).html(navigatehtml);	
					
					//�õ����а�ť
					var navigates = $(".pagenavigate");
					
					//�������еĵ�����ť
					$.extend({
						"hidenavigates":function(){
							navigates.each(function(){
								$(this).hide();
							})	
						}	
					});
					
					//��ʾ������ť
					$.extend({
						"shownavigate":function(currentnavigate){
							$.hidenavigates();
							var begin = currentnavigate>=options.navigatecount?currentnavigate-parseInt(options.navigatecount):0;
							if(begin>navigates.length-2*options.navigatecount){
								begin = navigates.length-2*options.navigatecount;	
							}
							for(var i = begin;i < currentnavigate+parseInt(options.navigatecount);i++){
								$(navigates[i]).show();
							}
						}	
					});
					
					//������ʾĳ����ť
					$.extend({
						"lightnavigate":function(currentnavigate){
							currentnavigate.addClass("pagenavigateon");	
						}	
					});
					
					//�Ƴ����и�����ť
					$.extend({
						"removelight":function(){
							$(".pagenavigateon").each(
								function(){
									$(this).removeClass("pagenavigateon");	
								}
							)
						 }	
					});
					
					//��ʾĳҳ������
					$.extend({
						"showPage":function(page){
							contentspages.each(
								function(contentindex){
									if(contentindex>=page*options.everycount && contentindex < (page+1)*options.everycount){
									$(this).show();	
									}else{
									$(this).hide();	
									}
								}
							);
						}
					});
					
					//����ǰ�����˰�ť
					$.extend({
						"hidePreNext":function(page){
								if(page==pagecount-1){
									$("#pagenext").hide();	
									$("#pagelast").hide();
									$("#pagepre").show();
									$("#pagefirst").show();			
								}else if(page==0){
									$("#pagepre").hide();
									$("#pagefirst").hide();	
									$("#pagenext").show();	
									$("#pagelast").show();	
								}else{
									$("#pagenext").show();
									$("#pagepre").show();	
									$("#pagefirst").show();	
									$("#pagelast").show();	
								}
						}	
					});
					//��ʾָ���ĵ�����ť
					$.shownavigate(0);
					//�������е�����
					$.showPage(0);
					//��ʼʱ���غ��˰�ť
					$.hidePreNext(0);
					//������ʾ��һ����ť
					if(pagecount>0){
							$.lightnavigate($(navigates.get(0)));
					}
					//���������ť
					$(".pagenavigate").each(
						function(myindex){
							$(this).click(
								function(){
									$.showPage(myindex);
									$.removelight();
									$.lightnavigate($(this));
									currentpage = myindex;
									$.hidePreNext(currentpage);
									var na = Math.floor((currentpage+1)/options.navigatecount)*options.navigatecount;
									$.shownavigate(na);	
								}
							);
						}
					);
					//������˰�ť
					$("#pagepre").click(
						function(){
							--currentpage<=0 && (currentpage=0);
							$.showPage(currentpage);	
							$.removelight();
							$.lightnavigate($(navigates.get(currentpage)));
							$.hidePreNext(currentpage);
							var na = Math.floor(currentpage/options.navigatecount)*options.navigatecount;
							$.shownavigate(na);	
						}
					);
					//���ǰ����ť
					$("#pagenext").click(
						function(){
							++currentpage>=pagecount-1 && (currentpage=pagecount-1);
							$.showPage(currentpage);	
							$.removelight();
							$.lightnavigate($(navigates.get(currentpage)));
							$.hidePreNext(currentpage);
							
							var na = Math.floor((currentpage+1)/options.navigatecount)*options.navigatecount;
							$.shownavigate(na);	
						}
					);
					//�����ҳ��ť
					$("#pagefirst").click(
						function(){
							currentpage=0;
							$.showPage(currentpage);	
							$.removelight();
							$.lightnavigate($(navigates.get(currentpage)));
							$.hidePreNext(currentpage);
							
							var na = Math.floor((currentpage+1)/options.navigatecount)*options.navigatecount;
							$.shownavigate(na);	
						}
					);
				 //���βҳ��ť
				 $("#pagelast").click(
				 		function(){
				 			currentpage=pagecount-1;
				 			$.showPage(currentpage);	
							$.removelight();
							$.lightnavigate($(navigates.get(currentpage)));
							$.hidePreNext(currentpage);
							
							var na = Math.floor((currentpage+1)/options.navigatecount)*options.navigatecount;
							$.shownavigate(na);	
				 		}
				 );
				}
		});
	}
)(jQuery)