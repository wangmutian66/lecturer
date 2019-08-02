<?php
namespace Home\Controller;
use Think\Controller;

class CourseController extends Controller {

	public function index(){

		session("nav","course");
		$id = getcompanyId();
        
		$enterprise = M('enterprise')->where(array('id'=>$id))->select();
		$b1 = M('user')->where(array('uid'=>$id,'lectype'=>1))->field('id,name')->select();
		$salesman=M("salesman")->where(array("uid"=>$id))->field("id,name")->select();
		$this->assign('salesman',$salesman);
		$this->assign('a1',$enterprise);//大区总裁下有多少分公司
		$this->assign('b1',$b1);//分公司下有多少讲师
		$this->display();
	}
	
	public function showchek(){
	    $id=I('post.id');
	    $lectype=M('user')->where(array('uid'=>$id,'lectype'=>1))->field('id,name')->select(); 
	    echo json_encode($lectype);
	}
	
    //起航课程搜索
    public function searove(){
    	$db = C('DB_PREFIX');
    	$ytime = I('post.ytime');
    	$ntime = I('post.ntime');
    	
    	$fields=I('post.selec','');//字段名称下拉菜单选项值
    	$punt=I('post.punt','');//文本框里的内容
    	$con=I('post.con');//已进行   未进行

    	$p = I('post.page');
    	$id = getcompanyId();
        //$t = strtotime($ytime);
		// $n = strtotime($ntime);		         
		
		$limit = 6;
		$p= I('post.page',1);
		$first = ($p-1) * $limit;
		
		$map['c.uid'] = $id;
		$map['lectype']=1;
		$map['starttime']  = array('egt',$ytime); //
        $map['endtime']  = array('elt',$ntime);
        if($fields!=''){
            $map["$fields"]  = array('like',"%$punt%");
        }
        $map["conduct"]=$con;
        
		$course = M('course as c')
				->join($db.'enterprise as e on e.id=c.uid','left')
				->join($db.'user as u on c.jid=u.id','left')
				->field('c.id as c_id,c.starttime as c_starttime,c.endtime as c_endtime,c.classname as c_clasname,c.place as c_place,u.name as u_name,e.Qyname as e_Qyname,c.conduct as conduct')
				->where($map)
				->limit($first,$limit)
				->select();

		foreach ($course as $key => $value) {
			$number = M('course_com')->where(array("cid"=>$value["c_id"]))->group("cid")->field("sum(pnum) as number")->find();
			//$course[$key]["number"]=$number["number"];
			$course[$key]["number"]=$number["number"]==null?0:$number["number"];
		}
		
		$studentCount = M('course as c')
				->join($db.'enterprise as e on e.id=c.uid','left')
				->join($db.'user as u on e.id=u.uid','left')
				->where($map)
				->count();
		$ini["pagecount"]=ceil($studentCount/$limit);
		
		$ini['error'] = 1;
		$ini['data'] = $course;
	// echo '<pre>';
 //       print_r($ini);die;
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
		$course = M('course as c')->where(array('c.uid'=>$id))->order('c.id desc')->limit($first,$limit)->select();
		
		foreach ($course as $k => $v) {
			$course[$k]["Qyname"]=M('enterprise')->where(array('id'=>$v['uid']))->getField("Qyname");//分公司名字
			$course[$k]["jname"]=M('user')->where(array('id'=>$v['jid']))->getField("name");//分公司名字
			$number=M('course_com')->where(array("cid"=>$v["id"]))->group("cid")->field("sum(pnum) as number")->find();
			$course[$k]["number"]=$number["number"]==null?0:$number["number"];
		}
	    // echo '<pre>';
	    // print_r($course);die;
		echo json_encode($course);
	}
    
	//修改参与人数
	public function editnum(){
	    $id=I('post.id');
	    $pnum=I('post.pnum');
	    M('course_com')->where(array('id'=>$id))->save(array("pnum"=>$pnum));
	    echo json_encode(1);
	}
	
	public function editlist(){
	    $id=I('post.id');
	    $courfind=M('course')->where(array("id"=>$id))->find();
	    echo json_encode($courfind);	    
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
        
        $id = session("uid");
        $id=getcompanyId();

        $asd = M('course')->where(array('uid'=>$id))->count();
        
        $enterprise1 = ceil($asd/6);
        echo json_encode($enterprise1);
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
		$course_com_order = M('course_com_order')->add($ini);
		$fid=M('course_com')->where(array("id"=>$info['comid']))->getField("fid");
		$info_rid=M("information")->where(array("id"=>$fid))->getField("rid");

		//判断添加移交记录 said
		if($info_rid!=$ini['rid']){
            $arr=array();
			$arr["yrid"]=$info_rid;
			$arr["nrid"]=$ini['rid'];
			$arr["fid"]=$fid;
			$arr["ctime"]=date("Y-m-d H:i:s");
			$arr["status"]=4;
			M('transfer_log')->add($arr);
		}

		//修改成交人
		M("information")->where(array("id"=>$fid))->save(array("status"=>1,"rid"=>$ini['rid']));
		//如果只是小票 要修改小票剩余数量
		//如果是只有九大规划要添加九大规划倒计时
        $buystatus=$this->getBuyStatus($ini);

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
		$uid = session("uid");
        $uid=getcompanyId();
        $yy=I('post.yy','');
		$id = I('post.id');//当前企业下的讲师
		$search=' 1=1 ';
        if($yy!=''){
           $search="f.name like '%$yy%'"  ;
        }
        //->join(C('DB_PREFIX')."course_com as c on f.id=c.fid")
		$information = M('information as f')->where(array('f.uid'=>$uid))->where($search)->select();
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
		echo json_encode($information);
	}
	//修改course表里
	public function qweadd(){
		$uid = session("uid");
        $uid=getcompanyId();
        $id = I('post.id');
		$nameq = I('post.nameq');
		$s = I('post.s');
		$newstr = substr($s,0,strlen($s)-1);
		$newarray=explode("|", $newstr);
	       
		foreach ($newarray as $row){
		    $exrow=explode(",", $row);
		    $fid=$exrow[0];
		    $pnum=$exrow[1];
		    $courselist=M('course_com')->where(array('cid'=>$id,'fid'=>$fid))->find();
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

	    $courselist=M('course_com')->where(array('cid'=>$id))->getField("fid",true);
        
	    if($courselist){
	    	$fid=implode(",", $courselist);
		    $inforlist=M('information')->where("id in ($fid)")->select();
		    foreach($inforlist as $k=>$i){
		    
		    	$courseCom=M('course_com')->where(array('cid'=>$id,'fid'=>$i["id"]))->find();
		    	$inforlist[$k]["comid"]=$courseCom["id"];
		    	$inforlist[$k]["pnum"]=$courseCom["pnum"];
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
		M('course_com')->where(array('id'=>$id))->delete();
		$course_com_order = M('course_com_order')->where(array('comid'=>$id))->delete();
		
		
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
        $id=$post['id'];

        $result=M('course_com_order as c')->where(array('c.comid'=>$id))->save($ini);
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
		$course_com_order = M('course_com_order')->where(array('id'=>$id))->select();
		if($course_com_order){
			$result['errcode'] = 1;
			$result['dis'] = $course_com_order;
		}
		else
		{
			$result['errcode'] = 0;
           
		}

		echo json_encode($result);
	}



}
