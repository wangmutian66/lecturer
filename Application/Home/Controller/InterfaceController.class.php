<?php
namespace Home\Controller;
use Think\Controller;
class InterfaceController extends Controller {

	//任务大厅
	public function _initialize(){
		header('content-type:application:json;charset=utf8');
		header('Access-Control-Allow-Origin:*');
		header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Methods:GET');
		header('Access-Control-Allow-Headers:x-requested-with,content-type');
		
	}
    public function login(){

    	$account = I('post.mobile');
    	$password = md5(I('post.password'));
    	$user = M('user')->where(array('account'=>$account,'pwd'=>$password,'lectype'=>1))->find();
    	if(empty($user)){
    		$result['errcode'] = 1;
            $result['errmsg'] = "账号或者密码错误！";
    	}else{
            $result["uid"]=$user["uid"]; //分公司id
    		$result["jid"]=$user["id"];  //讲师id
    		$result['code'] = 2;
            $result['errmsg'] = "登陆成功！";

    	}

        echo json_encode($result); 
    }
     public function rank(){
     	$jid = I('post.jid');//25讲师id

     	$uid = I('post.uid');//23企业id
    	$db = C('DB_PREFIX');
        $etime=I('post.timeyear','');
        $stime=I('post.beforeyea','');
        $name=I('post.lsor','');

        //拿到讲师id下的学员
	    $information  = M('information')->where(array('profitid'=>$jid))->select();
	    foreach ($information as $key => $value) {
	    	$ini[$key]['name'] = $value['name'];
	    	$ini[$key]['tel'] = $value['tel'];
	    	 $course_com =  M('course_com')->where(array('fid'=>$value['id']))->select();
	    }
		foreach ($course_com as $key => $value) {
			$ini[$key]['time'] = $value['time'];
		   	$course_com_order =  M('course_com_order')->where(array('comid'=>$value['id']))->select(); 
		}

		foreach ($course_com_order as $key => $value) {
			$ini[$key]['time'] = $value['time'];
			$ini[$key]['ticket'] = $value['ticket'];
			$ini[$key]['planning'] = $value['planning'];
			$ini[$key]['card'] = $value['card'];
			$ini[$key]['friends'] = $value['friends'];
			$ini[$key]['disciple'] = $value['disciple'];
			$ini[$key]['typet'] = $value['typet'];
			$ini[$key]['typep'] = $value['typep'];
			$ini[$key]['typec'] = $value['typec'];
			$ini[$key]['typef'] = $value['typef'];
			$ini[$key]['typed'] = $value['typed'];
		}
	 	$data = M('information as i')
	 	->join($db.'enterprise as e on i.uid=e.id')
	 	->where(array('i.uid'=>$uid))
	 	->select();
	 	
	 	foreach ($data as $key => $value) {
	 		$ini[$key]['tczong'] = $value['leafletstc']*$value['tc'];//小票的金额
    		$ini[$key]['cczong'] = $value['leafletscc']*$value['cc'];//卡的金额
    		$ini[$key]['fczong'] = $value['leafletsfc']*$value['fc'];//朋友圈的金额
    		$ini[$key]['dczong'] = $value['leafletsdc']*$value['dc'];//弟子的金额
    		$ini[$key]['nczong'] = $value['leafletsnc']*$value['nc'];//九大规划的金额
    		/*.............提成比例.....................*/
    		$ini[$key]['tcbili'] = $value['tc'];//小票的提成比例
    		$ini[$key]['ccbili'] = $value['cc'];//卡的提成比例
    		$ini[$key]['fcbili'] = $value['fc'];//朋友圈的提成比例
    		$ini[$key]['ncbili'] = $value['nc'];//九大规划的提成比例
    		$ini[$key]['dcbili'] = $value['dc'];//弟子的提成比例
	   		/*.............提成.....................*/
    		$ini[$key]['tctihceng'] = $value['leafletstc']*$value['tc']*$value['dc'];//弟子的提成
    		$ini[$key]['cctihceng'] = $value['leafletscc']*$value['cc']*$value['cc'];//卡的提成
    		$ini[$key]['fctihceng'] = $value['leafletsfc']*$value['fc']*$value['fc'];//朋友圈的提成
    		$ini[$key]['nctihceng'] = $value['leafletsnc']*$value['nc']*$value['nc'];//九大规划的提成
    		$ini[$key]['dctihceng'] = $value['leafletsdc']*$value['dc']*$value['dc'];//弟子的提成
	 	}

     echo json_encode($ini);


    }
    //显示老师信息
    function qwe(){
    	$jid = I('post.jid');//25讲师id
     	$uid = I('post.uid');//23企业id
    	$db = C('DB_PREFIX');
    	$user = M('user')->where(array('id'=>$jid))->find();
    	$data = M('information as i')
	 	->join($db.'enterprise as e on i.uid=e.id')
	 	->where(array('i.uid'=>$uid))
	 	->select();
	 	foreach ($data as $key => $value) {
	 		// $ini[$key]['tczong'] = $value['leafletstc']*$value['tc'];//小票的金额
    // 		$ini[$key]['cczong'] = $value['leafletscc']*$value['cc'];//卡的金额
    // 		$ini[$key]['fczong'] = $value['leafletsfc']*$value['fc'];//朋友圈的金额
    // 		$ini[$key]['dczong'] = $value['leafletsdc']*$value['dc'];//弟子的金额
    // 		$ini[$key]['nczong'] = $value['leafletsnc']*$value['nc'];//九大规划的金额
    		$ini[$key]['zong'] = $value['leafletstc']*$value['tc']+$value['leafletscc']*$value['cc']+$value['leafletsfc']*$value['fc']+$value['leafletsdc']*$value['dc']+$value['leafletsnc']*$value['nc'];
    		/*.............提成比例.....................*/
    		// $ini[$key]['tcbili'] = $value['tc'];//小票的提成比例
    		// $ini[$key]['ccbili'] = $value['cc'];//卡的提成比例
    		// $ini[$key]['fcbili'] = $value['fc'];//朋友圈的提成比例
    		// $ini[$key]['ncbili'] = $value['nc'];//九大规划的提成比例
    		// $ini[$key]['dcbili'] = $value['dc'];//弟子的提成比例
	   		/*.............提成.....................*/
    		// $ini[$key]['tctihceng'] = $value['leafletstc']*$value['tc']*$value['dc'];//弟子的提成
    		// $ini[$key]['cctihceng'] = $value['leafletscc']*$value['cc']*$value['cc'];//卡的提成
    		// $ini[$key]['fctihceng'] = $value['leafletsfc']*$value['fc']*$value['fc'];//朋友圈的提成
    		// $ini[$key]['nctihceng'] = $value['leafletsnc']*$value['nc']*$value['nc'];//九大规划的提成
    		// $ini[$key]['dctihceng'] = $value['leafletsdc']*$value['dc']*$value['dc'];//弟子的提成
    		$ini[$key]['tichengzong'] = $value['leafletstc']*$value['tc']*$value['dc']+$value['leafletscc']*$value['cc']*$value['cc']+$value['leafletsfc']*$value['fc']*$value['fc']+$value['leafletsnc']*$value['nc']*$value['nc']+$value['leafletsdc']*$value['dc']*$value['dc'];
    		
	 	}
	 	//echo json_encode($ini,$user);
    }
    public function lecturer_list(){
		header("Content-type: text/html; charset=utf-8");
		$stime = date('Y-m-01', strtotime(date("Y-m-d")));//当前月第一天
        $etime =  date('Y-m-d', strtotime("$stime +1 month -1 day"));//当前月最后一天
    	$jid = I('post.jid');
    	$db = C('DB_PREFIX');
		$uid = I('post.uid','');

		$stime = I('post.start_date',$stime);
		$etime = I('post.end_date',$etime);
		if($stime=="" || $etime ==""){
			$stime = date('Y-m-01', strtotime(date("Y-m-d")));//当前月第一天
			$etime =  date('Y-m-d', strtotime("$stime +1 month -1 day"));//当前月最后一天
		}


		$fname = I('post.btnseinpu','');
		$infor=array();
		$infor["profitid"]=$jid;
        //$where =" profitid =$jid ";
        if($fname!=""){
			$infor["name"]=array("like","%".$fname."%");
			//$where.=" AND name like '%".$fname."%' ";
		}

    	$information = M('information as i')
			    	->join($db.'course_com as m on i.id=m.fid')
			    	->where($infor)
			    	->select();

    	if(empty($information)){
    		echo json_encode(1);
			die();
    	}

		foreach($information as $key => $val){
			$cid[$key] = $val['cid'];//客户ID
			$comid[$key] =$val['id'];
		}
		$cid = array("in" ,$cid);//客户ID

		$cousr=array();
		$cousr["id"]=$cid;
		if($uid!=""){
			$cousr["uid"]=$uid;
		}

		$course = M('course')->where($cousr)->select();
		foreach($course as $k => $v){
			$eid[$k] = $v['uid'];
			$id[$k] = $v['id'];
		}
		if(empty($course)){
			echo json_encode(2);die;
		}
		// echo '<pre>';print_r($course);die;
		$eid = array("in" ,$eid);
		$enterprise = M('enterprise')->where(array('id'=>$eid))->select();//分公司

		$comid = array("in" ,$comid);
		$arr=array();
		$arr["comid"]=$comid;

		$arr['time'] =array(array('egt',$stime),array('elt',$etime));

		$course_com_order = M('course_com_order')->where($arr)->order("id desc")->select();
	

		 foreach($information as $k =>$v) {
		 			foreach($course as $key => $val) {
			 			if($val['id'] == $v['cid']){
			 				$information[$k]['classname'] = $val['classname'];
			 				$information[$k]['starttime'] = $val['starttime'];
			 				foreach($enterprise as $ke => $ve) {
			 					if($ve['id'] == $val['uid']){
				 						$information[$k]['kid'] =$v['id'];
				 						$information[$k]['Qyname'] = $ve['Qyname'];		 						
				 						$information[$k]['tc'] = $ve['tc'];
				 						$information[$k]['cc'] = $ve['cc'];
				 						$information[$k]['fc'] = $ve['fc'];
				 						$information[$k]['dc'] = $ve['dc'];
				 						$information[$k]['nc'] = $ve['nc'];
			 						
			 					}
			 				}
			 			}
		 		    }
		 }

		 foreach($course_com_order as $k => $v) {

		 	foreach($information as $ki => $vi) {
		 		if($v['comid'] == $vi['kid']){
		 			$course_com_order[$k]['kname'] = $vi['name'];//客户名称
		 			$course_com_order[$k]['tel'] = $vi['tel'];//电弧
		 			$course_com_order[$k]['Qyname'] = $vi['Qyname'];//分公司名
		 			$course_com_order[$k]['dtime'] = $v['time'];//订单时间
		 			$course_com_order[$k]['classname'] = $vi['classname'];//课程名称

		 			$course_com_order[$k]['piao_ratio'] = $vi['tc'];//弟子提成比
		 			$course_com_order[$k]['jiu_ratio'] = $vi['nc'];//九大提成比
		 			$course_com_order[$k]['car_ratio'] = $vi['cc'];//卡提成比
		 			$course_com_order[$k]['fri_ratio'] = $vi['fc'];//朋友圈提成比
		 			$course_com_order[$k]['dz_ratio'] = $vi['dc'];//弟子提成比

		 			$course_com_order[$k]['leafletstc'] = $v['ticket'] * $v['leafletstc'];
		 			$course_com_order[$k]['leafletsnc'] = $v['planning'] * $v['leafletsnc'];
		 			$course_com_order[$k]['leafletscc'] = $v['card'] * $v['leafletscc'];
		 			$course_com_order[$k]['leafletsfc'] = $v['friends'] * $v['leafletsfc'];
		 			$course_com_order[$k]['leafletsdc'] = $v['disciple'] * $v['leafletsdc'];

		 			$course_com_order[$k]['piao_money'] = $vi['tc'] * $v['ticket']*$v['leafletstc']/100;//弟子提成
		 			$course_com_order[$k]['jiu_money'] = $vi['nc'] * $v['planning'] * $v['leafletsnc']/100;//九大提成
		 			$course_com_order[$k]['car_money'] = $vi['cc'] * $v['card'] * $v['leafletscc']/100;//卡提成
		 			$course_com_order[$k]['fri_money'] = $vi['fc'] * $v['friends'] * $v['leafletsfc']/100;//朋友圈提成
		 			$course_com_order[$k]['dz_money'] = $vi['dc'] * $v['disciple'] * $v['leafletsdc']/100;//弟子提成
                     
		 			$performance += $v['ticket'] * $v['leafletstc'] + $v['planning'] * $v['leafletsnc'] +$v['card'] * $v['leafletscc'] + $v['friends'] * $v['leafletsfc'] + $v['disciple'] * $v['leafletsdc'];//业绩

		 			$deduct += $vi['tc'] * $v['ticket']*$v['leafletstc']/100 + $vi['nc'] * $v['planning'] * $v['leafletsnc']/100 + $vi['cc'] * $v['card'] * $v['leafletscc']/100 + $vi['fc'] * $v['friends'] * $v['leafletsfc']/100 + $vi['dc'] * $v['disciple'] * $v['leafletsdc']/100;//提成

		 			if($v['typet'] == 2){//已发的票
						$sent_t += ($v['ticket'] * $vi['tc']/100 * $v['leafletstc']);//已发
					}

					if($v['typep'] == 2){//已发的九大门票
						$sent_n += ($v['planning'] * $vi['nc']/100 * $v['leafletsnc']);//已发
					}

					if($v['typec'] == 2){//已发的卡
						$sent_c += ($v['card'] * $vi['cc']/100 * $v['leafletscc']);//已发
					}

					if($v['typef'] == 2){//已发的朋友圈
						$sent_f += ($v['friends'] * $vi['fc']/100 * $v['leafletsfc']);//已发
					}

					if($v['typed'] == 2){//已发的弟子
						$sent_d += ($v['disciple'] * $vi['dc']/100 * $v['leafletsdc']);//已发
					}

		 			$send = $sent_t + $sent_n + $sent_c + $sent_f + $sent_d;//已发

		 		}
		 		
		 	}
		 }
           
         $course_com_orders['performance']=$performance;
         $course_com_orders['deduct']=$deduct;
         $course_com_orders['send']=$send;
         $course_com_orders['unbilled']=$deduct - $send;
         $course_com_orders['stime']=$stime;
         $course_com_orders['etime']=$etime;
		 $course_com_orders['fname']=$fname; //客户名称
		 if(isset($uid)){
			 $course_com_orders['uname']=M('enterprise')->where(array("id"=>$uid))->getField("Qyname"); //分公司
		 }

         $course_list=array("order"=>$course_com_order,"orders"=>$course_com_orders);
 		 echo json_encode($course_list);
		// echo '<pre>';print_r($course_com_order);
		// die;
    }

    public function fuid_list(){
    	$jid = I('post.jid');
    	$user = M('user')->where(array('id'=>$jid))->find();
    	$enterprise = M('enterprise')->where(array('id'=>$user['uid']))->find();//讲师所属分公司

    	$j_list = M('enterprise')->where(array('aid'=>$enterprise['aid']))->select();

    	echo json_encode($j_list);

    	// 
    }

    public function showinfo(){
    	$jid = I('post.jid');
    	$user =M('user')->where(array('id'=>$jid))->find();
    	$enterprise = M('enterprise')->where(array('id'=>$user['uid']))->find();
    	$j_list = M('enterprise')->where(array('aid'=>$enterprise['aid']))->select();
   		foreach ($j_list as $key=>$v1) {
			 if($v1==$enterprise){
			   unset($j_list[$key]);//删除$a数组同值元素
			 }
		}
    	$result['user'] = $user;
    	$result['enterprise'] = $enterprise;
    	$result['j_list'] = $j_list;
    	// echo '<pre>';print_r($j_list);die;
    	echo json_encode($result);
    }

    public function change(){
    	$uid = I('post.uid');
    	$enterprise = M('enterprise')->where(array('id'=>$uid))->find();
    	echo json_encode($enterprise);
    }

    public function editpass(){
    	$jid = I('post.jid');
    	$oldpass = md5(I('post.oldpass'));
    	$newpass = md5(I('post.newpass'));

    	$user = M('user')->where(array('id'=>$jid))->find();

    	if($oldpass == $user['pwd']){
    		M('user')->where(array('id'=>$jid))->save(array('pwd'=>$newpass));
    		$result['errcode'] = 1;
    		$result['errmsg'] = '修改成功';
    	}else{
    		$result['errcode'] = 0;
    		$result['errmsg'] = '密码错误';
    	}
    				echo json_encode($result);

    }

//订单详情
    public function details(){
    	$oid = I('post.oid');
    	$db = C('DB_PREFIX');
    	$order = M('course_com_order as o')
		    	->join($db.'course_com as c on c.id=o.comid')
		    	->join($db.'course as m on m.id=c.cid')
		    	->join($db.'information as i on i.id=c.fid')
		    	->join($db.'enterprise as e on e.id=m.uid')
		    	->join($db.'salesman as s on s.id=o.rid')
		    	->join($db.'user as u on u.id=m.jid')
		    	->field('s.name as sname,e.Qyname,m.classname,m.place,i.name as iname,u.name as uname,c.pnum,o.time,o.ticket,o.planning,o.card,o.friends,o.disciple')
		    	->where(array('o.id'=>$oid))
		    	->find();
		    	// $result['order'] = $order;
			echo json_encode($order);

    	// echo '<pre>';print_r($result);die;
    }

 }