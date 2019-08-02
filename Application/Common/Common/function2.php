<?php
/***
 * 
 * [根据权限和登录用户获取当前分公司id]
 * @uid array("in",implode(",", $companyId));
 */

function getcompanyId(){
    $id=session("id");
    $type=session("type");
    $yid=session("yid");
    $admin=M('admin');
    $enterprise=M('enterprise');
    $user=M('user'); 
    $salesman=M('salesman');

    if($yid=="admin"){
        return array("neq","0");
    }
    $adminType=array("10","11","12","13");

    if(in_array($type,$adminType)){ //财务总监 ceo 管理账号 企业总账号

        $id=$admin->where(array("pid"=>$id,"type"=>3))->getField("id",true);
     
        if(empty($id)){
            return null;
        }
        $companyId=$enterprise->where(array("aid"=>array('in',implode(",", $id))))->getField("id",true);

    }else if($type==9){ // 大区总监
        $companyId=$enterprise->where(array("aid"=>$id))->getField("id",true);
    }else if($type<9&&$type>=5){ //分公司内部
        $companyId=$user->where(array("id"=>$id))->getField("uid",true);
    }else{ //公司销售人员
        $companyId=$salesman->where(array("id"=>$id))->getField("uid",true);
    }
    if(empty($companyId)){
        return null;
    }else{
        return array("in",implode(",", $companyId));
    }
    
}

/**
 * [获取当前角色是否有未读到的信息]
 * @return 未读信息的数量
 */

function isreadmessage(){

    $type=session("type");
    $uid=getcompanyId();
    $id=session("id");
    $yid=session('yid'); //id
    $messagecount=M('message')->where(array("uid"=>$uid))->count();
    $adminType=array("9","10","11","12","13");
    if(in_array($type,$adminType)){
        if($yid!=1&&isset($yid)){
            $id=$yid;
        }

        $message=M('message')->where(array("uid"=>$uid))->where("aid like '%,".$id.",%' or aid like '".$id.",%' or aid like '%,".$id."' or aid=".$id." ")->count();
     
    }else{
        $message=M('message')->where(array("uid"=>$uid))->where("eid like '%,".$id.",%' or eid like '".$id.",%' or eid like '%,".$id."' or eid=".$id." ")->count();
    }
    return $messagecount-$message;
}

/**
 * [获取当前登录者的信息]
 * @return 数据库表名|名字|数据库中存放的角色(数字)|id
 */

function getUserMessge(){
    $id=session("id");
    $type=session("type");
    $admin=M('admin');
    //$enterprise=M('enterprise');
    $user=M('user');
    $salesman=M('salesman');
    $yid=session("yid");
    $adminType=array("9","10","11","12","13");

    if(in_array($type,$adminType)){ //ceo
     
        if($yid!=1&&($type==10||$type==11||$type==12)){ //财务总监
            $id=$admin->where(array("id"=>$yid))->find();

            $moveperson="admin|".$id["mname"]."|".$id["type"]."|".$yid;
        }else{
            $idcontent=$admin->where(array("id"=>$id))->find();
            $moveperson="admin|".$idcontent["mname"]."|".$idcontent["type"]."|".$id;
        }

    }else if($type<9&&$type>=5){ //分公司内部
        $idcontent=$user->where(array("id"=>$id))->find();
        $moveperson="user|".$idcontent["name"]."|".$idcontent["role"]."|".$id;
    }else{ //公司销售人员
        $idcontent=$salesman->where(array("id"=>$id))->find();
        $moveperson="salesman|".$idcontent["name"]."|".$idcontent["role"]."|".$id;
    }

    return $moveperson;
}



/**获取企业id**/
function getenterpriseid(){
    $id=session("id");
    $type=session("type");
    $admin=M('admin');

    $user=M('user');
    $salesman=M('salesman');
    $yid=session("yid");
    $adminType=array("9","10","11","12","13");

    if(in_array($type,$adminType)){ //ceo

        if($type==9){ //财务总监
            $idcontent=$admin->where(array("id"=>$id))->getField("pid");
            return $idcontent;
        }else{
           return $id;
        }

    }else {
        $aid=M('enterprise')->where(array("id"=>getcompanyId()))->getField("aid",true);

        $idcontent=$admin->where(array("id"=>array("in",implode(",",$aid))))->getField("pid");

        return $idcontent;
    }


}