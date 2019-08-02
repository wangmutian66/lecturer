<?php
namespace Home\Controller;
use Think\Controller;
class LeunfoldController extends BaseController
{
	public function index()
	{

		session("nav","fold");
        $type=session("type");
		$enterprise = M('enterprise')->where(array('id'=>getcompanyId()))->select();


		if($type<9){
			$id=session("id");
			$uid=M('user')->where(array("id"=>$id))->getField("uid");
		}else{
			$uid=0;
		}

		$this->assign('uid',$uid);
		$this->assign('enterprise',$enterprise);
		$this->display();
	}

	public function getTeach(){
		$id=I('post.id');
		$user=M('user')->where(array("uid"=>$id,'lectype'=>1))->field("id,name")->select();
        echo json_encode($user);
	}


	public  function infinc()
	{
		$jtime = I('post.jtime');
		$ktime = I('post.ktime');
		$tid = I('post.tid','');
		$jid = I('post.jid');
		$type=session("type");
        $typearray=array("8","7","6","5");

        if($tid==""&&in_array($type,$typearray)){
			$tid=session("id");
		}
        if($tid==""){
			$result["error"]=1;
			echo json_encode($result);
			exit();
		}

		if(empty($jtime) || empty($ktime) )
		{
			$jtime = date('Y-m-d');
			$ktime = date('Y-m').'-01';
		}
		$enterprise = M('enterprise')->where(array('id'=>getcompanyId()))->select();
		
		$user = M('user')->field('id,name,mobile,uid')->where(array('id'=>$tid,'lectype'=>1))->select();

		foreach ($user as $k=>$v)
		{
			$db = C('DB_PREFIX');
			$enterprise = M('enterprise')->where(array('id'=>$v['uid']))->find();

			//----------------------------------新增的九大规划start--------------------------------------
			$edj = round(($enterprise['leafletsnc']/100)*$enterprise['nc'],2);


			$acl['i.profitid'] = $v['id'];
			if(!empty($jtime)&&!empty($ktime)){
			     $acl['r.time'] =array(array('elt',$jtime),array('egt',$ktime));
			}
			
			$course = M('course as c')
				->where($acl)
				->join($db.'course_com as m on c.id=m.cid')
				->join($db.'information as i on i.id = m.fid')
				->join($db.'course_com_order as r on r.comid = m.id')
				->field('r.id as mid,i.profitid as profitid,i.name as name , i.tel as tel, c.classname as classname,r.time as time,ticket,planning,card,friends,disciple,typet,typep,typec,typef,typed,leafletstc,leafletsnc,leafletscc,leafletsfc,leafletsdc')
				->order("r.id desc")
				->select();

			$user[$k]["zkyoushow"]=$this->zkyoushow($v['id'],$enterprise,$course,$jtime,$ktime);
			
			 $aey = 0;
			foreach ($course as $kk=>$vv)
			{


				$pdj = round(($vv['leafletstc']/100)*$enterprise['tc'],2);
				$kdj = round(($vv['leafletscc']/100)*$enterprise['cc'],2);
				$qdj = round(($vv['leafletsfc']/100)*$enterprise['fc'],2);
				$ddj = round(($vv['leafletsdc']/100)*$enterprise['dc'],2);
				$edj = round(($vv['leafletsnc']/100)*$enterprise['nc'],2);
				
				$user[$k]['zmoney']+= $vv['ticket']*$vv['leafletstc']+$vv['planning']*$vv['leafletsnc']+$vv['card']*$vv['leafletscc']+$vv['friends']*$vv['leafletsfc']+$vv['disciple']*$vv['leafletsdc'];
				$user[$k]['zmoneypr'] += $vv['ticket']*$pdj+$vv['planning']*$edj+$vv['card']*$kdj+$vv['friends']*$qdj+$vv['disciple']*$ddj;
				if($vv['typet'] == 2 )
				{
					
					$user[$k]['yf'] += $vv['ticket']*$pdj;
					
				}else
				{
					$user[$k]['wf'] += $vv['ticket']*$pdj;
				}
				
				if($vv['typep'] == 2 )
				{
						
					$user[$k]['yf'] += $vv['planning']*$edj;
						
				}else
				{
					$user[$k]['wf'] += $vv['planning']*$edj;
				}
				
				if($vv['typec'] == 2 )
				{
						
					$user[$k]['yf'] += $vv['card']*$kdj;
						
				}else
				{
					$user[$k]['wf'] += $vv['card']*$kdj;
				}
				if($vv['typef'] == 2 )
				{
						
					$user[$k]['yf'] += $vv['friends']*$qdj;
						
				}else
				{
					$user[$k]['wf'] += $vv['friends']*$qdj;
				}
				
				
				if($vv['typed'] == 2 )
				{
						
					$user[$k]['yf'] += $vv['disciple']*$ddj;
						
				}else
				{
					$user[$k]['wf'] += $vv['disciple']*$ddj;
				}
				
				
			}
			
			
		}
		$act['user'] = $user;
		$act['finatype']=session("finatype");
		echo json_encode($act); 
	}

	public function zkyoushow($id,$enterprise,$course,$jtime='',$ktime='')
	{

		$db = C('DB_PREFIX');
		$acl['i.profitid'] = $id;
		$acl['r.time'] =array(array('elt',$jtime),array('egt',$ktime));
//		$course = M('course as c')
//			->where($acl)
//			->join($db.'course_com as m on c.id=m.cid')
//			->join($db.'information as i on i.id = m.fid')
//			->join($db.'course_com_order as r on r.comid = m.id')
//			->field('i.profitid as profitid,r.id as rid,i.name as name , i.tel as tel, c.classname as classname,r.time as time,ticket,planning,card,friends,disciple,typet,typep,typec,typef,typed,leafletstc,leafletsnc,leafletscc,leafletsfc,leafletsdc')
//			->select();
		//公司金额


//		$pdj = round(($enterprise['leafletstc']/100)*$enterprise['tc'],2);
//		$kdj = round(($enterprise['leafletscc']/100)*$enterprise['cc'],2);
//		$qdj = round(($enterprise['leafletsfc']/100)*$enterprise['fc'],2);
//		$ddj = round(($enterprise['leafletsdc']/100)*$enterprise['dc'],2);

		
		foreach ($course as $k=>$v)
		{
			$course[$k]['Qyname'] =$enterprise['Qyname'];
			
		}

		foreach ($course as $kk=>$vv)
		{
			$pdj = round(($vv['leafletstc']/100)*$enterprise['tc'],2);
			$kdj = round(($vv['leafletscc']/100)*$enterprise['cc'],2);
			$qdj = round(($vv['leafletsfc']/100)*$enterprise['fc'],2);
			$ddj = round(($vv['leafletsdc']/100)*$enterprise['dc'],2);
			$edj = round(($vv['leafletsnc']/100)*$enterprise['nc'],2);



			$course[$kk]['ticketmoney']=$vv['ticket']*$vv['leafletstc'];
			$course[$kk]['planningmoney']=$vv['planning']*$vv['leafletsnc'];
			$course[$kk]['cardmoney']=$vv['card']*$vv['leafletscc'];
			$course[$kk]['friendsmoney']=$vv['friends']*$vv['leafletsfc'];
			$course[$kk]['disciplemoney']=$vv['disciple']*$vv['leafletsdc'];

			$course[$kk]['tc'] = $enterprise['tc'].'%';
			$course[$kk]['nc'] = $enterprise['nc'].'%';
			$course[$kk]['cc'] = $enterprise['cc'].'%';
			$course[$kk]['fc'] = $enterprise['fc'].'%';
			$course[$kk]['dc'] = $enterprise['dc'].'%';

			$course[$kk]['ticketpr']=$vv['ticket']*$pdj;
			$course[$kk]['planningpr']=$vv['planning']*$edj;
			$course[$kk]['cardpr']=$vv['card']*$kdj;
			$course[$kk]['friendspr']=$vv['friends']*$qdj;
			$course[$kk]['disciplepr']=$vv['disciple']*$ddj;
			

		}
	
		return $course;


	}

	
	public function zkyou()
	{
		/* $id = I('post.id');
		$db = C('DB_PREFIX');
		$jtime = I('post.jtime');
		$ktime = I('post.ktime');
		$course = M('course as c')
		->where(array('c.jid'=>$id))
		->join($db.'course_com as m on c.id=m.cid')
		->join($db.'information as i on i.id = m.fid')
		->join($db.'course_com_order as r on r.comid = m.id')
		->field('r.id as rid,i.name as name , i.tel as tel, c.classname as classname,r.time as time,ticket,planning,card,friends,disciple,typet,typep,typec,typef,typed')
		->select();
//		echo M()->_sql();
		 //公司金额
		$user = M('user')->where(array('id'=>$id))->find();
		$enterprise = M('enterprise')->where(array('id'=>$user['uid']))->find();
		$pdj = round(($enterprise['leafletstc']/100)*$enterprise['tc'],2);
		$kdj = round(($enterprise['leafletscc']/100)*$enterprise['cc'],2);
		$qdj = round(($enterprise['leafletsfc']/100)*$enterprise['fc'],2);
		$ddj = round(($enterprise['leafletsdc']/100)*$enterprise['dc'],2);
		//----------------------------------新增的九大规划start--------------------------------------
		$edj = round(($enterprise['leafletsnc']/100)*$enterprise['nc'],2);
		foreach ($course as $k=>$v)
		{
			$course[$k]['Qyname'] =$enterprise['Qyname'];
			
		}
		
		foreach ($course as $kk=>$vv)
		{
			$course[$kk]['ticketmoney']=$vv['ticket']*$enterprise['leafletstc'];
			$course[$kk]['planningmoney']=$vv['planning']*$enterprise['leafletsnc'];
			$course[$kk]['cardmoney']=$vv['card']*$enterprise['leafletscc'];
			$course[$kk]['friendsmoney']=$vv['friends']*$enterprise['leafletsfc'];
			$course[$kk]['disciplemoney']=$vv['disciple']*$enterprise['leafletsdc'];
			
			$course[$kk]['tc'] = $enterprise['tc'].'%';
			$course[$kk]['nc'] = $enterprise['nc'].'%';
			$course[$kk]['cc'] = $enterprise['cc'].'%';
			$course[$kk]['fc'] = $enterprise['fc'].'%';
			$course[$kk]['dc'] = $enterprise['dc'].'%';
			
			$course[$kk]['ticketpr']=$vv['ticket']*$pdj;
			$course[$kk]['planningpr']=$vv['planning']*$edj;
			$course[$kk]['cardpr']=$vv['card']*$kdj;
			$course[$kk]['friendspr']=$vv['friends']*$qdj;
			$course[$kk]['disciplepr']=$vv['disciple']*$ddj;
			
		}
	
		echo json_encode($course); */
		
		
	}
	
	
	public function  neihtml()
	{
		$ktime = I('post.ktime');
		$jtime = I('post.jtime');
		$s1 = I('post.s1');
		$p = I('post.p');
		$yy = I('post.yy');
		
		if(!empty($yy))
		{
			$ini['name'] = array('like',"%$yy%");
		}
		$limit = 10;
		if(!$p)
		{
			$p = 1;
		}
		$first = ($p-1) * $limit;
		
		if($s1 == 0)
		{
			$ini['uid'] = getcompanyId();
			$ini['lectype'] = 1;
			$user=M('user')->where($ini)->limit($first,$limit)->select();

			$count=M('user')->where($ini)->field("id,name")->count();
		}else
		{	
			
			$ini['uid'] = $s1;
			$ini['lectype'] = 1;
			$user=M('user')->where($ini)->field("id,name")->limit($first,$limit)->select();
			$count=M('user')->where($ini)->field("id,name")->count();
		}
		$act['count'] = ceil($count/$limit);
		$act['user'] = $user;
		echo json_encode($act);
		
		
	}
	
	function savebin()
	{
		$act = I('post.act');
		$nid = I('post.nid');
		$nid = substr($nid,0,-1);
		$nid = explode(',',$nid);
		foreach ($nid as $k=>$v)
		{
			$ini[] = explode(':',$v);
		}
		if($act == 1)
		{
			foreach ($ini as $kk=>$vv)
			{
				if($vv[1] == 1)
				{
					$azk['typet']= 0;

				}else if($vv[1] == 2)
				{
					$azk['typep']= 0;

				}else if($vv[1] == 3)
				{
					$azk['typec']= 0;

				}else if($vv[1] == 4)
				{
					$azk['typef']= 0;

				}else if($vv[1] == 5)
				{
					$azk['typed']= 0;

				}
				$atp['id']=$vv[0];
				$course_com_order = M('course_com_order')->where($atp)->save($azk);

					
			}

		}else if($act == 2)
		{
			
			foreach ($ini as $kk=>$vv)
			{
				if($vv[1] == 1)
				{
					$azk['typet']= 1;
			
				}else if($vv[1] == 2)
				{
					$azk['typep']= 1;
			
				}else if($vv[1] == 3)
				{
					$azk['typec']= 1;
			
				}else if($vv[1] == 4)
				{
					$azk['typef']= 1;
			
				}else if($vv[1] == 5)
				{
					$azk['typed']= 1;
			
				}
				$atp['id']=$vv[0];
				$course_com_order = M('course_com_order')->where($atp)->save($azk);
						
		    }
		}else
		{
			foreach ($ini as $kk=>$vv)
			{
				if($vv[1] == 1)
				{
					$azk['typet']= 2;
						
				}else if($vv[1] == 2)
				{
					$azk['typep']= 2;
						
				}else if($vv[1] == 3)
				{
					$azk['typec']= 2;
						
				}else if($vv[1] == 4)
				{
					$azk['typef']= 2;
						
				}else if($vv[1] == 5)
				{
					$azk['typed']= 2;
						
				}
				$atp['id']=$vv[0];
				$course_com_order = M('course_com_order')->where($atp)->save($azk);
			
		}
	}

	if($course_com_order === false)
	{	
		$data['data'] = 2;

	
	}else
	{
		$data['data'] = 1;
	}
	echo json_encode($data);
	
	}



	//订单详情
    public function xqing(){
		$orderid=I('post.orderid');
		$prefix=C('DB_PREFIX');
		//$course_com_order=M('course_com_order')->where(array("id"=>$orderid))->find();
        //$course_com=M('course_com')->where(array("id"=>$course_com_order["comid"]))->find();

		$courseOrder=M('course_com_order as o')
						->join($prefix."course_com as c on c.id=o.comid")
						->join($prefix."course as e on e.id=c.cid")
						->join($prefix."enterprise as u on e.uid=u.id")
		            	->join($prefix."information as f on c.fid=f.id")
						->join($prefix."user as r on r.id=e.jid")
			            ->join($prefix."salesman as s on o.rid=s.id")
			            ->field("u.Qyname,e.classname,e.place,r.name as jname,c.pnum,f.name as fname,o.time as otime,o.ticket,o.planning,o.card,o.friends,o.disciple,s.name as sname")
						->where(array("o.id"=>$orderid))
			            ->find();

        echo json_encode($courseOrder);

	}

	
}