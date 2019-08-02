<?php
namespace Home\Controller;
use Think\Controller;

class ClientController extends BaseController 
{
	public function index(){
		$uid = getcompanyId();
		$enterprise = M('enterprise')->where(array('id'=>$uid))->select();
		 $this->assign('enterprise',$enterprise);
		$this->display();

	}

	public function searove(){
		$p = I('post.p');
   		$limit = 6;
   		$first = ($p-1) * $limit;

		$uid = I('post.uid');
		$se1 = I('post.se1','');
		$se2 = I('post.se2','');






		if($uid == '0'){
			$uid = getcompanyId();
		}
		$data['uid'] = $uid;
		if($se1!=""){
			$data['province'] = $se1;
		}
		if($se2!=""){
			$data['city'] = $se2;
		}
		$data['deletemark'] = 0;
		$information = M('information')->where($data)->order("id desc")->limit($first,$limit)->select();

		// echo '<pre>';print_r($se2);die;
		echo json_encode($information);
		
	}

	public function page(){
		$se1 = I('get.se1');
		$se2 = I('get.se2');
		$uid = I('get.uid');
		if($se1 == "请选择省份"){//只选分公司
			
			if($uid == '0'){
				$uid = getcompanyId();
			}
			$information = M('information')->where(array('uid'=>$uid))->count();

		}else if($se1 != "请选择省份" && $uid != "0" && $se2 == "请选择城市"){//只选省和分公司

			$data['uid'] = $uid;
			$data['province'] = $se1;
			// $data['city'] = $se2;
			$information = M('information')->where($data)->count();
		}else if($se1 != "请选择省份" && $uid != "0" && $se2 != "请选择城市"){//省市 分公司
			$data['uid'] = $uid;
			$data['province'] = $se1;
			$data['city'] = $se2;
			$information = M('information')->where($data)->count();
		}else if($se1 != "请选择省份"){
			if($se1 != "请选择省份" && $se2 != "请选择城市" && $uid == 0){//选择省市
				$information = M('information')->where(array('province'=>$se1,'city'=>$se2))->count();
			}else if($se1 != "请选择省份"){//只选省
		    	$information = M('information')->where(array('province'=>$se1))->count();
			}
			
		}
          $users = ceil($information/6);
          
           echo json_encode($users);
	}

	public function client(){
		$fid = I('post.id');
		$val = I('post.val');
		$turnover = I('post.turnover','');
		$db = C('DB_PREFIX');
		$page = I('post.page');
        $limitPage=10;
        $pageCurrt=($page-1)*$limitPage;

		$information = M('information')->where(array('id'=>$fid))->find();



		//听课 turnover
//		$order = M('course_com as c')
//		    	->join($db.'course as m on m.id=c.cid')
//		    	->join($db.'enterprise as e on e.id=m.uid')
//		    	->join($db.'user as u on u.id=m.jid')
//		    	->field('e.Qyname,m.classname,m.place,u.name as uname,c.pnum,m.starttime as time,m.starttime,m.endtime')
//		    	->where(array('c.fid'=>$fid)) // 还应该加上签到
//		    	->select();

		$bill["b.fid"]=$fid;
		$bill["b.gonetype"]=1;

		$billcount = M('bill as b')
			->join($db.'course as m on b.cid = m.id')
			->join($db.'course_com_order as o on b.orderid = o.id')
			->join($db.'user as u on u.id=m.jid')
			->join($db.'enterprise as e on e.id=m.uid')
			->where($bill)
			->group("b.cid")
			->count();

		$coursecount =M('course_com as c')
			->join($db.'course as m on m.id=c.cid')
			->join($db.'enterprise as e on e.id=m.uid')
			->join($db.'user as u on u.id=m.jid')
			->where(array('c.fid'=>$fid)) // 还应该加上签到
			->count();



		//成交

        if($turnover==''){

			M('bill as b')
					->join($db.'course as m on b.cid = m.id')
					->join($db.'course_com_order as o on b.orderid = o.id')
					->join($db.'user as u on u.id=m.jid')
					->join($db.'enterprise as e on e.id=m.uid')
					->field('e.Qyname,m.classname,m.place,u.name as uname,SUM(o.ticket) AS pnum,b.time,m.starttime,m.endtime,m.id as cid,b.fid,1 as turnover')
					->where($bill)
					->group("b.cid")
					->order("time desc")
                    ->limit($pageCurrt,$limitPage)
					->select();

			$billsql=M()->_sql();

			$order =M('course_com as c')
				->join($db.'course as m on m.id=c.cid')
				->join($db.'enterprise as e on e.id=m.uid')
				->join($db.'user as u on u.id=m.jid')
				->field('e.Qyname,m.classname,m.place,u.name as uname,c.pnum,m.starttime as time,m.starttime,m.endtime,m.id as cid,c.fid,0 as turnover')
				->union($billsql)
				->where(array('c.fid'=>$fid)) // 还应该加上签到
				->select();

			$pagecount=$billcount+$coursecount;
		}else{
			if($turnover==1){

				$order = M('bill as b')
						->join($db.'course as m on b.cid = m.id')
						->join($db.'course_com_order as o on b.orderid = o.id')
						->join($db.'user as u on u.id=m.jid')
						->join($db.'enterprise as e on e.id=m.uid')
						->field('e.Qyname,m.classname,m.place,u.name as uname,SUM(o.ticket) AS pnum,b.time,m.starttime,m.endtime,m.id as cid,b.fid,1 as turnover')
						->where($bill)
						->group("b.cid")
						->order("time desc")
					    ->limit($pageCurrt,$limitPage)
						->select();
				$pagecount=$billcount;
			}else if($turnover==0){
				$order =M('course_com as c')
					->join($db.'course as m on m.id=c.cid')
					->join($db.'enterprise as e on e.id=m.uid')
					->join($db.'user as u on u.id=m.jid')
					->field('e.Qyname,m.classname,m.place,u.name as uname,c.pnum,m.starttime as time,m.starttime,m.endtime,m.id as cid,c.fid,0 as turnover')
					->where(array('c.fid'=>$fid)) // 还应该加上签到
					->limit($pageCurrt,$limitPage)
					->select();

				$pagecount=$coursecount;
			}
		}
		$result=array("information"=>$information,"order"=>$order,"pagecount"=>ceil($pagecount/$limitPage));
		echo json_encode($result);

	}

	public function orderxq(){
		$cid=I('post.cid');
		$fid=I('post.fid');
        $orderid=M('bill')->where(array("cid"=>$cid,"fid"=>$fid,'gonetype'=>1))->getField("orderid",true);

        $course_com_order=M('course_com_order')->where(array("id"=>array("in",implode(",",$orderid))))->select();
		
		echo json_encode($course_com_order);

	}



	public function maybe(){
		$val = I('post.val');
		$uid = I('post.uid');
		 if($uid == '0'){
			$ini['name'] = array('like',"%$val%");
		}else{
			$ini['uid'] = $uid;
			$ini['name'] = array('like',"%$val%");
		}
		$ini['name'] = array('like',"%$val%");
		$user = M('information')->where($ini)->order('id desc')->select();
		if(empty($user)){
			if($uid == '0'){
				$result['errcode'] = '0';
				$result['errmsg'] = '客户名不存在';
			}else{
				$result['errcode'] = '1';
				$result['errmsg'] = '分公司没有该客户';
			}
		}else{
			$result['user'] = $user;
		}
		
		// echo '<pre>';print_r($result);die;
		echo json_encode($result);
	}

	public function page1(){
			$val = I('get.val');
			$uid = I('get.uid');
			 if($uid == '0'){
				$ini['name'] = array('like',"%$val%");
			}else{
				$ini['uid'] = $uid;
				$ini['name'] = array('like',"%$val%");
			}
		$ini['name'] = array('like',"%$val%");
        $user = M('information')->where($ini)->count();
        // echo '<pre>';print_r($ini);die;
		$users = ceil($user/6);
		echo json_encode($users);
	}
}