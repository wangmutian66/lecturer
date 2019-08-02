<?php
namespace Home\Controller;
use Think\Controller;

class RelationshipController extends BaseController 
{
    private $_relaname=array(
		""=>"全部客户",
		"deal"=>"成交客户",
		"follow"=>"跟踪客户",
		"public"=>"客户公海池",
		"delete"=>"客户回收站"
	);

	public function index()
	{
	    $sales=C('SALES_POWER_SESSION');
		$type=session("type");
	    $list=I('get.list','');
		$list=($list=="")?null:$list;
		session("menu",$list);
        $move=true;
		$relaname=$this->_relaname;
	    $uid=$id=0;
	    $salesmancompany=null;

        $finatype=session("finatype");

        if($type!=9||$type!=10){
			$id=getcompanyId();
			$comid=$id[1];
		}
	    if($type==$sales){
			$move=false;
            $id=session("id");
	        $salesman=M('salesman')->where(array("id"=>$id))->select();
	        $uid=$salesman[0]["uid"];
	        //$this->getTeamsubor($id,$uid);
	        $salesmancompany=M('salesman')->where(array("uid"=>$uid))->select();
	       
            $team=M('team')->where(array("uid"=>$uid,"sid"=>$id))->getField("id",true);
			$teamuid=M('team')->where(array("uid"=>$uid))->getField("id",true);
            if(!empty($team)){
				$move=true;
			}
			if(!empty($teamuid)){
				$teammember=M('team_member')->where(array("eid"=>$id,"iszg"=>1,"tid"=>array("in",implode(",",$teamuid))))->find();

				if(!empty($teammember)){
					$move=true;
				}
			}
	    }

        //检查如果成交信息不存在 就让其自动回到全部客户
		$info=M('information')->where(array("uid"=>getcompanyId(),"status"=>1))->select();
        foreach ($info as $row){
			$course_com=M('course_com')->where(array("fid"=>$row["id"]))->find();
            if(empty($course_com)){
				M('information')->where(array("id"=>$row["id"]))->save(array("status"=>0));
			}
		}
	    $this->backpublic();
	    $enterprise=M('enterprise')->where(array("id"=>getcompanyId()))->select();
	    if(count(explode(",",$comid))>1){
			$comid=0;
		}
		$lectusers = M("user")->where(array("uid"=>getcompanyId(),'lectype'=>'1'))->select();
	
	    $this->assign("id",$id);
		$this->assign("finatype",$finatype);
		$this->assign("comid",$comid);
		$this->assign("relaname",$relaname[$list]);
	    $this->assign("uid",$uid);
		$this->assign('list',$list);
		$this->assign('move',$move);
		$this->assign("salesman",$salesman);
		$this->assign("salesmancom",$salesmancompany);
		$this->assign("enterprise",$enterprise);
		$this->display();
	}





	//连续只购买两次小票 和 半年九大门规用户 没有其他交易  被送回公海池
	public function backpublic(){
		$information= M('information');
		$info=$information->where(array("uid"=>getcompanyId()))->where("ctime < DATE_SUB(CURDATE(), INTERVAL 0.5 YEAR)")->select();
        foreach ($info as $row){
			if($row["rid"]!=0) {
				$array = array();
				$array["yrid"] = $row["rid"];
				$array["nrid"] = 0;
				$array["fid"] = $row["id"];
				$array["ctime"] = date("Y-m-d H:i:s");
				$array["status"] = 3;
				$array["reason"] = "购买九大规划客户连续半年未购买其他成交记录";
				$array["moveperson"]=getUserMessge();
				M('transfer_log')->add($array);
			}
		}

//		$info=$information->where(array("uid"=>getcompanyId()))->where("surplus>=2")->select();
//		foreach ($info as $row){
//			if($row["rid"]!=0){
//				$array=array();
//				$array["yrid"]=$row["rid"];
//				$array["nrid"]=0;
//				$array["fid"]=$row["id"];
//				$array["ctime"]=date("Y-m-d H:i:s");
//				$array["status"]=3;
//				$array["reason"]="连续两次只购买小票客户";
//				$array["moveperson"]=getUserMessge();
//				M('transfer_log')->add($array);
//			}
//
//		}
        $uid=getcompanyId();
//		$information->query("update ".C('DB_PREFIX')."information  set rid=0 where uid in('".$uid[1]."') and ( ctime < DATE_SUB(CURDATE(), INTERVAL 0.5 YEAR) or surplus>=2)");
		$information->query("update ".C('DB_PREFIX')."information  set rid=0 where uid in('".$uid[1]."') and ( ctime < DATE_SUB(CURDATE(), INTERVAL 0.5 YEAR))");


	}


	//获取团队下属成员
	public function getTeamsubor($sid,$uid){
	    //上级领导
	    $teampeople=M('team')->where(array("sid"=>$sid,"uid"=>$uid))->getField("id",true);
		if(!empty($teampeople)){
			$teammember=M('team_member')->where(array("tid"=>array("in",implode(",",$teampeople))))->getField("eid",true);
		}

	    //团队主管
	    $teamMemberTids=M('team_member')->where(array("eid"=>$sid,"iszg"=>1))->getField("tid",true);
		if(!empty($teamMemberTids)){
			$teammembers=M('team_member')->where(array("iszg"=>0,"tid"=>array("in",implode(",",$teamMemberTids))))->getField("eid",true);
		}

	    $teammember=(empty($teammember))?array():$teammember;
	    $teammembers=(empty($teammembers))?array():$teammembers;
	   
	    $teammember=array_merge($teammember,$teammembers);
 	    
 	    return $teammember;
	}
	
	public function moveclient(){
	    $company=I('post.company');
	    $name=I('post.name','');
		$tid=I('post.tid','');
		$sales=C('SALES_POWER_SESSION');
		$type=session("type");
	    $arr=array();
	    $arr["uid"]=$company;
//        $teams["uid"]=$company;
//		if($tid!=""){
//			$teams["tid"]=$tid;
//		}
//		$team=M('team')->where($teams)->select();
//        if($sales==$type){
//
//			$tid=array();
//			$sid=array();
//			if(!empty($team)){
//				foreach ($team as $row){
//					$tid[]=$row["id"];
//					$sid[]=$row["sid"];
//				}
//				$eids=M('team_member')->where(array("tid"=>array("in",implode(",",$tid))))->getField("eid",true);
//				$said=implode(",",$sid);
//				if(!empty($eids)){
//					$said.=",".implode(",",$eids);
//				}
//				$arr["id"]=array("in",$said);
//			}
//
//			//获得该销售人员所管辖的团队
//			$id=session("id");
//			$team_eid=M('team_member')->where(array('eid'=>$id,'iszg'=>1))->getField("tid",true);
//
//			//$team=M('team')->where(array(array("sid"=>$id,"uid"=>$company),array("id"=>array("in",implode(",",$team_eid)))))->select();
//			$team=M('team')->where("(sid={$id}  AND uid={$company}) or id in (".implode(",",$team_eid).") ")->select();
//		}
	    if($name!=''){
	        $arr["name"]=array("like","%$name%");
	    }

	    $salesman=M('salesman')->where($arr)->select();
	 
	    $array['salesman']=$salesman;
//		$array['team']=$team;
	    echo json_encode($array);
	}

	public function teamchange(){
		$id=I('post.id');

		$team_sid=M('team')->where(array("id"=>$id))->getField('sid');
		$team_eid=M('team_member')->where(array("tid"=>$id))->getField('eid',true);
		if(!empty($team_eid)){
			$team_sid.=",".implode(",", $team_eid);
		}
		$salesman=M('salesman')->where(array("id"=>array("in",$team_sid)))->field("id,name")->select();
		echo json_encode($salesman);
	}

	public function savemoveclient(){
	    $tid=I('post.tid','');//客户ID
	    $rid=I('post.rid','');//现负责人id
	    $otherval=I('post.otherval');//状态选择值
	    $reason=I('post.reason');//备注
	    $tid = substr($tid,0,strlen($tid)-1);
	    $fid = array('in',$tid);

	    $trans_log = M('information')->where(array('id'=>$fid))->select();
	    foreach($trans_log as $k =>$v){

	    	$arr=array();
	    	$arr['yrid'] = $v['rid'];//原负责人id
	    	$arr['fid'] = $v['id'];
            $arr['nrid'] = $rid;
            $arr['ctime'] = date('Y-m-d H:i:s');
            $arr['status'] = $otherval;
            $arr['reason'] = $reason;
			$arr["moveperson"]=getUserMessge();
            M('transfer_log')->add($arr);

	    }
      
	    $salesman=M('information')->where(array('id'=>array("in",$tid)))->save(array("rid"=>$rid,"surplus"=>0,"countdown"=>"0000-00-00"));

	    if($salesman){
	        $result["error"]=0;
	        $result["msg"]="移交成功";
	    }else{
	        $result["error"]=0;
	        $result["msg"]="移交失败";
	    }
	    echo json_encode($result);
	}
	
	public function selectcom(){
	    $uid=I('post.uid');
	    $salesman=M('salesman')->where(array("uid"=>$uid))->field("id,name")->select();
	    echo json_encode($salesman);
	}	 
	
	public function actindex()
	{	
		// 		$id = session('uid');
		$sele1 = I('post.sele1');
		$sele2 = I('post.sele2');
		$sele3 = I('post.sele3');
		$sele4 = I('post.sele4');
		$sele5 = I('post.sele5');

		if($sele1  != "")
		{
			$ini['star'] = $sele1;
		}
		
		if($sele2  != "")
		{
			 
			$ini['industry'] = $sele2;
		}
		if($sele3  != "")
		{
			$ini['level'] = $sele3;
		}
		if($sele4  != "")
		{
			$ini['nature'] = $sele4;
		}
		if($sele5  != "")
		{
			$ini['state'] = $sele5;
		}
		
		
		
		
		
		
		$acr = I('post.ipadc');
		$kt = I('post.kt');
		$jt = I('post.jt');
		$s1  = I('post.s1');
		$s2 = I('post.s2');
	
		$yu = I('post.yu');
		$tesarr = I('post.tesarr');
		$tes = I('post.tes');
		$list=I('post.list','');
		$uid=I('post.uid','');
		$client=I('post.client','');

		//$information= M('information');
		//$information->query("update ".C('DB_PREFIX')."information  set rid=0  WHERE (countdown < DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND  countdown <> '0000-00-00') OR surplus>2  ");

		if($yu == 0 || empty($tesarr) || empty($tes))
		{
			
			if($tesarr == 1)
			{
				$ini['contact'] = array('like',"%$tes%");
			
			}else if($tesarr == 2)
			{
			
				$ini['name'] = array('like',"%$tes%");
			}else if($tesarr == 3)
			{
				
				$sal = M('salesman')->where(array('name'=>array('like',"%$tes%")))->select();
			
				
					
				foreach ($sal as $ke1=>$ve1)
				{
					$nontype.=$ve1['id'].',';
				}
				if($nontype != "")
				{
					
				
				$nontype = substr($nontype,0,-1);
				$ini['rid'] = array(array('in',$nontype),array('neq',0));
				}else
				{
					$ini['rid'] = array('eq',-1);
				}
			}elseif ($tesarr == 4)
			{
				$ini['tel'] = array('like',"%$tes%");
			}
			
			

		
    		if($acr == 0)
    		{	
    			if($s1 == 1 || $s1 == 0){
    				   
    			$ini['ctime'] = array(array('elt',date('Y-m-d')),array('egt',$kt));
    			}else if($s1 == 2)
    			{
    			$ini['ztime'] = array(array('elt',date('Y-m-d')),array('egt',$kt));
    			}
    		}else
    		{	
    			if($s1 == 1 || $s1 == 0){
    				$ini['ctime'] = array(array('elt',$jt),array('egt',$kt));
    			}else if($s1 == 2)
    			{
    				$ini['ztime'] = array(array('elt',$jt),array('egt',$kt));
    			}
    			
    		}
    		
    		if(!empty($s2))
    		{
    			if($s2 == 1)//已联系
    			{
    			    $ini['ztime']=array('neq','0000-00-00');
    				//$ini['ztime'] = array(array('elt',$jt),array('egt',$kt));
    			}else if($s2 == 2)//未联系
    			{	
    			    $ini['ztime']=array('eq','0000-00-00');
    				//$ini['ztime'] = array(array('gt',$jt),array('lt',$kt),'or');
    			}
    			
    		}
    		
    		
		}else
		{	
		
			if($tesarr == 1)
			{
				$ini['contact'] = array('like',"%$tes%");
				
			}else if($tesarr == 2)
			{	
				
				$ini['name'] = array('like',"%$tes%");
			}else if($tesarr == 3)
			{	
				
				$sal = M('salesman')->where(array('name'=>array('like',"%$tes%")))->select();
				foreach ($sal as $ke1=>$ve1)
				{
					$nontype .=$ve1['id'].','; 
				}
				$nontype = substr($nontype,0,-1);
				$ini['rid'] = array(array('in',$nontype),array('neq',0));
				
			}elseif ($tesarr == 4)
			{
				$ini['tel'] = array('like',"%$tes%");
			}
			
		}


		if($tesarr != 3 && $list!="delete")
		{
			$ini['rid'] = array('neq',0);
		}
		
		
		$sales=C('SALES_POWER_SESSION');
		$p = I('post.p');
   		$limit = 10;
	    if(!$p)
	    {
	      $p = 1;
	    }
   		 $first = ($p-1) * $limit;
         $ini['deletemark'] = 0;//未删除
         if($uid==""){
             $ini["uid"]=getcompanyId();
         }else{
             $ini["uid"]=$uid;
         }         
         $id=session("id");
         if(session("type")==$sales){
             $getsubor=$this->getTeamsubor($id,$uid);
             $getsubors=array_merge($getsubor,array($id));
             switch ($client){
                 case 0:
                     $ini["rid"]=array("in",implode(",", $getsubors));// 全部客户
                     break;
                 case 1:
                     $ini["rid"]=session("id");
                     break;
                 case 2:
                     $ini["rid"]=array("in",implode(",", $getsubor));// 下属客户
                     break;
             }
         }
         
         
         switch ($list){
             case 'deal':
                 $ini['status']=1;
                 break;
             case 'follow':
                 $ini['ztime']=array('neq','0000-00-00');
                 break;
             case 'public':
                 $ini['rid']=0;
                 break;
             case 'delete':
				 unset($ini['rid']);
                 $ini['deletemark']=1;
                 break;
         }

   
         $information = M('information')->where($ini)->limit($first,$limit)->order("id desc")->select();
       

         $count = M('information')->where($ini)->count();
         $act['count'] = ceil($count/$limit);
         
    	 foreach ($information as $k=>$v)
    	 {
			 $information[$k]['uname']=M('enterprise')->where(array("id"=>$v["uid"]))->getField("Qyname");
    		 $information[$k]['rwname'] = M('salesman')->where(array('id'=>$v['rid']))->getField("name");//find()['name']
    		//$information[$k]['ztime1']=strtotime($v['ztime']);
			 $information[$k]['ztime1']=(($v['ztime']=="0000-00-00")?false:$v['ztime']);
             //countdown
			 if($v["countdown"]=="0000-00-00"){
				 $information[$k]["jdsyday"]="无剩余天数";
			 }else{
				 $information[$k]["jdsyday"]=$this->time2string(strtotime('+6 month',strtotime($v["countdown"])));

			 }

	
			 if($ini['status']==1){
				$information[$k]['dealist']=$this->getdeallist($v['id']);
			 }else{
				$information[$k]['dealist']=array();
			 }

    	 }
    	 $act['data'] = $information;
    	
    	 
    	 echo json_encode($act);
	}

    // echo time2string(strtotime('+6 month',strtotime("2017-03-18")));
	function time2string($second){
		$second=$second-time();
		if($second<0){
			return '0天'; //0小时0分0秒
		}
		$day = floor($second/(3600*24)); //floor
		$second = $second%(3600*24);//除去整天之后剩余的时间
		$hour = floor($second/3600);
		$second = $second%3600;//除去整小时之后剩余的时间
		$minute = floor($second/60);
		$second = $second%60;//除去整分钟之后剩余的时间
		//返回字符串
		return $day.'天'.$hour.'小时'.$minute.'分'.$second.'秒';
	}


	public function getdeallist($fid){
        $corseCom=M('course_com')->where(array("fid"=>$fid))->select();

		$result=array();
		$i=0;
        foreach($corseCom as $k=>$row){
            //课程
            $course=M('course')->where(array("id"=>$row["cid"]))->find();
			//成交信息
			$order=M('course_com_order')->where(array("comid"=>$row["id"]))->select();
			foreach($order as $k=>$val){
				$order[$k]["ridname"]=M('salesman')->where(array("id"=>$val["rid"]))->getField("name");
			}
			if(!empty($order)){
				$result[$i]["name"]=$course["classname"];
				$result[$i]["comdorder"]=$order;

				$i++;
			}

		}
        return $result;
	}

	public function rsase()
	{

		
		//$ini['rid'] = I('post.rid');

		$ini['name'] =I('post.name');
		$ps =I('post.ps');//01:北京
//		$qwe = substr($ps,3);
	    $cs =I('post.cs');
//	    $asd = substr($cs,5);
		$ini['province'] =$ps;
	    $ini['city'] =$cs;
		$ini['uid'] =I('post.uid');
		$ini['type'] = I('post.genre');
		$ini['state'] = I('post.type');
		$ini['nature'] = I('post.isIndividual');
		$ini['level'] = I('post.scale');
		$ini['industry'] = I('post.industry');
		$ini['star'] = I('post.xing');
		$ini['source'] = I('post.source');
		$ini['tel'] = I('post.num');
		$ini['address'] = I('post.zun');
		$ini['ctime'] = date('Y-m-d');
		$ini['contact'] = I('post.razecont');
		$ini['birthday'] = I('post.shri');
		$ini['deletemark'] = 0 ;
		$ini['profitid'] = I('post.lid');
		$tid  = I('post.tid');
		$que = M('information')->where(array('deletemark'=>0,'name'=>I('post.name'),'id'=>array("neq",$tid),"uid"=>getcompanyId()))->find();
		if(!empty($que))
		{
			echo json_encode(3);
			exit();
		}
		if(!empty($tid))
		{
			$information = M('information')->where(array('id'=>$tid))->save($ini);
            
		}else
		{
			$information = M('information')->add($ini);

			$fid=M('information')->getLastInsID();
			$array=array();
			$array["yrid"]=0;
			$array["nrid"]=0;
			$array["fid"]=$fid;
			$array["ctime"]=date("Y-m-d H:i:s");
			$array["status"]=5;
			$array["moveperson"]=getUserMessge();
			M('transfer_log')->add($array);
		}



		if($information)
		{
			echo json_encode(1);
			
		}else
		{
			echo json_encode(2);
		}
		
		
	}
	
	public function exdel()
	{	
		
		$list = I('get.list');
		
		if($list == 'deal')
		{
			$ini['status'] =1;
			$ini['rid']= array('neq',0);
			$ini['deletemark'] = 0;
		}else if($list == 'follow')
		{	
			$ini['rid']= array('neq',0);
			$ini['ztime']=array('neq','0000-00-00');
			$ini['deletemark'] = 0;
		}else if($list == 'public')
		{
			$ini['rid']= array('eq',0);
			$ini['deletemark'] = 0;
		}else if($list == 'delete')
		{
			$ini['deletemark'] = 1;
		}
		else
		{
			$ini['rid']= array('neq',0);
			$ini['deletemark'] = 0;
		}
		
		
		
		$kt = I('get.kt');
		$jt = I('get.jt');
		$company = I('get.company');
		$s1 = I('get.s1');
		if($company == 0)
		{
			$enterprise=M('enterprise')->where(array("id"=>getcompanyId()))->field('id')->select();
			
			if(empty($enterprise) )
			{
				
				$dataar = date('Y-m-d H:i:s'); //时间
				$fileName=$dataar.'客户数据';
				$fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称
				
				import("Org.Util.PHPExcel");
				$objPHPExcel = new \PHPExcel();
				
				header('pragma:public');
				header('Content-type:application/vnd.ms-excel;charset=utf-8;name=$fileName.xls');
				header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
				$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				exit;
				
			}
			foreach ($enterprise as $k=>$v)
			{
				$yid .= $v['id'].',';
				
				
			}
			$yid  = substr($yid,0,-1);
			$ini['uid'] = array('in',$yid);
		}else
		{
			$ini['uid'] = $company;
		}
	
	/* 	if($ini['uid'][1] == "")
		{
			unset($ini['uid']);
		} */
		
		
		if($s1 == 0 || $s1 == 1)
		{
			$ini['ctime'] =array(array('elt',$jt),array('egt',$kt));
		}else
		{
			$ini['ztime'] =array(array('elt',$jt),array('egt',$kt));
		}
	
	
	$information = M('information')->where($ini)->select();
	foreach ($information as $key=>$val)
	{
			
		$information[$key]['rwname'] = M('salesman')->where(array('id'=>$val['rid']))->getField("name");
			
	}
	
	$dataar = date('Y-m-d H:i:s'); //时间
 	$fileName=$dataar.'客户数据';
 	$fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称
 	
 	import("Org.Util.PHPExcel");
 	$objPHPExcel = new \PHPExcel();
 	$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('A1', '负责人');
 
 	$objPHPExcel->getActiveSheet(0)->setCellValue('B1', '客户名称');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('C1', '客户类型');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('D1', '客户状态');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('E1', '客户性质');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('F1', '客户分级');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('G1', '客户行业');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('H1', '重要程度');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('I1', '客户来源');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('J1', '联系电话');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('K1', '详细地址');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('L1', '最后联系时间'); 
 	$objPHPExcel->getActiveSheet(0)->setCellValue('M1', '联系人');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('N1', '客户生日');
 	
	foreach ($information as $k=>$v)
	{		

		$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($k+2), $v['rwname']);
	 	 
		$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($k+2), $v['name']);
		if($v['type'] == 1)
		{
			$v['type'] = '最终客户';
		}else if($v['type'] == 2)
		{
			$v['type'] = '渠道客户';
		}else if($v['type'] == 3)
		{
			$v['type'] = '竞争对手';
		}else
		{
			$v['type'] = '未设置';
		}
		
		if($v['state']  == 1)
			{
				$v['state'] = '潜在客户';
			}else if($v['state']  == 2)
			{
				$v['state'] = '初步接触';
				
			}
			else if($v['state']   == 3)
			{
				$v['state']  = '持续跟进';
				
			}
			else if($v['state']  == 4)
			{
				$v['state']  = '成交客户';
				
			}else if($v['state']   == 5)
			{
				$v['state']  = '忠诚客户';
				
			}else if($v['state']   == 6)
			{
				
				$v['state']  = '无效客户';
			}else
			{
				$v['state']  = '未设置';
			}
			
			if($v['nature'] == 1)
			{
				$v['nature']  = '企业客户';
			}else if($v['nature']  == 2)
			{
				$v['nature'] = '个人客户';
			}else if($v['nature']  == 3)
			{
				$v['nature']  = '政府企业单位';
			}else
			{
				$v['nature']  = '未设置';
			}
			if($v['level'] == 1)
			{
				$v['level'] = '小型';
			}else if($v['level'] == 2)
			{
				$v['level'] = '中型';
			}else if($v['level'] == 3)
			{
				$v['level'] = '大型';
			}else
			{
				$v['level'] = '未设置';
			}
			
			
			if($v['industry'] == 1 )
			{
				$v['industry'] = '金融';
			}else if($v['industry'] == 2)
			{
					
				$v['industry'] = '电信';
			}else if($v['industry'] == 3)
			{
					
				$v['industry'] = '教育';
			}else if($v['industry'] == 4)
			{
					
				$v['industry'] = '高科技';
			}else if($v['industry'] == 5)
			{
					
				$v['industry'] = '政府';
			}else if($v['industry'] == 6)
			{
					
				$v['industry'] = '制造业';
			}else if($v['industry'] == 7)
			{
					
				$v['industry'] = '服务业';
			}else if($v['industry'] == 8)
			{
					
				$v['industry'] = '能源';
			}else if($v['industry'] == 9)
			{
					
				$v['industry']= '零售';
			}else if($v['industry']== 10)
			{
					
				$v['industry'] = '媒体';
			}else if($v['industry'] == 11)
			{
					
				$v['industry'] = '娱乐';
			}else if($v['industry'] == 12)
			{
					
				$v['industry'] = '咨询';
			}else if($v['industry'] == 13)
			{
					
				$v['industry'] = '非盈利事业';
			}else if($v['industry'] == 14)
			{
					
				$v['industry'] = '公用事业';
			}else if($v['industry'] == 15)
			{
					
				$v['industry'] = '其他';
			}else
			{
			
				$v['industry'] = '未设置';
			}
			
			
		$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($k+2), $v['type']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($k+2), $v['state']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($k+2), $v['nature']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($k+2), $v['level']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($k+2), $v['industry']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($k+2), $v['star']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($k+2), $v['source']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($k+2), $v['tel']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($k+2), $v['address']);
		if($v['ztime'] == '0000-00-00')
		{
			$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($k+2),'未设置');
		}else
		{
			$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($k+2), $v['ztime']);
		}
	
		$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($k+2), $v['contact']);
		$objPHPExcel->getActiveSheet(0)->setCellValue('N'.($k+2), $v['birthday']);
		
	}
 	
 	header('pragma:public');
 	header('Content-type:application/vnd.ms-excel;charset=utf-8;name=$fileName.xls');
 	header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
 	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 	$objWriter->save('php://output');
 	exit;
		
	}
	
	public function uploadCate(){
        $uid = session('id'); //个人用户id
     
        $uid = I('post.uid'); 
        // $getid = I('post.getid');   //企业id
        //Excel配置文件
        $config = array(
            'maxSize'    =>    553145728,
            'rootPath'   =>    './Public/Uploads/',
            'savePath'   =>    $uid.'kexcel/',    
            'saveName'   =>    array('uniqid'),
            'exts'       =>    array('xls','xlsx'),
            'autoSub'    =>    true,
            'replace'    =>    true,
            'subName'    =>    false,
        );

        $upload = new \Think\Upload($config);// 实例化上传类

        $info   =   $upload->upload();

        if(!$info){
            $data['errcode'] = 0;
            $data['errmsg'] = '上传失败';
            echo json_encode($data);
            die();
        }
        import("Org.Util.PHPExcel");
        $filename = "./Public/Uploads/".$uid."kexcel/".$info['file']['savename'];

        if($info['file']['ext'] == "xlsx"){
            $objReader = \PHPExcel_IOFactory::createReader("Excel2007");
        }else{
            $objReader = \PHPExcel_IOFactory::createReader("Excel5");
        }

        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename,$encode='utf-8');
        $objWorksheet = $objPHPExcel->getSheet(0);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        unlink("./Public/Uploads/".$uid."kexcel/".$info['file']['savename']);
        rmdir("./Public/Uploads/".$uid."kexcel");
        import("Common.Org.PHPExcel.Shared.Date");
        $shared = new \PHPExcel_Shared_Date();
        foreach($objWorksheet->getRowIterator() as $key => $row){
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach($cellIterator as $cell){
                $excelDatas[$k][] = $cell->getValue();//获取excel里的数据转成数组
            }
            $k++;
        }
		if(count($excelDatas)>101){
			$data["error"]=1;
			$data["errormsg"]="上传数据已经超出100条，已经超出服务器所承受范围，请重新分配上传！";
			echo json_encode($data);
			exit();
		}

		$data['errmsg']=array();
        $k=0;
        array_shift($excelDatas);//去数组头部
		
        foreach ($excelDatas as $key => $value) {
            $datalist[$key]['rid'] = $value[0];		//负责人
            $datalist[$key]['name'] = $value[1];	//客户名称
            $datalist[$key]['type'] = $value[2];	//客户类型
            $datalist[$key]['state'] = $value[3];	//客户状态
            $datalist[$key]['nature'] = $value[4];	//客户性质
            $datalist[$key]['level'] = $value[5];	//客户分级
            $datalist[$key]['industry'] = $value[6];	//客户行业
            $datalist[$key]['star'] = $value[7];	//重要程度
            $datalist[$key]['source'] = $value[8];	//客户来源
            $datalist[$key]['contact'] = $value[9];		//联系人
            $datalist[$key]['tel'] = $value[10];		//联系电话
            $datalist[$key]['address'] = $value[11];	//详细地址
            $datalist[$key]['birthday'] =  date('Y-m-d',$shared->ExcelToPHP($value[12]));	//客户生日
			$datalist[$key]['city'] = $value[13];	//城市
        }

        
        foreach ($datalist as $key => $value) {		//检测数据
        	$hang = $key+2;
			$datalist[$key]["hang"]=$hang;
        	if(empty($value['name'])){
                $data['code'] = 0;
                $data['errmsg'][] = '行数：'.$hang.' 客户名称为空！';
                $datalist[$key]['error']=1;
        	}

        	//判断客户名称是否重复
			$ifind=M('information')->where(array("deletemark"=>0,"name"=>$value["name"],"uid"=>$uid))->find();
            if(!empty($ifind)){
				$data['code'] = 0;
				$data['errmsg'][] = '行数：'.$hang.' 客户名称重复！';
				$datalist[$key]['error']=1;
			}
        	if(!empty($value['type'])){
        		$types = array("最终客户", "渠道客户", "竞争对手");
    			if(!in_array($value['type'], $types))
    			{
	                $data['code'] = 0;
	                $data['errmsg'][] = '行数：'.$hang.' 客户类型错误！';
	                $data['sample'] = '请在三种类型中‘最终客户’,‘渠道客户’,‘竞争对手’任选其一';
	                $datalist[$key]['error']=1;
    			}
    		}else{
                $data['code'] = 0;
                $data['msg'][] = '行数：'.$hang.' 客户类型未填写！';
				$datalist[$key]['type']=0;
    		}

    		if(!empty($value['state']))
    		{
    			$states = array("潜在客户", "初步接触", "持续跟进","成交客户","忠诚客户","无效客户");
    			if(!in_array($value['state'], $states))
				{
	                $data['code'] = 0;
	                $data['errmsg'][] = '行数：'.$hang.' 客户状态不正确！';
	                $data['sample'] = '请在六种状态中‘潜在客户’,‘初步接触’,‘持续跟进’,‘成交客户’,‘忠诚客户’,‘无效客户’任选其一';
	                $datalist[$key]['error']=1;
				}
			}else{
                $data['code'] = 0;
                $data['msg'][] = '行数：'.$hang.' 客户状态未填写！';
                $data['sample'] = '请在六种状态中‘潜在客户’,‘初步接触’,‘持续跟进’,‘成交客户’,‘忠诚客户’,‘无效客户’任选其一';
				$datalist[$key]['state']=0;
			}

			if(!empty($value['nature']))
			{
    			$natures = array("企业客户", "个人客户", "政府企业单位");
    			if(!in_array($value['nature'], $natures))
				{
	                $data['code'] = 0;
	                $data['errmsg'][] = '行数：'.$hang.' 客户性质类型错误！';
	                $data['sample'] = '请在三种类型中‘企业客户’,‘个人客户’,‘政府企业单位’任选其一';
	                $datalist[$key]['error']=1;
				}
			}else{
                $data['code'] = 0;
                $data['msg'][] = '行数：'.$hang.' 客户性质未填写！';
				$datalist[$key]['nature']=0;
			}

			if(!empty($value['level']))
			{
    			$levels = array("小型", "中型", "大型");
    			if(!in_array($value['level'], $levels))
  				{
	                $data['code'] = 0;
	                $data['errmsg'][] = '行数：'.$hang.' 客户分级类型错误！';
	                $data['sample'] = '请在三种类型中‘小型’,‘中型’,‘大型’任选其一';
	                $datalist[$key]['error']=1;
  				}
			}else{
                $data['code'] = 0;
                $data['msg'][] = '行数：'.$hang.' 客户分级未填写！';
				$datalist[$key]['level']=0;
			}

			if(!empty($value['industry']))
			{
    			$industrys = array("金融", "电信", "教育","高科技", "政府", "制造业","服务业", "能源", "零售","媒体", "娱乐", "咨询","非盈利事业", "公用事业", "其他");
    			if(!in_array($value['industry'], $industrys))
  				{
	                $data['code'] = 0;
	                $data['errmsg'][] = '行数：'.$hang.' 客户分级类型错误！';
	                $data['sample'] = '请在表格中任选其一';
	                $datalist[$key]['error']=1;
  				}
			}else{
                $data['code'] = 0;
                $data['msg'][] = '行数：'.$hang.' 客户行业未填写！';
				$datalist[$key]['industry']=0;
			}

			if(!empty($value['star']))
			{
				if(!is_numeric($value['star'])){
	                $data['code'] = 0;
	                $data['errmsg'][] = '行数：'.$hang.' 重要程度类型错误！';
	                $data['sample'] = '请在表格中填入0-5之间的数字';
	                $datalist[$key]['error']=1;
				}
			}else{
                $data['code'] = 0;
                $data['msg'][] = '行数：'.$hang.' 重要程度未选择！';
				$datalist[$key]['star']=0;
			}

			if(!empty($value['source']))
			{
//    			$sources = array("网络推广", "电话销售", "渠道代理");
//    			if(!in_array($value['source'], $sources))
//    			{
//	                $data['code'] = 0;
//	                $data['errmsg'][] = '行数：'.$hang.' 客户来源类型错误！';
//	                $data['sample'] = '请在表格中选择';
//	                $datalist[$key]['error']=1;
//				}
			}else{
                $data['code'] = 0;
                $data['msg'][] = '行数：'.$hang.' 客户来源未填写！';

			}
			if(empty($value['contact']))
			{
                $data['code'] = 0;
                $data['errmsg'][] = '行数：'.$hang.' 客户联系人未填写！';
                $data['sample'] = '请在表格中填写';
                $datalist[$key]['error']=1;
			}

			if(!empty($value['tel']))
			{
				if(!preg_match("/^1[34578]{1}\d{9}$/", $value['tel'])){
	                $data['code'] = 0;
	                $data['msg'][] = '行数：'.$hang.' 客户联系电话格式不正确！';
				}
			}else{
                $data['code'] = 0;
                $data['errmsg'][] = '行数：'.$hang.' 客户联系电话未填写！';
                $data['sample'] = '请在表格中填写';
                $datalist[$key]['error']=1;
			}

			
			// if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $value['rid'])){
				if(!empty($value['rid'])){
					$map['name'] = $value['rid'];
					$map['uid'] = $uid;

					$id = M('salesman')->where($map)->getField('id');
					if(!empty($id)){
						$datalist[$key]['rid'] = $id;
					}else{
						$data['msg'][] = '行数：'.$hang.' 没有查到客户负责人';
						$datalist[$key]['rid'] = 0;
					}
				}
			// }else{	
                // $data['code'] = 0;
                // $data['errmsg'][] = '行数：'.$hang.' 客户负责人格式不正确！';
                // $data['sample'] = '请在表格中填写汉字格式';
                // $datalist[$key]['error']=1;
			// }

			
        }
        $sjts = count($datalist);
        if($sjts > 100){
            $data['code'] = 0;
            $data['errmsg'] = '上传数据不能大于100条';
        }else{
            $data['code'] = 1;
            $data['datalist'] = $datalist;
        }
	    echo json_encode($data);
	
	}

	public function uploadCategory(){
		//$uid = session('id');

	    $uid = I('post.uid');
		$excelDatas = I('post.datalist');
		if(!empty($excelDatas))
		{
            foreach ($excelDatas as $key => $value) {
                if($value['type'] == '最终客户'){
                    $excelDatas[$key]['type'] = 1;
                }else if($value['type'] == '渠道客户'){
                    $excelDatas[$key]['type'] = 2;
                }else if($value['type'] == '竞争对手'){
                    $excelDatas[$key]['type'] = 3;
                }else{
                	$excelDatas[$key]['type'] = 0;
                }

                if($value['state'] == '潜在客户'){
                    $excelDatas[$key]['state'] = 1;
                }else if($value['state'] == '初步接触'){
                    $excelDatas[$key]['state'] = 2;
                }else if($value['state'] == '持续跟进'){
                    $excelDatas[$key]['state'] = 3;
                }else if($value['state'] == '成交客户'){
                    $excelDatas[$key]['state'] = 4;
                }else if($value['state'] == '忠诚客户'){
                    $excelDatas[$key]['state'] = 5;
                }else if($value['state'] == '无效客户'){
                    $excelDatas[$key]['state'] = 6;
                }else{
                	$excelDatas[$key]['state'] = 0;
                }

                if($value['nature'] == '企业客户'){
                    $excelDatas[$key]['nature'] = 1;
                }else if($value['nature'] == '个人客户'){
                    $excelDatas[$key]['nature'] = 2;
                }else if($value['nature'] == '政府企业单位'){
                    $excelDatas[$key]['nature'] = 3;
                }else{
                	$excelDatas[$key]['nature'] = 0;
                }

                if($value['level'] == '小型'){
                    $excelDatas[$key]['level'] = 1;
                }else if($value['level'] == '中型'){
                    $excelDatas[$key]['level'] = 2;
                }else if($value['level'] == '大型'){
                    $excelDatas[$key]['level'] = 3;
                }else{
                	$excelDatas[$key]['level'] = 0;
                }

                if($value['industry'] == '金融'){
                    $excelDatas[$key]['industry'] = 1;
                }else if($value['industry'] == '电信'){
                    $excelDatas[$key]['industry'] = 2;
                }else if($value['industry'] == '教育'){
                    $excelDatas[$key]['industry'] = 3;
                }else if($value['industry'] == '高科技'){
                    $excelDatas[$key]['industry'] = 4;
                }else if($value['industry'] == '政府'){
                    $excelDatas[$key]['industry'] = 5;
                }else if($value['industry'] == '制造业'){
                    $excelDatas[$key]['industry'] = 6;
                }else if($value['industry'] == '服务业'){
                    $excelDatas[$key]['industry'] = 7;
                }else if($value['industry'] == '能源'){
                    $excelDatas[$key]['industry'] = 8;
                }else if($value['industry'] == '零售'){
                    $excelDatas[$key]['industry'] = 9;
                }else if($value['industry'] == '媒体'){
                    $excelDatas[$key]['industry'] = 10;
                }else if($value['industry'] == '娱乐'){
                    $excelDatas[$key]['industry'] = 11;
                }else if($value['industry'] == '咨询'){
                    $excelDatas[$key]['industry'] = 12;
                }else if($value['industry'] == '非盈利事业'){
                    $excelDatas[$key]['industry'] = 13;
                }else if($value['industry'] == '公用事业'){
                    $excelDatas[$key]['industry'] = 14;
                }else if($value['industry'] == '其他'){
                    $excelDatas[$key]['industry'] = 15;
                }else{
                	$excelDatas[$key]['industry'] = 0;
                }

//                if($value['source'] == '网络推广'){
//                    $excelDatas[$key]['source'] = 1;
//                }else if($value['source'] == '电话销售'){
//                    $excelDatas[$key]['source'] = 2;
//                }else if($value['source'] == '渠道代理'){
//                    $excelDatas[$key]['source'] = 3;
//                }else{
//                	$excelDatas[$key]['source'] = 0;
//                }

                $excelDatas[$key]['ctime'] = date('Y-m-d',time());
                $excelDatas[$key]['deletemark'] = 0;
                $excelDatas[$key]['uid'] = $uid;
				$excelDatas[$key]['province'] = $excelDatas[$key]['provincetext'];
				$excelDatas[$key]['city'] = $excelDatas[$key]['citytext'];
				unset($excelDatas[$key]['provincetext']);
				unset($excelDatas[$key]['citytext']);
            }
            M()->startTrans(); 
            foreach ($excelDatas as $key => $value) {
            	if(empty($value['error'])){
	            	$data = M('information')->add($value);
					$fid=M('information')->getLastInsID();
					/**添加跟踪记录**/
					$array=array();
					$array["yrid"]=0;
					$array["nrid"]=0;
					$array["fid"]=$fid;
					$array["ctime"]=date("Y-m-d H:i:s");
					$array["status"]=5;
					$array["moveperson"]=getUserMessge();

					M('transfer_log')->add($array);
		            if(!$data)
		            {
		                M()->rollback();
		            }
	            }
            }
            $commit = M()->commit();
            if($commit){
                $res['errcode'] = 1;
                $res['errmsg'] = "上传成功！";
            }else{
                $res['errcode'] = 0;
                $res['errmsg'] = "请联系管理员！"; 
            }
		}else{
			$res['errcode'] = 0;
            $res['errmsg'] = "上传数据空！"; 
		}
        echo json_encode($res);
	}

	public function delewh()
	{
		$tid = I('post.tid');
		$status=I('post.status');
		$tid = substr($tid,0,-1);
		$where['id'] = array('in',$tid);
		if($status==1){
		    $information = M('information')->where($where)->delete();
		}else{
		    $information = M('information')->where($where)->save(array('deletemark'=>1));
		}
		
		if($information)
		{	
			$acr['data'] = 1;
			
		}else
		{	
			$acr['data'] = 2;
			
		}
		echo json_encode($acr);
	}
	public function upadimg()
	{		
		// 				echo '<pre>';
		// print_r($_FILES);die;

		//上传
				if($_FILES['file']['tmp_name']){
					
					
			 	$upload = new \Think\Upload();// 实例化上传类
				$upload->maxSize   =     3145728 ;// 设置附件上传大小
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				$upload->rootPath  =     './Public/Uploads/'; // 设置附件上传根目录
				$upload->savePath  =     'upapplon/'; // 设置附件上传（子）目录
				$upload->saveName  =   array('uniqid');
				$upload->autoSub   =   true;
				$upload->replace   =   true;
				$upload->subName   =   false;
				// 上传文件
				$info   =   $upload->upload();
				if(!$info) {// 上传错误提示错误信息
					$ini['path'] = 1;
					echo json_encode($ini);	
				}
				else{// 上传成功
					//xapp 需要加open
					$ini['srcimg'] =$info['file']['savename'];
					
					$ini['path'] = $info['file']['savename'];
	
					echo json_encode($ini);				
				} 
			}
	}
	public function addfowwl()
	{
		$ini['img'] = I('post.img');
		$ini['oid'] = I('post.id');
		$ini['info'] = I('post.info');
		$ini['ftime'] =$time = date('Y-m-d H:i:s');
		$rid=M('information')->where(array('id'=>I("post.id")))->getField("rid");
		$ini['rid'] =$rid;
		$ini['followmessage']=getUserMessge();
		$followup = M('follow')->add($ini);


		if($followup !== false)
		{
			$information = M('information')->where(array('id'=>I("post.id")))->setfield('ztime',$time);
			echo json_encode(1);
		}
		

	}
	public function nonefolw()
	{
		$id = I('post.id');
		
		//$followup = M('follow')->where(array('oid'=>$id))->select();->join(C("DB_PREFIX")."salesman as s on s.id=i.rid")
		$followup =M('follow as f')->where(array('f.oid'=>$id))->order("id desc")->select();
		foreach ($followup as $k=>$row){
			$followup[$k]["ftime"]=date("Y-m-d H:i:s",strtotime($row["ftime"]));
			//$followup[$k]["name"]=M('salesman')->where(array("id"=>$row["rid"]))->getField("name");
			$moveperson=explode("|",$row["followmessage"]);
			$followup[$k]["name"]=$moveperson[1];
			$followup[$k]["role"]=$this->getrole($moveperson[0],$moveperson[2]);
		}
		echo json_encode($followup);

	}

	private function  getrole($table,$role){
		switch ($table){
			case 'admin':
				switch ($role){
					case '1':
						return '启企业总账号';
						break;
					case '4':
						return '企航企业CEO';
						break;
					case '5':
						return '企业管理员';
						break;
					case '2':
						return '企航财务总监';
						break;
					case '3':
						return '大区总监';
						break;

				}
				break;
			case 'user':
				switch ($role){
					case '8':
						return '分公司总经理';
						break;
					case '7':
						return '分公司副总经理';
						break;
					case '6':
						return '业务总监';
						break;
					case '5':
						return '业务副总监';
						break;
				}
				break;
			case 'salesman':
				switch ($role){
					case '5':
						return '业务经理';
						break;
					case '4':
						return '业务副经理';
						break;
					case '3':
						return '业务主管';
						break;
					case '2':
						return '业务副主管';
						break;
					case '2':
						return '业务员';
						break;
				}
				break;
		}
	}

	//移交
	public function listname(){
		$tid = I('post.tid');
		$id = $newstr = substr($tid,0,strlen($tid)-1); 
		$where['id'] = array('in', $id);
		$info = M('information')->where($where)->select();//查出客户的基本资料
		foreach($info as $k => $v){
			$salid[$k] =  $v['rid'];
		}
		$rid['id'] = array('in', $salid);
		$salesman = M('salesman')->where($rid)->select();
	foreach($info as $k => $v){
		foreach($salesman as $key => $val){
			if($val['id'] == $v['rid']){
				$info[$k]['fname'] = $val['name'];
			}
		}
	}
		echo json_encode($info);
	}

	public function overname(){
		$type = I('post.type');

		if($type == 1){
			$val = I('post.val');
			$search['uid'] = 99;
			$search="name like '%$val%'";
			$salesman = M('salesman')->where($search)->select();
		}else{
			$salesman = M('salesman')->where(array('uid'=>99))->select();
		}
		
		echo json_encode($salesman);
	}

	public function checkedname(){
		$tid = I('post.tid');
		$id = I('post.id');
		$ttid = $newstr = substr($tid,0,strlen($tid)-1);
		$kid['id'] = array('in', $ttid);
		$data['rid'] = $id;
		M('information')->where($kid)->save($data);
		echo json_encode(1);
	}
	//退回公海
	public function backspacing(){
		$tid = I('post.tid');
		$reason= I('post.reason');
		$ttid = $newstr = substr($tid,0,strlen($tid)-1);
		$where['id'] = array('in', $ttid);
		$data['rid'] = 0;
	
		$infoselect=M('information')->where($where)->select();
		foreach ($infoselect as $row){
			$array = array();
			$array["yrid"] = $row["rid"];
			$array["nrid"] = 0;
			$array["fid"] = $row["id"];
			$array["ctime"] = date("Y-m-d H:i:s");
			$array["status"] = 3;
			$array["reason"] = $reason;
			$array["moveperson"]=getUserMessge();
			M('transfer_log')->add($array);
		}
		M('information')->where($where)->save($data);
		echo json_encode(1);


	}

	public function savewhere()
	{
		$id = I('post.id');

		$information = M('information')->where(array('id'=>$id))->find();
	// echo '<pre>';
	// print_r($information);
		$salesman = M('salesman')->where(array('uid'=>$information['uid']))->select();
		 
		//查询分公司id
		$leid = M('user')->where(array('uid'=>$information['uid'],'lectype'=>'1'))->getField('uid');
		//$information['uid']
		
		//查询分公司
		$eid = M("enterprise")->where(array('id'=>$leid))->getField('aid');

		//查询所有分公司
		
		
		$esid = M('enterprise')->where(array('aid'=>$eid))->getField("id",true);

   
		$esid=implode(",",$esid);
		$kid = array('in' ,$esid);

		$enterprise = M('user')->where(array('uid'=>$kid))->select();

		$ini['act'] = $information;
		// echo '<pre>';
		// print_r($ini['act']);
		$ini['fin'] = $salesman;
		$ini['letype'] = $enterprise;
		echo json_encode($ini);
	}
	
	public function down(){
	    $file = "./Public/Uploads/download/customer.xls";
	  
	    if(is_file($file)){
	        $length = filesize($file);
	        header("Content-Description: File Transfer");
	        header('Content-type: application/vnd.ms-excel;');
	        header('Content-Length:' . $length);
			$filename="客户模板";
			$filename=iconv("utf-8", "gb2312", $filename);
	        header('Content-Disposition: attachment; filename='.$filename.'.xls');
	
	        readfile($file);
	        exit;
	    }
	}

	public function restore(){
		$tid = I('post.tid');//客户ID
		$tid = substr($tid,0,strlen($tid)-1);
		$kid = array('in' ,$tid);
		$data['deletemark'] = 0;
		M('information')->where(array('id'=>$kid))->save($data);
		echo json_encode(1);

	}
	
}
