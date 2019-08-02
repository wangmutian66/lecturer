<?php
namespace Home\Controller;
use Think\Controller;

class CourseController extends BaseController {

	public function index(){

		session("nav","course");
		$type=session("type");
		$id = getcompanyId();
		$comid=0;
        if($type!=9||$type!=10){
			$comid=$id[1];
		}
		
		$enterprise = M('enterprise')->where(array('id'=>$id))->select();

		$b1 = M('user')->where(array('uid'=>$id,'lectype'=>1))->field('id,name')->select();
		$salesman=M("salesman")->where(array("uid"=>$id))->field("id,name")->select();
		// echo '<pre>';print_r($b1);die;
		$this->assign('salesman',$salesman);
		$this->assign('comid',$comid);
		$this->assign('a1',$enterprise);//大区总裁下有多少分公司
		$this->assign('b1',$b1);//分公司下有多少讲师
		$this->display();
	}
	
	public function showchek(){
	    $id=I('post.id'); //公司id
		$aid=M('enterprise')->where(array("id"=>$id))->getField("aid");
		$enterid=M('enterprise')->where(array("aid"=>$aid))->getField("id",true);
	    if(empty($enterid)){
            echo json_encode(array());
			exit();
		}
	    $lectype=M('user')->where(array('uid'=>array("in",implode(",",$enterid)),'lectype'=>1))->field('id,name')->select();
	    echo json_encode($lectype);
	}
	
    //起航课程搜索 searov
    public function searove(){

    	$db = C('DB_PREFIX');
    	$ytime = I('post.ytime');
    	$ntime = I('post.ntime');   	
    	$fields=I('post.selec','');//字段名称下拉菜单选项值
    	$punt=I('post.punt','');//文本框里的内容
    	$con=I('post.con','');//已进行   未进行
    	//$p = I('post.page');
    	$id = getcompanyId();
		$limit = 6;
		$p= I('post.p',1);
		$first = ($p-1) * $limit;

		$map['c.uid'] = $id;
		//$map['lectype']=1;
		$map['starttime']  = array('egt',$ytime); 
        $map['endtime']  = array('elt',$ntime);
        if($fields!=''){
            $map["$fields"]  = array('like',"%$punt%");
        }

        if($con!=''){
        	$map["conduct"]=$con;
        }
		$course = M('course as c')
				->join($db.'enterprise as e on e.id=c.uid','left')
				->join($db.'user as u on c.jid=u.id','left')
				->field('c.id as id,c.uid as uid,c.starttime as starttime,c.endtime as endtime,c.classname as classname,c.place as place,u.name as name,e.Qyname as Qyname,c.conduct as conduct')
				->where($map)
			    ->order("id desc")
				->limit($first,$limit)
				->select();
      
		foreach ($course as $key => $value) {
			$number = M('course_com')->where(array("cid"=>$value["id"]))->group("cid")->field("sum(pnum) as number")->find();
			$course[$key]["number"]=$number["number"]==null?0:$number["number"];
		}

		$studentCount = M('course as c')
				->join($db.'enterprise as e on e.id=c.uid','left')
			    ->join($db.'user as u on c.jid=u.id','left')
				->where($map)
				->count();
		$ini["pagecount"]=ceil($studentCount/$limit);
		
		$ini['error'] = 1;
		$ini['data'] = $course;

		echo json_encode($ini);

    }
    public function search(){
		$selec = I('post.selec','');//请选择下拉菜单里的选项
		$punt = I('post.punt','');//搜索文本框里的值
		$con = I('post.con','');//已进行   未进行 选择值
    	$id = getcompanyId();
    	$limit = 6;
		$p= I('post.p',1);
		$first = ($p-1) * $limit;
		$map['c.uid'] = $id;
		if($selec){
		    
		}
		

	}
    public function classlist(){

		$id = getcompanyId();
		
		$p = I('post.page');
        $limit = 6;
        $first = ($p-1) * $limit;


//		$course = M('course as c')->where(array('c.uid'=>$id))->order('c.id desc')->limit($first,$limit)->select();
//
//		foreach ($course as $k => $v) {
//			$course[$k]["Qyname"]=M('enterprise')->where(array('id'=>$v['uid']))->getField("Qyname");//分公司名字
//			$course[$k]["name"]=M('user')->where(array('id'=>$v['jid']))->getField("name");//分公司名字
//			$number=M('course_com')->where(array("cid"=>$v["id"]))->group("cid")->field("sum(pnum) as number")->find();
//			$course[$k]["number"]=$number["number"]==null?0:$number["number"];
//		}
		$map['c.uid'] = $id;
		$map['starttime']  = array('egt',date("Y-m-01"));
		$map['endtime']  = array('elt',date("Y-m-d"));
		$db = C('DB_PREFIX');
		$course = M('course as c')
			->join($db.'enterprise as e on e.id=c.uid','left')
			->join($db.'user as u on c.jid=u.id','left')
			->field('c.id as id,c.uid as uid,c.starttime as starttime,c.endtime as endtime,c.classname as classname,c.place as place,u.name as name,e.Qyname as Qyname,c.conduct as conduct,u.id as jid')
			->where($map)
			->order("id desc")
			->limit($first,$limit)
			->select();

		foreach ($course as $key => $value) {
			$number = M('course_com')->where(array("cid"=>$value["id"]))->group("cid")->field("sum(pnum) as number")->find();
			$numbersign = M('course_com')->where(array("cid"=>$value["id"],"issignin"=>1))->group("cid")->field("sum(pnum) as number")->find();
		    $course[$key]["number"]=$number["number"]==null?0:$number["number"];
			$course[$key]["numbersign"]=$numbersign["number"]==null?0:$numbersign["number"];
		}


		echo json_encode($course);
	}
    
	//修改参与人数
	public function editnum(){
	    $id=I('post.id');
	    $pnum=I('post.pnum');
	    M('course_com')->where(array('id'=>$id))->save(array("pnum"=>$pnum));
	    echo json_encode(1);
	}

	//----//
	public function editlist(){
	    $id=I('post.id');
	    $courfind=M('course')->where(array("id"=>$id))->find();

		$aid=M('enterprise')->where(array("id"=>$courfind["uid"]))->getField("aid");
		$enterid=M('enterprise')->where(array("aid"=>$aid))->getField("id",true);
		if(empty($enterid)){
			echo json_encode(array());
			exit();
		}
		$lectype=M('user')->where(array('uid'=>array("in",implode(",",$enterid)),'lectype'=>1))->field('id,name')->select();
	    echo json_encode(array("courfind"=>$courfind,"lectype"=>$lectype));
	}
	
	public function savecourse(){
	    $id=I('post.id');
	    $classname=I('post.classname');
	    $jid=I('post.jid');
	    $uid=I('post.uid');
	    $place=I('post.place');
	    $starttime=I('post.starttime');
	    $endtime=I('post.endtime');
	    $conduct=I('post.conduct');
	   
	    $arr=array();
	    $arr["classname"]=$classname;
	    $arr["jid"]=$jid;
	    $arr["uid"]=$uid;
	    $arr["place"]=$place;
	    $arr["starttime"]=$starttime;
	    $arr["endtime"]=$endtime;
	    $arr["conduct"]=$conduct;
	    
	    $result=M('course')->where(array('id'=>$id))->save($arr);
	   
	    if($result){
	        $res["error"]=0;
	        $res["msg"]="修改成功";
	    }else{
	        $res["error"]=1;
	        $res["msg"]="修改失败";
	    }
	    echo json_encode($res);	    
	}
	
	//分页
	 public function page1()//分公司下的讲师分页
    {
        
//        $id = session("uid");
        $id=getcompanyId();

        //$asd = M('course')->where(array('uid'=>$id))->count();
		$map['c.uid'] = $id;
		$map['starttime']  = array('egt',date("Y-m-01"));
		$map['endtime']  = array('elt',date("Y-m-d"));
		$db = C('DB_PREFIX');
		$asd = M('course as c')
			->join($db.'enterprise as e on e.id=c.uid','left')
			->join($db.'user as u on c.jid=u.id','left')
			->field('c.id as id,c.uid as uid,c.starttime as starttime,c.endtime as endtime,c.classname as classname,c.place as place,u.name as name,e.Qyname as Qyname,c.conduct as conduct')
			->where($map)
			->count();
        $enterprise1 = ceil($asd/6);
        echo json_encode($enterprise1);
    }

    public function page2(){

		$uid=I('post.uid');
		$yy=I('post.yy','');
		$search=I('post.search','');
		if($search!=""){
			$information = M('information as f')->where(array('f.uid'=>$uid,'deletemark'=>0,"f.name"=>array("like","%$search%")))->count();
		}else if($yy!=''){
			$information = M('information as f')->where(array('f.uid'=>$uid,'deletemark'=>0,"f.name"=>array("like","%$yy%")))->count();
		}else{
			$information = M('information as f')->where(array('f.uid'=>$uid,'deletemark'=>0))->count();
		}

		$enterprise = ceil($information/6);
        echo json_encode($enterprise);

    }
	public function addzjl(){
		$info = I('post.');
		$ini['classname'] = $info['kcb'];
	    $ini['jid'] = $info['jiangshi'];
	    $ini['uid'] = $info['fengongsi'];
		$ini['place'] = $info['didian'];
		$ini['number'] = $info['renshu'];
		$ini['starttime'] = $info['stime'];
		$ini['endtime'] = $info['etime'];
		$ini['conduct'] = $info['conduct'];
		
		
		$course = M('course')->add($ini);
		if($course!==false){
			 echo json_encode(1);
			 
		}else{
			 echo json_encode(2);
		}
	}
	//添加成交信息
	public function shadd(){
		$info = I('post.');

		$ini['time'] = $info['timem'];
		$ini['rid'] = $info['said'];
	    $ini['ticket'] = $info['xiaopiao'];
	    $ini['card'] = $info['kahui'];
		$ini['friends'] = $info['pengyou'];
		$ini['planning'] = $info['guihua'];
		$ini['disciple'] = $info['dizi'];
		$ini['comid'] = $info['comid'];

        $uid=$info['uid'];
        $enterprise=M('enterprise')->where(array("id"=>$uid))->find();
		$ini['leafletstc'] = $enterprise['leafletstc'];
		$ini['leafletscc'] = $enterprise['leafletscc'];
		$ini['leafletsfc'] = $enterprise['leafletsfc'];
		$ini['leafletsdc'] = $enterprise['leafletsdc'];
		$ini['leafletsnc'] = $enterprise['leafletsnc'];


		$user = M('information')->where(array('id'=>$info['kid']))->find();
		if($user['profitid'] == 0){
			M('information')->where(array('id'=>$info['kid']))->save(array('profitid'=>$info["jid"]));
		}
		
		$course_com_order = M('course_com_order')->add($ini);

		$fid=M('course_com')->where(array("id"=>$info['comid']))->getField("fid");
		$info_rid=M("information")->where(array("id"=>$fid))->getField("rid");

		//判断添加移交记录 said    &&$info_rid==0
		if($info_rid!=$ini['rid']){
            $arr=array();
			$arr["yrid"]=$info_rid;
			$arr["nrid"]=$ini['rid'];
			$arr["fid"]=$fid;
			$arr["ctime"]=date("Y-m-d H:i:s");
			$arr["moveperson"]=getUserMessge();
			$arr["status"]=4;
			M('transfer_log')->add($arr);
		}

		//修改成交人
		M("information")->where(array("id"=>$fid))->save(array("status"=>1,"rid"=>$ini['rid'])); //,"state"=>4
	
		//如果只是小票 要修改小票剩余数量
		//如果是只有九大规划要添加九大规划倒计时

        $buystatus=$this->getBuyStatus($ini);
        //---
        switch ($buystatus){
			case 0:
				$information= M('information');
				$information->query("update ".C('DB_PREFIX')."information  set surplus=surplus+1 where id={$fid}  ");
                break;
			case 1:
				$countdown=date("Y-m-d");
				M('information')->where(array("id"=>$fid))->save(array("countdown"=>$countdown));
				break;
			case 2:
				M('information')->where(array("id"=>$fid))->save(array("surplus"=>0,"countdown"=>"0000-00-00"));
				break;
		}
		
		if($course_com_order!==false){
		     
			 echo json_encode(1);
		}else{
			 echo json_encode(2);
		}
	}

	//判断购买情况 0 只购买小票 1 购买九大规划
    public function getBuyStatus($ini){
        $status=0;
        if($ini['planning']>0){
			$status=1;
		}
		//card friends disciple
        if($ini['card']>0||$ini['friends']>0||$ini['disciple']>0){
			$status=2;
		}
		return $status;
	}


	//成交信息展开
	public function ed(){
		$id = I('post.id');
		$course_com_order = M('course_com_order')->where(array('comid'=>$id))->select();
		foreach ($course_com_order as $k=>$row){
			$course_com_order[$k]["rname"]=M('salesman')->where(array("id"=>$row["rid"]))->getField("name");
		}
		if($course_com_order){
			$result['errcode'] = 1;
			$result['dis'] = $course_com_order;
		}
		else
		{
			$result['errcode'] = 0;
           
		}
		// echo '<pre>';
		// print_r($result);die;
		echo json_encode($result);
	}
	//删除
	public function del(){
		$id = I('post.id');
		
		$course_com=M('course_com')->where(array('cid'=>$id))->getField("id",true);
		$course_com_order = M('course_com_order')->where(array('comid'=>implode(",", $course_com)))->delete();
		
		M('course_com')->where(array('cid'=>$id))->delete();
		
		$course=M('course')->where(array('id'=>$id))->delete();
		if($course){
			$result['errcode'] = 1;
            $result['errmsg'] ='删除成功';
		}else{
			$result['errcode'] = 0;
            $result['errmsg'] ='删除失败';
		}
		echo json_encode($result);
	}
	public function edit(){
		//$uid = session("uid");
        //$uid=getcompanyId();
		$uid=I('post.uid');

		$id = I('post.id');//当前企业下的讲师
		$yy=I('post.yy','');
       
        
		//$search=' 1=1 ';
        if($yy!=''){
           //$search="f.name like '%$yy%'"  ;
        }
        if(I('post.typey') == 1){
			//->where($search)
			$information = M('information as f')->where(array('f.uid'=>$uid,'deletemark'=>0,'f.name'=>array("like","%$yy%")))->order('f.id desc')->select();

		}else{
		    $p = I('post.page');
	        $limit = 6;
	        $first = ($p-1) * $limit;
			//->where($search)
			$information = M('information as f')->where(array('f.uid'=>$uid,'deletemark'=>0))->limit($first,$limit)->order('f.id desc')->select();
		}
        //->join(C('DB_PREFIX')."course_com as c on f.id=c.fid")
		

        foreach ($information as $k=>$row){
            $pnum=M('course_com')->where(array('cid'=>$id,'fid'=>$row["id"]))->find();
            
            if(empty($pnum)){
                $information[$k]["pnum"]=0;
                $information[$k]["checked"]=0;
            }else{
                $information[$k]["pnum"]=$pnum["pnum"];
                $information[$k]["checked"]=1;
            }
        }
        // echo '<pre>';print_r($information);die;
		echo json_encode($information);
	}
	//-------------------------
	//修改course表里
	public function qweadd(){
		$uid = session("uid");
        $uid=getcompanyId();
        $id = I('post.id');
		$nameq = I('post.nameq');
		$s = I('post.s');
		$newstr = substr($s,0,strlen($s)-1);
		$newarray=explode("|", $newstr);


		//更改人数
		foreach ($newarray as $row){
		    $exrow=explode(",", $row);
		    $fid=$exrow[0];
		    $pnum=$exrow[1];
		    $courselist=M('course_com')->where(array('cid'=>$id,'fid'=>$fid))->find();
			$information= M('information');
			$information->query("update ".C('DB_PREFIX')."information  set surplus=surplus+1 where id={$fid}  ");
			//连续两次
			$row=M('information')->where(array("id"=>$fid,"surplus"=>array("gt","1")))->find();
			if(!empty($row)){
				M('information')->where(array("id"=>$fid,"surplus"=>array("gt","1")))->save(array("rid"=>0));
				$array=array();
				$array["yrid"]=$row["rid"];
				$array["nrid"]=0;
				$array["fid"]=$fid;
				$array["ctime"]=date("Y-m-d H:i:s");
				$array["status"]=3;
				$array["reason"]="连续两次参加opp都没有成交客户";
				$array["moveperson"]=getUserMessge();
				M('transfer_log')->add($array);
			}
		    if(!$courselist){
		        $course = M('course_com')->add(array('cid'=>$id,'fid'=>$fid,'pnum'=>$pnum));
		    }else{
		        $course = M('course_com')->where(array('cid'=>$id,'fid'=>$fid))->save(array('pnum'=>$pnum));
		    }
		}
	    
	    if($course!==false){
			 echo json_encode(1);
		}else{
			 echo json_encode(2);
		}
	}

	
	public function show(){
	    $id=I('post.id');
        $uid=getcompanyId();
	    $courselist=M('course_com')->where(array('cid'=>$id))->getField("fid",true);
        
	    if($courselist){
	    	$fid=implode(",", $courselist);
		    $inforlist=M('information')->where("id in ($fid)")->where(array("uid"=>$uid))->select();
		    foreach($inforlist as $k=>$i){
		    
		    	$courseCom=M('course_com')->where(array('cid'=>$id,'fid'=>$i["id"]))->find();
		    	$inforlist[$k]["comid"]=$courseCom["id"];
		    	$inforlist[$k]["pnum"]=$courseCom["pnum"];
				$inforlist[$k]["issignin"]=$courseCom["issignin"];
		    }
		    
		    $result['errcode'] = 1;
		    $result['dis'] = $inforlist;
	    }else{
	        $result['errcode'] = 0;
	    }

	    echo json_encode($result);
	}
	//展开删除
	public function dele(){
		 $id = I('post.id');
		 $courseCom = M('course_com')->where(array('id'=>$id))->delete();
		 M('course_com_order')->where(array('comid'=>$id))->delete();
		 if($courseCom){
            $arr["errcode"]=0;
            $arr["errmsg"]="删除成功";
         }else{
            $arr["errcode"]=1;
            $arr["errmsg"]="删除失败";
         }  
         echo json_encode($arr);

	}
	//成交信息展开删除
	public function qdel(){
		$id = I('post.id');
		// echo $id;die;
		//M('course_com')->where(array('id'=>$id))->delete();
		// $course_com_order = M('course_com_order')->where(array('comid'=>$id))->delete();
		$course_com_order = M('course_com_order')->where(array('id'=>$id))->delete();
		
		if($course_com_order){
            $arr["errcode"]=0;
            $arr["errmsg"]="删除成功";
        }else{
            $arr["errcode"]=1;
            $arr["errmsg"]="删除失败";
        }  
        echo json_encode($arr);
	}
	//成交信息展开编辑
	public function bjedit1(){
		$post=I('post.');
		$ini['time'] = $post['timem1'];
		$ini['ticket'] = $post['xiaopiao1'];
		$ini['card'] = $post['kahui1'];
		$ini['friends'] = $post['pengyou1'];
		$ini['planning'] = $post['dizi1'];
		$ini['disciple'] = $post['guihua1'];
		$ini['rid'] = $post['rid'];
		if(isset($post['leafletstc'])){
			$ini['leafletstc'] = $post['leafletstc'];
			$ini['leafletsnc'] = $post['leafletsnc'];
			$ini['leafletstc'] = $post['leafletstc'];
			$ini['leafletstc'] = $post['leafletstc'];
			$ini['leafletstc'] = $post['leafletstc'];
		}
        $id=$post['id'];

        $result=M('course_com_order as c')->where(array('c.id'=>$id))->save($ini);
        if($result){
            $arr["errcode"]=0;
            $arr["errmsg"]="修改成功";
        }else{
            $arr["errcode"]=1;
            $arr["errmsg"]="修改失败";
        }  
        echo json_encode($arr);
	}
	public function dit(){
		$id = I('post.id');
		$uid=I('post.uid');
		$course_com_order = M('course_com_order')->where(array('id'=>$id))->select();
		$salesman=M('salesman')->where(array("uid"=>$uid))->select();
        $finatype=session("finatype");

		if($course_com_order){
			$result['errcode'] = 1;
			$result['dis'] = $course_com_order;
			$result['salesman'] = $salesman;
			$result['finatype'] = $finatype;
		}
		else
		{
			$result['errcode'] = 0;
		}

		echo json_encode($result);
	}

	public function getsh(){
		$uid=I('post.uid');
		$salesman=M('salesman')->where(array("uid"=>$uid))->select();
        echo json_encode($salesman);
	}

	public function adf(){
		$cid=I('post.cid');
		$fid=I('post.fid');
		$comid=M('course_com')->where(array("cid"=>$cid,"fid"=>$fid))->getField("id");
		M('course_com_order')->where(array("comid"=>$comid))->delete();
		M('course_com')->where(array("cid"=>$cid,"fid"=>$fid))->delete();
	}
	public function savenum()
	{
		$pnum=I('post.pnum');
		$count=I('post.cont');
		$comid=I('post.comid');
        //M('course_com')->where(array("id"=>$comid))->setField("pnum",$count);
		M('course_com')->where(array("id"=>$comid))->save(array("pnum"=>$count,"issignin"=>1));
		$information = M('information')->where(array('id'=>I('post.id')))->getField('remaining');

		$num = $information - $count;

		$snum = M('information')->where(array('id'=>I('post.id')))->setField('remaining',$num);
    
		if($sum !== false)
		{
			echo json_encode(1);
		}

	}

}
