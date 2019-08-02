function createUrl(path,option){
	//username-id
	var user = GetQueryString("user");
	str = '';
	
	delete option['errcode'];
	delete option['errmsg'];
	if(user !=null && user.toString().length>1){
		user = parseInt("({" + user + "})");
		if(option){
			for(i in option){
				if(user[i]){
					delete user[i];
				}
			}
		}

		for(i in user){
			str += '"'+i + '":"' + user[i] + '",';
		}
	}

	if(option){
		for(i in option){
			str += '"'+i + '":"' + option[i] + '",';
		}
	}

	if(str != ''){
		str = encodeURI(str.substring(0,str.length-1));
		return path += "?user="+str;
	}else{
		return path;
	}
}
function GetQueryString(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  r[2]; return null;
}
function locationUrl(path,option,type){
	var username = option['companycode']+option['qimobile'];
	var password = option['qimobile'];
	if(option){
		for(i in option){
	        window.localStorage[i] = option[i]; //本地存值
		}
	}

	// if(type == '')
	// {
	// 	loginnn(username,password,url);
	// }else
	// {
		window.location.href = path;
	// }	
}

function getUser(){
	var storage = window.localStorage;
	return storage;
}
function loginnn(username,password,url) {
    var username = username;
    var password = password;

    window.JMessage.login(username, password,
        function (response) {
            window.JMessage.username = username;
            window.location.href = url;
        }, function(){
            registerrr(username,password,url)
        });
}
function registerrr(username,password) {
    var username = username;
    var password = password;

    window.JMessage.register(username, password,
        function (response) {
        	getPushRegistrationID();
            loginnn(username,password,url);
        }, function (response) {
            console.log("register failed");
        }
    );
}

function onGetRegistrationID(response) {
	var user = getUser();
        var str = 'rest/{"response":"'+response+'","mobile":"'+user.companycode+'","eid":"0","uid":"'+user.Qimobile+'"}';
        $.ajax({
            url:path_url+'index.php/Task/app/push/'+str,       //跨域到http://www.wp.com，另，http://test.com也算跨域
            type:'GET',             //jsonp 类型下只能使用GET,不能用POST,这里不写默认为GET
            dataType:'jsonp',                          //指定为jsonp类型
            jsonp:'callback',                          //服务器端获取回调函数名的key，对应后台有$_GET['callback']='getName';callback是默认值
            jsonpCallback:'getName223344',                   //回调函数名
            async:false,
            success:function(result){
                
            }
        }); 
}

function getPushRegistrationID() {
        window.JPush.getRegistrationID(onGetRegistrationID);
}






