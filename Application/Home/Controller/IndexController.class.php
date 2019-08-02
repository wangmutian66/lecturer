<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends BaseController{
	private $_currpage=6;
	public function index(){
		//$this->redirect('Home/Login/login');
	    session("nav","index");
		$this->display();
	}
	
	public function chunfet()
	{
		$id = getcompanyId();
	    $page=I('post.page',1);
		$db = C('DB_PREFIX');
		$acid = I('post.acid');//接到的是下拉选项
		$name = I('post.name');//接到文本框输入的内容
		$ini['uid'] =$id;
		if($this->isMobile($name)){
			$field="tel";
			$ini["tel"] = $name;
		}else{
			$field="name";
			$ini["name"] = array('like',"%$name%");
		}
		$person=array();
		switch ($acid){
			case 1:
				$information = M('information')->where($ini)->find();//79
				$rid=array("neq","0");
				$fid=$information["id"];
				$person=array("information"=>$information["name"]);
				break;
			case 2:
				if($this->isMobile($name)){
					$field="mobile";
					$ini["mobile"] = $name;
				}
				$salesman=M('salesman')->where($ini)->find();
				
				$rid=$salesman["id"];
				if(empty($rid)){
					$arr["error"]=1;
					$arr["msg"]="没有相关销售人员";
					$arr["countpage"]=0; //该所在的全部课程
					echo json_encode($arr);
					exit();
				}
                
				$information=M('information')->where(array("rid"=>$rid))->find();

				$fid=$information["id"];
				$person=array("information"=>$information["name"],"salesman"=>$salesman["name"]);
				break;
		}

		//1.课程  财务
		//2.crm
		if(empty($fid)){
			$arr["error"]=1;
			$arr["msg"]="没有课程消息";
			$arr["countpage"]=0; //该所在的全部课程
			echo json_encode($arr);
			exit();
		}
		
		$course_com = M('course_com')->where(array('fid'=>$fid))->getField("cid",true);//54

		$pagelimit=0;
		$course=null;
        if(!empty($course_com)){
			$coursecount = M('course as c')
				->join($db.'enterprise as e on e.id=c.uid','left')
				->join($db.'user as u on c.jid=u.id','left')
				->field('c.id as id,c.starttime as starttime,c.endtime as endtime,c.classname as classname,c.place as place,u.name as name,e.Qyname as Qyname,c.conduct as conduct')
				->where(array('c.uid'=>$id,"c.id"=>array("in",implode(",",$course_com))))
				->count();
			$pagelimit=ceil($coursecount/$this->_currpage);

			$course = M('course as c')
				->join($db.'enterprise as e on e.id=c.uid','left')
				->join($db.'user as u on c.jid=u.id','left')
				->field('c.id as id,c.starttime as starttime,c.endtime as endtime,c.classname as classname,c.place as place,u.name as name,e.Qyname as Qyname,c.conduct as conduct')
				->where(array('c.uid'=>$id,"c.id"=>array("in",implode(",",$course_com))))
				->limit($page-1,$this->_currpage)
				->select();
			foreach ($course as $key => $value) {
				$number = M('course_com')->where(array("cid"=>$value["id"]))->group("cid")->field("sum(pnum) as number")->find();
				$course[$key]["number"]=$number["number"]==null?0:$number["number"];
				$comid=M('course_com')->where(array("cid"=>$value["id"],"fid"=>$fid))->getField("id",true);
				$course_com_order=M('course_com_order')->where(array("comid"=>array("in",implode(",",$comid)),"rid"=>$rid))->select();

				foreach ($course_com_order as $k=>$row){
					$course_com_order[$k]["salesmanname"]=M('salesman')->where(array("id"=>$row["rid"]))->getField("name");
				}
				$course[$key]["order"]=$course_com_order;
				//$course[$key]["qyname"]=M('enterprise')->where(array("id"=>$row["uid"]))->getField("Qyname");
			}
		}


		$arr=array();
		$arr["person"]=$person;
		$arr["course"]=$course; //该所在的全部课程
		$arr["countpage"]=$pagelimit; //总页数
		echo json_encode($arr);
		exit();
	}


	public function setsail(){
		$id = getcompanyId();
		$page=I('post.page',1);
		$db = C('DB_PREFIX');
		$acid = I('post.acid');//接到的是下拉选项
		$name = I('post.name');//接到文本框输入的内容
		$ini['uid'] =$id;
		//$ini['name'] = array('like',"%$name%");
		if($this->isMobile($name)){
			$field="tel";
			$ini["tel"] = $name;
		}else{
			$field="name";
			$ini["name"] = array('like',"%$name%");
		}
		switch ($acid){
			case 1:
				$information = M('information')->where($ini)->find();//79
				$fid=array();
				$rid=array();
                if(!empty($information)){
					$fid=array($information["id"]);
					$rid=array($information["rid"]);
				}
				break;
			case 2:
				if($this->isMobile($name)){
					$field="mobile";
					$ini["mobile"] = $name;
				}
				$rid=M('salesman')->where($ini)->getField("id",true);
				if(empty($rid)){
					$arr["error"]=1;
					$arr["msg"]="没有相关销售人员";
					$arr["countpage"]=0; //该所在的全部课程
					echo json_encode($arr);
					exit();
				}
				$fid=M('information')->where(array("rid"=>array("in",implode(",",$rid))))->getField("id",true);

				break;
		}
		$arr["error"]=0;

		if(empty($fid)){
			$arr["error"]=1;
			$arr["msg"]="没有可用的数据";
			echo json_encode($arr);
			exit();
		}
		$bill=M('bill')->where(array('uid'=>$id,"fid"=>array("in",implode(",",$fid))))->order("time desc,id desc")->limit($page-1,$this->_currpage)->select();
		$billcount=M('bill')->where(array('uid'=>$id,"fid"=>array("in",implode(",",$fid))))->count();

		$pagelimit=ceil($billcount/$this->_currpage);
		foreach ($bill as $k=>$row){
			$bill[$k]["cname"]=M('course')->where(array("id"=>$row["cid"]))->getField("classname");
			$bill[$k]["fname"]=M('information')->where(array("id"=>$row["fid"]))->getField("name");
			$where['id'] = $row["orderid"];
			//$where['rid'] = array('in',$rid);
			$order=M('course_com_order')->where($where)->find();
			$bill[$k]["corderisset"]=empty($order)?0:1;
			unset($where);
			$bill[$k]["corder"]=$order["time"]."  小票({$order["ticket"]})  九大规划({$order["planning"]}) 卡({$order["card"]}) 朋友圈({$order["friends"]}) 弟子学员({$order["disciple"]})";
			$rid=M('information')->where(array("id"=>$row["fid"]))->getField("rid");
			$bill[$k]["rname"]=M('salesman')->where(array("id"=>$rid))->getField("name");
			$bill[$k]["qyname"]=M('enterprise')->where(array("id"=>$row["uid"]))->getField("Qyname");
		}
		$arr["bill"]=$bill;
		$arr["pagelimit"]=$pagelimit;
        echo json_encode($arr);
	}

    //跟踪记录
	public function followinfo(){
		$id = getcompanyId();
		$db = C('DB_PREFIX');
		$acid = I('post.acid');//接到的是下拉选项
		$name = I('post.name');//接到文本框输入的内容
		$page=I('post.page',1);

		$ini['uid'] =$id;

		if($this->isMobile($name)){
			$field="tel";
			$ini["tel"] = $name;
		}else{
			$field="name";
			$ini["name"] = array('like',"%$name%");
		}



		switch ($acid){
			case 1:
				$fid = M('information')->where($ini)->getField("id",true);//79
				break;
			case 2:
				if($this->isMobile($name)){
					$field="mobile";
					$ini["mobile"] = $name;
				}
				$rid=M('salesman')->where($ini)->getField("id",true);
				if(empty($rid)){
					$arr["error"]=1;
					$arr["msg"]="没有相关销售人员";
					$arr["countpage"]=0; //该所在的全部课程
					echo json_encode($arr);
					exit();
				}

				$fid=M('information')->where(array("rid"=>array("in",implode(",",$rid))))->getField("id",true);
				break;
		}
		if(empty($fid)){
			$arr["error"]=1;
			$arr["msg"]="没有可用的数据";
			echo json_encode($arr);
			exit();
		}
		$infofindcount=M('information')->where(array("id"=>array("in",implode(",",$fid))))->count();
		$pagelimit=ceil(($infofindcount)/$this->_currpage);
		$infofind=M('information')->where(array("id"=>array("in",implode(",",$fid))))->limit($page-1,$this->_currpage)->select();
		$arr["error"]=0;


		foreach ($infofind as $k=>$item) {
			$infofind[$k]["rname"]=M('salesman')->where(array("id"=>$item["rid"]))->getField("name");
			$infofind[$k]["followinfocontent"]=$this->followinfocontent($item["id"]);

        }

        $arr["infofind"]=$infofind;
		$arr["pagelimit"]=$pagelimit;
		echo json_encode($arr);
	}



	private function followinfocontent($fid){
		$transferLog=M('transfer_log')->where(array("fid"=>$fid))->select();

		$yrid=array();
		$nrid=array();
		foreach ($transferLog as $row){
			$yrid[]=$row["yrid"];
			$nrid[]=$row["nrid"];
		}
		$where=implode(",",$yrid);
		if(!empty($nrid)){
			$where.=",".implode(",",$nrid);
		}
		$salesman=M('salesman')->where(array("id"=>array("in",$where)))->select();
		$salArray=array();
		foreach ($salesman as $row){
			$salArray[$row["id"]]=$row["name"];
		}


		foreach ($transferLog as $k=>$row){
			$transferLog[$k]["yname"]=($salArray[$row["yrid"]]==null)?"":$salArray[$row["yrid"]];
			$transferLog[$k]["nname"]=($salArray[$row["nrid"]]==null)?"":$salArray[$row["nrid"]];
            $moveperson=explode("|",$row["moveperson"]);

			$transferLog[$k]["username"]=$moveperson[1];
			$transferLog[$k]["role"]=$this->getrole($moveperson[0],$moveperson[2]);
		}
    
		return $transferLog;
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
	
	function searchword(){
		$pagecurrt=5;  //每页显示5条
		$page=I('post.page',1);
		$id=getcompanyId();
		$value=I('post.value');
		$acid=I('post.acid');
		$arr["uid"]=$id;


		switch ($acid){
			case 1:
				if($this->isMobile($value)){
					$where="tel";
					$field="tel as name,id";
				}else{
					$where="name";
					$field="name,id";
				}
				$arr[$where]=array("like","%$value%");

				$info_name=M('information')->where($arr)->field($field)->limit((($page-1)*$pagecurrt),$pagecurrt)->select();
         

				$info_name_count=M('information')->where($arr)->count();
				break;
			case 2:
				if($this->isMobile($value)){
					$where="mobile as name,id";
					$field="mobile";
				}else{
					$where="name,id";
					$field="name";
				}
				$arr[$field]=array("like","%$value%");
				$info_name=M('salesman')->where($arr)->field($where)->limit((($page-1)*$pagecurrt),$pagecurrt)->select();
				$info_name_count=M('salesman')->where($arr)->count();
				break;
		}
        $countpage=ceil($info_name_count/$pagecurrt);
		echo json_encode(array("info_name"=>$info_name,"info_count"=>$countpage));

	}

	public function isMobile($value){

		if(preg_match("/^1[34578]{1}/",$value)){
			return true;
		}else{
			return false;
		}

	}

}