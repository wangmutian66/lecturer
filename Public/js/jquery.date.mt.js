/**
 * 
 */
var data = {};
    var myDate = new Date();
    var m = myDate.getMonth()+1;
    var lnstr ="";
    var lyst ="";
    var lrstr ="";
    var nyr = myDate.getFullYear()+"-"+(myDate.getMonth()+1)+"-"+myDate.getDate();
    var tnst ="";
    var tystr ="";
    var trst ="";
    
    var y = new Date().getFullYear(); //当前年份

    $(document).ready(function($){

        for (var i = (y-2); i < (y+1); i++) {
            lnstr += '<option value="'+i+'">'+i+'年</option>';
            if(i==y){
                tnst+="<option selected='selected' value='"+i+"'>"+i+"年</option>";
            }else{  
                tnst+="<option  value='"+i+"'>"+i+"年</option>";
            }
        }
        $('#timeyear').append(tnst); //前两年年份
        $('#beforeyea').append(tnst); //今年年份

    /********************************年份赋值****************************************/
      
        for (var i = 1; i < 13; i++)  
        {  
            
            
            if(i==m){
                tystr+="<option selected='selected' value='"+i+"'>"+i+"月</option>";
                lyst += "<option  selected='selected' value='" + i + "'> " + i + "月</option>"; 
            }else{
                tystr+="<option  value='"+i+"'>"+i+"月</option>";
                lyst += "<option value='" + i + "'> " + i + "月</option>"; 
            } 
        }  
        $('#timemonth').append(lyst); //前两年月份
        $('#datayear').append(tystr); //今年月份

    /********************************月份赋值****************************************/
        var days=getDays(myDate.getFullYear(),m);
        for (var i = 1; i <= days; i++)  
        {  
            lrstr += "<option value='" + i + "'> " + i + "日</option>"; 
            if(i==myDate.getDate()){
                trst+="<option selected='selected' value='"+i+"'>"+i+"日</option>";
            }else{
                trst+="<option  value='"+i+"'>"+i+"日</option>";
            } 
        }  
        $('#timeday').append(lrstr); //前两年月份
        $('#day').append(trst); //今年月份

    /********************************天数赋值****************************************/

        var timeyear = $('#timeyear').val();
        var timemonth = $('#timemonth').val();
        var timeday = $('#timeday').val();
        var beforeyea = $('#beforeyea').val();
        var datayear = $('#datayear').val();
        var day = $('#day').val();
       

    });
    
    
    function  getDays(year,mouth){

        //定义当月的天数；
        var days ;
         
        //当月份为二月时，根据闰年还是非闰年判断天数
        if(mouth == 2){
            days= year % 4 == 0 ? 29 : 28;
             
        }
        else if(mouth == 1 || mouth == 3 || mouth == 5 || mouth == 7 || mouth == 8 || mouth == 10 || mouth == 12){
            //月份为：1,3,5,7,8,10,12 时，为大月.则天数为31；
            days= 31;
        }
        else{
            //其他月份，天数为：30.
            days= 30;   
        }
        return days;
    }	
    function checkChangDate(obj,act){
        var myDate = new Date();
        var year = myDate.getFullYear();
        var lrstrs = '';
        var trsts = '';
        var timeyear = $('#timeyear').val();//左侧年

        var timemonth = $("#timemonth").val();//左侧月 

        var timeday = $("#timeday").val();//左侧日

        var beforeyea = $("#beforeyea").val();//右侧年

        var datayear = $('#datayear').val();//右侧月

        var day = $('#day').val();//右侧日

        timeyear = parseInt(timeyear);
        beforeyea = parseInt(beforeyea);
        if(timeyear > beforeyea){
            $("#beforeyea").val(timeyear);
        }

        timemonth = parseInt(timemonth);
        datayear = parseInt(datayear);
        if(timeyear == beforeyea){
            if(timemonth > datayear){
                $("#datayear").val(m);
            }
            if(timeyear == year){
                if(timemonth >= m){
                    $("#timemonth").val(m);
                }
            }
            if(beforeyea == year){
                if(datayear >= m){
                    $("#datayear").val(m);
                }
            }
        }

        timemonth = $("#timemonth").val();
        timemonth = parseInt(timemonth);

        datayear = $("#datayear").val();
        datayear = parseInt(datayear);

        timeday = parseInt(timeday);
        day = parseInt(day);
        if(timeyear == beforeyea && timemonth == datayear){
            if(timeday > day){
                $("#day").val(timeday+1);
            }
            if(timeyear == year && timemonth == m){
                if(timeday >= myDate.getDate()){
                    $("#timeday").val(myDate.getDate());
                }      
            }
            if(beforeyea == year && datayear == m){
                if(day >= myDate.getDate()){
                    $("#day").val(myDate.getDate());
                }      
            }
        }

        if(act == 1){
            var mm = getDays(timeyear,timemonth);    //当左侧月改变时执行
        }else if(act == 2){
            var mm = getDays(beforeyea,datayear);    //当右侧月改变时执行
        }

        for (var i = 1 ; i <= mm ; i++)  
        {  
            if(act == 1){   
                lrstrs += "<option value='" + i + "'> " + i + "日</option>"; 
            }else if(act == 2){
                if(i==myDate.getDate()){
                    trsts+="<option selected='selected' value='"+i+"'>"+i+"日</option>";
                }else{
                    trsts+="<option  value='"+i+"'>"+i+"日</option>";
                } 
            }
        } 
        
        if(act == 1){
            $('#timeday').html(lrstrs); //左侧变化的天数
        }else if(act == 2){
            $('#day').empty();
            $('#day').append(trsts); //右侧变化的天数
        }
        
    }
    
    