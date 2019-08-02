<?php
namespace Home\Controller;
use Think\Controller;

class EnterpriseController extends BaseController{
	//class EnterpriseController extends BaseController{


   public function vicezong(){
   	   $role=I('get.role');
   	   $type=$_SESSION["type"];
   	   $uid=session("id");
	   session("nav","branch");
	   session("rightnav","vicezong".$role);
   	   $nameArray=array("8"=>"分公司总经理","7"=>"分公司副总经理","6"=>"分公司业务总监","5"=>"分公司业务副总监");
 
   	   if($type>=9){
   	       $stats=1;
   	   }else{
   	       $stats=0;
   	   }
   	   
       if($_POST){
           
       	    $p = I('post.page');
	        $type = I('post.type');
	        $role=I('get.role');
	        $uid=I('post.uid');
	        $limit = 6;
	        
	        $first = ($p-1) * $limit;//,'uid'=>$uid
	        
//	        $user=array();
//	        if($_SESSION["type"]==10){
//                $adsel=M('admin')->where(array("pid"=>$uid))->getField("id",true);
//            	if(empty($adsel)){
//            		echo json_encode(array());
//            		die;
//            	}
//            	$aid=implode(",", $adsel);
//            	$adsel=M('enterprise')->where(array("aid"=>array('in',$aid)))->getField("id",true);
//
//            	if(!empty($adsel))
//            	{
//            		$user = M('user')->field('account,pwd,mobile,name,id,prid')->limit($first,$limit)->where("uid in (".implode(",", $adsel).")")->where(array('role'=>$role))->order('id desc')->select();//先不安uid区分，全排出来
//            	}
//	        }else if($_SESSION["type"]==9){
//	            $user = M('user')->field('account,pwd,mobile,name,id,prid')->limit($first,$limit)->where(array('role'=>$role,'prid'=>$uid))->order('id desc')->select();//先不安uid区分，全排出来
//	        }else{
//	            $uid=M('user')->where(array('id'=>$uid))->getField("uid");
//	            $user = M('user')->field('account,pwd,mobile,name,id,prid')->limit($first,$limit)->where(array('role'=>$role,'uid'=>$uid))->order('id desc')->select();//先不安uid区分，全排出来
//	        }

		    $user = M('user')->field('account,pwd,mobile,name,id,prid,lectype,finatype')->where(array('role'=>$role,'uid'=>getcompanyId()))->limit($first,$limit)->order('id desc')->select();

	        echo json_encode($user);
       }
       else{
            $this->assign("name",$nameArray[$role]);
            $this->assign('stats',$stats);
       	    $this->assign('uid',$uid);
       	    $this->assign('role',$role);
          	$this->display("vicezong");
       }
   	   
   }
   public function selename()
   {
        $type=session("type");
        $enterprise=null;

	    $id=getcompanyId();
	    $enterprise = M('enterprise')->where(array("id"=>$id))->select();
   		echo json_encode($enterprise);
   }
   public function enteinfo()
   {
   	    $p = I('post.p');
   		$limit = 6;
   		$first = ($p-1) * $limit;
   		$enterprise = M('user')->where(array('uid'=>I('post.val'),'role'=>I('post.role')))->order("id desc")->limit($first,$limit)->select();

   		echo json_encode($enterprise);
   }
   public function pages(){
        $users = M("user")->where(array('uid'=>I('get.val'),'role'=>I('get.role')))->count();
        $users = ceil($users/6);
        echo json_encode($users);
    }
   public function dxchange(){
       $id=I('post.id');
       $enterprise=M('enterprise')->where(array('aid'=>$id))->select();
       
       echo json_encode($enterprise);
   }
	/**
	 * 企业账号数据
	 * @param  null
	 * @return void
	 */
	public function index(){ //主页
		$this->redirect('Home/Login/login');
        $map['id'] = session('uid');
        
		$info = M('enterprise')
    		->where($map)
    		->find();	//查询企业信息

		
		if($info['id']){
			$maps['qid'] = $info['id']; 
			$data = M('student')
			->field('id,uid,name,mobile,note')
			->where($maps)
			->order('id desc')
			->select();	//企业下所有学员

			foreach ($data as $key => $value) {
				$ids[] = $value['id'];
			}
			$ini['o.sid'] = array('in',$ids);
			$db = C('DB_PREFIX');

			if($ids){
				$orders = M("order as o")
				->field('u.name,u.mobile,o.id,o.sid,o.times,o.ticket,o.card,o.friends,o.disciple,o.ptype,o.ctype,o.ftype,o.dtype,o.planning as planning,o.ntype as ntype')
				->where($ini)
				->join($db.'user as u on o.uid = u.id','left')
				->order('o.id desc')
				->select(); //查询学员相对应的订单列表与讲师信息
			}
			
			$user = M('user')
			->field('id,name')
			->where(array('uid'=>session('uid'),'type'=>1))
			->select(); //查询该企业下所有讲师与财务姓名

			$date = date('Y年m月d日');
			// echo "<pre>";
			// var_dump($data);
			$this->assign('date', $date);
			$this->assign('user', $user);
			$this->assign('orders', $orders);
			$this->assign('data', $data);
		}

		$this->assign('info', $info);
		$this->display();
	}
	/**
	 * 修改法人姓名
	 * @param  null
	 * @return void
	 */
	function fedit(){
		if(IS_POST){
			$data = I('post.');
			$type = $data['type'];	
			$value = $data['value'];	
			if(!empty($value) && !empty($type)){
				$map['id'] = session('uid');
				if($type == 1){
					$data['person'] = $value;
				}else if($type == 2){
					$data['phone'] = $value;
					$info = M('enterprise')->where(array('phone'=>$value))->find();
					if($info){
						$arr['errcode'] = 2;
			            $arr['errmsg'] = '手机号已存在!';
			            $this->ajaxReturn($arr);
			            die();	
					}
				}
				$status = M('enterprise')->where($map)->save($data);
				if($status){
					$arr['errcode'] = 1;
		            $arr['errmsg'] = '修改成功!';
		            $this->ajaxReturn($arr);					
				}else{
					$arr['errcode'] = 0;
		            $arr['errmsg'] = '操作失败!';
		            $this->ajaxReturn($arr);
				}
			}else{
				$arr['errcode'] = 2;
	            $arr['errmsg'] = '参数不正确!';
	            $this->ajaxReturn($arr);
			}	
		}
	}

	/**
	 * 查询讲师.财务电话号
	 * @param  null
	 * @return void
	 */
	function jmobile(){
		$id = I('post.js'); //讲师.财务id
		$map['id'] = $id;
		$map['uid'] = session('uid');
		$data = M('user')->field('mobile')->where($map)->find();
		if(!empty($data)){
			$arr['errcode'] = 1;
            $arr['errmsg'] = $data['mobile'];
            $this->ajaxReturn($arr);
		}else{
			$arr['errcode'] = 0;
            $arr['errmsg'] = '手机号不存在!';
            $this->ajaxReturn($arr);
		}
	}

	function savewang()
	{
		$id = I('post.id');	 //id
		$tid = I('post.tid');//标识
		$cin = I('post.cin'); //内容
		$ini['id'] = $id;
		if ($tid == 1) 
		{	$ini['mobile'] = $cin;
			
		}else if($tid == 2)
		{
			$ini['note'] = $cin;
			
		}
		$studentdata =  M('student')->where($ini)->find();
		if (empty($studentdata)) 
		{
			
		
		$student = M('student')->save($ini);
		
		if ($student) 
		{
			echo json_encode(1);
		}else
		{
			echo json_encode(2);
		}
		}else
		{
			echo json_encode(3);
		}
		
	}

	/**
	 * 添加订单
	 * @param  null
	 * @return void
	 */
	function order(){
		if(IS_POST){
			$data = I('post.');
			$data['qid'] = session('uid');
			$data['times'] = date('Y-m-d');
			if(M('order')->add($data)){
				$arr['errcode'] = 1;
	            $arr['errmsg'] = '添加成功!';
	            $this->ajaxReturn($arr);
			}else{
				$arr['errcode'] = 0;
	            $arr['errmsg'] = '添加失败!';
	            $this->ajaxReturn($arr);
			}
		}
	}

	/**
	 * 订单修改
	 * @param  null
	 * @return void
	 */
	function orderedit(){
		if(IS_POST){
			$data = I('post.');
			$val = $data['val'];
			$id = $data['id'];
			$name = $data['name'];
			$map['id'] = $id;
			$map['qid'] = session('uid');
			$info = M('order')->where($map)->find();
			if(!empty($info)){
				$ini[$name] = $val;
				$status = M('order')->where(array('id'=>$id))->save($ini);
				if($status){
					$arr['errcode'] = 1;
		            $arr['errmsg'] = '修改成功!';
		            $this->ajaxReturn($arr);
				}else{
					$arr['errcode'] = 2;
		            $arr['errmsg'] = '操作失败!';
		            $this->ajaxReturn($arr);
				}
			}else{
				$arr['errcode'] = 0;
	            $arr['errmsg'] = '订单不存在!';
	            $this->ajaxReturn($arr);	
			}
		}
	}

  

    /**
	 * 订单状态
	 * @param  null
	 * @return void
	 */
    public function statu($status){
    	switch ($status) {
    		case 0:
    			$status = '取消';
    			break;
    		case 1:
    			$status = '已发';
    			break;
    		case 2:
    			$status = '未发';
    			break;  		
    		default:
    			
    			break;
    	}
    	return $status;
    }
/************************************企业账号主页**************************************/
	/**
	 * 讲师数据
	 * @param  null
	 * @return void
	 */
	public function lecturer(){
		$this->display();
	}
	/**
	 * ajax动态获取讲师数据
	 * @param  null
	 * @return void
	 */
	function jsajax(){
		$post = I('post.');
		$page = $post['page'] ? floor($post['page']) : 1 ; //第几页
		$type = $post['type'] ? $post['type'] : 1 ; //讲师

		$limit = 8;//每页显示条数
		$data = array();
        $data['p'] = $page;

    	$map['type'] = $type; //讲师或财务
    	$map['uid'] = session('uid'); //登录用户
    	if(!empty($post['timeyear']) && !empty($post['timemonth']) && !empty($post['timeday']) && !empty($post['beforeyea']) && !empty($post['datayear']) && !empty($post['day'])){
    		$ytime = $post['timeyear'].'-'.$post['timemonth'].'-'.$post['timeday'];
    		$ntime = $post['beforeyea'].'-'.$post['datayear'].'-'.$post['day'];
    		$map['times'] = array(array('egt',$ytime),array('elt',$ntime));
    	}

        $datas = M("user")->where($map)->select(); //查询所有企业下的讲师

        $allnum = count($datas);
        $p = ($page - 1) * $limit;

        if($page != 1){
            if( ($allnum % $limit) <= 0 ){
                $data['p'] = $data['p'];
                $p = ($page - 1) * $limit;
            }
        }

        $data['num'] = ceil($allnum / $limit); 
        $data['data']   = M('user')
        				->field('id,uid,name,mobile')
                        ->where($map)
                        ->limit("{$p},{$limit}")
                        ->order('id desc')
                        ->select();

		foreach ($data['data'] as $key => $value) {
			$ids[] = $value['id'];
		}

		$info = M('enterprise')
		->where(array('id'=>session('uid')))
		->find();	//查询企业信息

		$pdj = round(($info['leafletstc']/100)*$info['tc'],2);
		$kdj = round(($info['leafletscc']/100)*$info['cc'],2);
		$qdj = round(($info['leafletsfc']/100)*$info['fc'],2);
		$ddj = round(($info['leafletsdc']/100)*$info['dc'],2);
		$ndj = round(($info['leafletsnc']/100)*$info['nc'],2);

		$db = C('DB_PREFIX');
		if(!empty($ids)){
			$ini['qid'] = session('uid');
			$ini['uid'] = array('in',$ids);

			$student = M("order")
			->field('uid,sid')
			->where($ini) 
			->group('uid,sid')
			->select();	//查询学员相对应的订单列表与讲师信息

			$nums = array();
			foreach ($student as $key => $value) {
				$nums[$value['uid']][$key] = $value['sid'];
			}
		}
		
		if(!empty($ids)){

			$ins['uid'] = array('in',$ids);
			$ins['qid'] = session('uid');
			$order = M('order')
			->where($ins)
			->select();

			foreach ($data['data'] as $key => $value) {
				foreach ($order as $key1 => $value1) {
					if($value['id'] == $value1['uid']){
						$tt[$value['id']] = 0;
						if($value1['ptype'] == '1'){
							$pt[$value1['uid']] += $value1['ticket']*$pdj;
						}

						if($value1['ptype'] == '2'){
							$pt2[$value1['uid']] += $value1['ticket']*$pdj;
						}

						if($value1['ctype'] == '1'){
							$ct1[$value1['uid']] += $value1['card']*$kdj;
						}
						if($value1['ctype'] == '2'){
							$ct2[$value1['uid']] += $value1['card']*$kdj;
						}

						if($value1['ftype'] == '1'){
							$ft1[$value1['uid']] += $value1['friends']*$qdj;
						}
						if($value1['ftype'] == '2'){
							$ft2[$value1['uid']] += $value1['friends']*$qdj;
						}

						if($value1['dtype'] == '1'){
							$dt1[$value1['uid']] += $value1['disciple']*$ddj;
						}
						if($value1['dtype'] == '2'){
							$dt2[$value1['uid']] += $value1['disciple']*$ddj;
						}	
						if($value1['ntype'] == '1'){
							$nt1[$value1['uid']] += $value1['planning']*$ndj;

						}
						if($value1['ntype'] == '2'){

							$nt2[$value1['uid']] += $value1['planning']*$ndj;
						}					
					}
				}
			}

			foreach($tt as $key => $val){	//每个人已发金额
			    $sums[$key] = $val + $ct1[$key] + $ft1[$key] + $dt1[$key] + $pt[$key] + $nt1[$key];
			}

			foreach($tt as $key => $val){	//每个人未发金额
			    $sums2[$key] = $val + $ct2[$key] + $ft2[$key] + $dt2[$key] + $pt2[$key] + $nt2[$key];
			}	

			foreach($tt as $key => $val){	//每个人应发发金额
			    $sums1[$key] = $val + $sums2[$key] + $sums[$key];
			}	

			foreach ($data['data'] as $key => $value) {
				foreach ($sums as $key1 => $value1) {	//每个人已发金额
					if($key1 == $value['id']){
						$data['data'][$key]['yf'] = $value1;
					}
				}
				foreach ($sums1 as $key2 => $value2) {	//每个人应发金额
					if($key2 == $value['id']){
						$data['data'][$key]['zje'] = $value2;
					}
				}
				foreach ($sums2 as $key3 => $value3) {	//每个人未发金额
					if($key3 == $value['id']){
						$data['data'][$key]['wf'] = $value3;
					}
				}				
			}		
		}
    
		if(!empty($nums)){
			foreach ($data['data'] as $key => $value) {
				foreach ($nums as $key1 => $value1) {
					if($value['id'] == $key1){
						$data['data'][$key]['nums'] = count($value1);
					}
				}
			}
		}
        echo json_encode($data);
	}

	/**
	 * 讲师数据表格下载
	 * @param  null
	 * @return void
	 */
	function jreport(){
		$map['uid'] = session('uid');
		$map['type'] = 1;
		$data = jexcel($map);
		if(!empty($data)){
			echo $data;
		}else{
			$this->redirect("Home/Enterprise/lecturer");
		}
	}
/************************************讲师数据**************************************/
	/**
	 * 账号管理数据
	 * @param  null
	 * @return void
	 */
	public function account (){
        $uid = session('uid');
        $map['id'] = $uid;
		$info = M('enterprise')
		->where($map)
		->find();
		$this->assign('info', $info);
		$this->display();
	}

	/**
	 * 账号管理ajax
	 * @param  null
	 * @return void
	 */
	public function ajaxGetData (){
		$page = I('post.page') ? floor(I("post.page")) : 1 ; //第几页
		$type = I('post.type') ? I('post.type') : 1 ; //财务或讲师
		$limit = 8;//每页显示条数
		$data = array();
        $data['p'] = $page;
    	$map['type'] = $type; //讲师或财务
    	$map['uid'] = session('uid'); //登录用户
        $datas = M("user")->where($map)->select(); //查询所有企业下的讲师
        $allnum = count($datas);
        $p = ($page - 1) * $limit;

        if($page != 1){
            if( ($allnum % $limit) <= 0 ){
                $data['p'] = $data['p'] - 1;
                $p = ($page - 2) * $limit;
            }
        }

        $data['num'] = ceil($allnum / $limit); 
        $data['data']   = M('user')
                        ->where($map)
                        ->limit("{$p},{$limit}")
                        ->order('id desc')
                        ->select();
        echo json_encode($data);
	}

	/**
	 * 添加账号
	 * @param  null
	 * @return void
	 */
	public function add()
	{
		if(IS_POST){
            $data = I("post.");
            $uid = session('uid');  //所添加的企业id
            $user = M("user");
            $map['uid'] = $uid;
            $map['account'] = $data['account'];

            $check = $user
            ->where($map)
            ->find();

            $enterprise = M('enterprise')
            ->where(array('id'=>$uid,'account'=>$data['account']))
            ->find();

            if(!$check && !$enterprise){
            	$check1 = $user->where(array('uid'=>$uid,'mobile'=>$data['mobile']))->find();

            	if(!$check1){
            		$useraccount = M('enterprise')->where(array('account'=>I('post.account')))->find();
            		if($useraccount != false)
            		{
            			echo 4;die;
            		}
            		$mobile = M('enterprise')->where(array('phone'=>I('post.mobile')))->find();
            		if($mobile != false)
            		{
            			echo 5;die;
            		}
            		$data['uid'] = $uid;
            		$data['times'] = date('Y-m-d');
            		$data['pwd'] = md5($data['pwd']);
					if($user->add($data)){
		                $arr['errcode'] = 1;
		                $arr['errmsg'] = '添加成功!';
		                $this->ajaxReturn($arr);
		            }else{
		                $arr['errcode'] = 0;
		                $arr['errmsg'] = '添加失败!';
		                $this->ajaxReturn($arr);
		            }
            	}else{
	                $arr['errcode'] = 2;
	                $arr['errmsg'] = '手机号已被占用!';
	                $this->ajaxReturn($arr);            		
            	}
            }else{
                $arr['errcode'] = 2;
                $arr['errmsg'] = '登录账号已被占用!';
                $this->ajaxReturn($arr);
            }
            
        } 
	}

	/**
	 * 添加删除
	 * @param  null
	 * @return void
	 */
	public function del()
	{
		if(IS_POST){
            $id = I("post.id");	//要删除的讲师或财务id
            $uid = session('uid');  //所属的企业id

            $map['id'] = $id;
            $map['uid'] = $uid;
            $info = M("user")->where($map)->find();
            if(!empty($info)){
            	M()->startTrans();
            	$ini['uid'] = $id;
            	$ini['qid'] = $uid;
            	$order = M('order')->where($ini)->select();
            	if(!empty($order)){
            		$order = M('order')->where($ini)->delete();
            	}else{
            		$order = 1;
            	}
            	if($order){
	    			$status = M("user")->where($map)->delete();
	    			if($status){
	    				M()->commit();
	    			}else{
	            		M()->rollback();
		                $arr['errcode'] = 0;
		                $arr['errmsg'] = '操作失败!';
	            		echo json_encode($arr);
	                	die();            				
	    			}
            	}else{
            		M()->rollback();
	                $arr['errcode'] = 0;
	                $arr['errmsg'] = '操作失败!';
            		echo json_encode($arr);
                	die();
            	}
                $arr['errcode'] = 1;
                $arr['errmsg'] = '删除成功!';
                $this->ajaxReturn($arr);
            }else{
                $arr['errcode'] = 2;
                $arr['errmsg'] = '用户不存在!';
                $this->ajaxReturn($arr);
            }
        } 
	}

	/**
	 * 提成编辑
	 * @param  null
	 * @return void
	 */
	public function commission(){
		if(IS_POST){
			$data = I("post.");
			// echo '<pre>';
			// print_r($data);die;
			$tc = $data['tc'];	//票提成比例
			$cc = $data['cc'];	//卡提成比例
			$fc = $data['fc'];	//朋友圈提成比例
			$dc = $data['dc'];	//弟子提成比例
			$nc = $data['nc'];	//弟子提成比例
			$leafletstc = $data['leafletstc'];	//单张票金额
			$leafletscc = $data['leafletscc'];	//单张卡金额
			$leafletsfc = $data['leafletsfc'];	//单条朋友圈金额
			$leafletsdc = $data['leafletsdc'];	//单个弟子金额
			$leafletsnc = $data['leafletsnc'];	//单个九大规划金额

			if(preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $tc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $cc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $fc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $dc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $leafletstc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $leafletscc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $leafletsfc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $leafletsdc) && preg_match("/^\d{1,10}(\.\d{1,2})?$|^-\d{1,10}(\.\d{1,2})?$/", $leafletsnc)){ //验证格式
				$user = M("enterprise");
				$map['id'] = session('uid');
				$user->where($map)->save($data);
				if($user !== false){
	                $arr['errcode'] = 1;
	                $arr['errmsg'] = '添加成功!';
	                $this->ajaxReturn($arr);
	            }else{
	                $arr['errcode'] = 0;
	                $arr['errmsg'] = '添加失败!';
	                $this->ajaxReturn($arr);
		        }
			}else{
                $arr['errcode'] = 3;
                $arr['errmsg'] = '提成比例参数错误!';
                $this->ajaxReturn($arr);
			}
		}
	}

	/**
	 * 修改信息
	 * @param  null
	 * @return void
	 */
	public function edit(){
		if(IS_POST){
			$data = I("post.");

			$id = $data['id']; 		//要修改的讲师财务id
			$type = $data['type']; 	//是否是讲师
			$val = $data['val'];	//要修改的值
			$name = $data['name'];	//要修改的字段

			$user = M("user");

			$map['uid'] = session('uid');	//登录用户
			$map['id'] = $id;
			$map['type'] = $type; 
			$info = $user->where($map)->find();
			if($info){
				$useraccount = M('user')->where(array('account'=>I('post.val')))->find();
				if($useraccount != false)
				{
					echo 3;die;
				}
				else
				{
					$usermoble = M("user")->where(array('mobile'=>I('post.val')))->find();
					if($usermoble != false)
					{
						echo 3;die;
					}
					$admin = M('enterprise')->where(array('phone'=>I('post.val')))->find();
					if($admin != false)
					{
						echo 3;die;
					}
					else
					{
						$adminaccount = M('enterprise')->where(array('account'=>I('post.val')))->find();
						if($adminaccount!= false)
						{
							echo 3;die;
						}
						
					}
				}
				
				if($user->where(array('id'=>$map['id']))->save(array($name=>$val))){
	                $arr['errcode'] = 1;
	                $arr['errmsg'] = '修改成功!';
	                $this->ajaxReturn($arr);
	            }else{
	                $arr['errcode'] = 0;
	                $arr['errmsg'] = '修改失败!';
	                $this->ajaxReturn($arr);
		        }
			}else{
				$arr['errcode'] = 2;
                $arr['errmsg'] = '用户信息错误!';
                $this->ajaxReturn($arr);
			}
		}
	}

	/**
	 * 修改密码
	 * @param  null
	 * @return void
	 */	
	public function pwd(){
		if(IS_POST){
			$data = I("post.");
			$id = $data['id']; 		//要修改的讲师财务id
			$oldPwds = $data['oldPwds']; 	//原密码
			$newPwds = $data['newPwds'];	//要修改的新密码

			$user = M("user");
			$map['uid'] = session('uid');	//登录用户
			$map['id'] = $id;
			$map['pwd'] = md5($oldPwds);

			$info = $user->field("pwd")->where($map)->find();
			if($info){
				$pwds = md5($newPwds);
				$status = $user->where($map)->save(array("pwd"=>$pwds));
				if($status){
					$arr['errcode'] = 1;
	                $arr['errmsg'] = '修改成功!';
	                $this->ajaxReturn($arr);
				}else{
					$arr['errcode'] = 0;
	                $arr['errmsg'] = '操作失败!';
	                $this->ajaxReturn($arr);
				}
			}else{
				$arr['errcode'] = 2;
                $arr['errmsg'] = '原密码不正确!';
                $this->ajaxReturn($arr);
			}
		}
	}
/************************************账号管理数据**************************************/

public function branch(){
	$role = C('ROTA_STATE.branch');
	$result=array_splice($role,1);
	$this->assign('role',$result); 
	$this->display();
}

public function branchget(){
	  $id = 1;
	  $qwe = I("post.");
	  $asd['name'] = $qwe['gsname'];    
	  $asd['mobile'] = $qwe['gsmobile'];
	  $asd['account'] = $qwe['account'];
	  $asd['lectype'] = $qwe['letype'];
	  $asd['finatype'] = $qwe['cwtype'];
	  $asd['pwd'] = md5(I('post.gspwd'));
	  $asd['times'] = date('Y-m-d');
      $enterprise =  M("enterprise")->where(array('aid'=>$id))->find();
     if(!empty($enterprise)){
     	$asd['prid'] = $enterprise['aid'];
     }
      
     $user = M("user")->where(array('account'=>I('post.account')))->find();
     if(!empty($user)){
     	echo json_encode(1);
     }else{
     	$user = M('user')->add($asd);
     	if($user !==false){
     		echo json_encode(2);
     	}else{
     		echo json_encode(3);
     	}
     }
 }
 
 /**
  * 导出Excel
  * @param  null
  * @return void
  */
 function report(){//导出Excel
 	$ktimes = I('get.kttime');
 	$jtimes = I('get.jttime');
 	$lsor = I('get.lsor');
 	$uid  = session('uid');	//登录用户
 
 	$info = M('enterprise')
 	->where(array('id'=>$uid))
 	->find();	//查询企业信息
 	$db = C('DB_PREFIX');
 	$pdj = round(($info['leafletstc']/100)*$info['tc'],2);
 	$kdj = round(($info['leafletscc']/100)*$info['cc'],2);
 	$qdj = round(($info['leafletsfc']/100)*$info['fc'],2);
 	$ddj = round(($info['leafletsdc']/100)*$info['dc'],2);
 	$ndj = round(($info['leafletsnc']/100)*$info['nc'],2);
 	$ini['o.qid'] = $uid;
 	$ini['o.times'] = array(array('lt',$jtimes),array('gt',$ktimes));
 	$ini['u.name'] = array('like',"%$lsor%");
 	$ini['o.qid'] = $uid;
 	
 	$a = M('order as o')->join($db.'user as u on o.uid=u.id','left')
 	->join($db.'student as t on o.sid = t.id ','left')
 	->where($ini)
 		
 	->field('o.id as id , o.times as times ,u.name as uname ,u.id as uuid, u.mobile as umobile, t.mobile as tmobile , t.note as tnote , t.name as tname,o.ticket as ticket,o.card as card,o.friends as friends,o.planning as planning,o.disciple as disciple,o.ptype,o.ctype,o.ftype,o.dtype,o.ntype ')
 	->select();
 	
 		
 	foreach ($a as $k=>$v)
 	{
 		$arr[$v['uuid']]['umobile']= $v['umobile'];
 		$arr[$v['uuid']]['uname'] = $v['uname'];
 		$arr[$v['uuid']]['count'][$k] = $v;
 	
 	}
 	foreach ($arr as $k1=>$v1)
 	{
 		$hay = "";
 		$tiy ="";
 		$yiaction = "";
 		$waction = "";
 		$yiaction2 = "";
 		$waction2 = "";
 		$yiaction3= "";
 		$waction3 = "";
 		$yiaction4 = "";
 		$waction4 = "";
 		$yiaction5 = "";
 		$waction5 = "";
 	
 		foreach ($v1['count'] as $k2=>$v2)
 		{
 				
 				
 				
 			$arr[$k1]['count'][$k2]['pcount'] = $v2['ticket']*$pdj;
 			$arr[$k1]['count'][$k2]['kcount'] = $v2['card']*$kdj;
 			$arr[$k1]['count'][$k2]['qcount'] = $v2['friends']*$qdj;
 			$arr[$k1]['count'][$k2]['dcount'] = $v2['planning']*$ddj;
 			$arr[$k1]['count'][$k2]['ncount'] = $v2['disciple']*$ndj;
 			$arr[$k1]['count'][$k2]['pil'] = $info['tc'];
 			$arr[$k1]['count'][$k2]['kil'] = $info['cc'];
 			$arr[$k1]['count'][$k2]['qil'] = $info['fc'];
 			$arr[$k1]['count'][$k2]['dil'] = $info['dc'];
 			$arr[$k1]['count'][$k2]['nil'] = $info['nc'];
 	
 			$arr[$k1]['count'][$k2]['ticketall'] = $v2['ticket']*$info['leafletstc'];
 			$arr[$k1]['count'][$k2]['cardall'] = $v2['card']*$info['leafletscc'];
 			$arr[$k1]['count'][$k2]['friendsall'] =  $v2['friends']*$info['leafletsfc'];
 			$arr[$k1]['count'][$k2]['planningall'] = $v2['planning']*$info['leafletsnc'];
 			$arr[$k1]['count'][$k2]['discipleall'] = $v2['disciple']*$info['leafletsdc'];
 			$hay += $arr[$k1]['count'][$k2]['ticketall']+$arr[$k1]['count'][$k2]['cardall']+$arr[$k1]['count'][$k2]['friendsall']+$arr[$k1]['count'][$k2]['planningall']+$arr[$k1]['count'][$k2]['discipleall'];
 			$tiy += ($v2['ticket']*$pdj)+($v2['card']*$kdj)+($v2['friends']*$qdj)+($v2['planning']*$ddj)+($v2['disciple']*$ndj);
 			if($v2['ptype'] == 1 )
 			{
 				$yiaction +=  $v2['ticket']*$pdj;
 			}else if($v2['ptype'] == 2 )
 			{
 				$waction +=  $v2['ticket']*$pdj;
 			}else
 			{
 				$yiaction =0;
 				$waction = 0;
 			}
 	
 			if($v2['ctype'] == 1 )
 			{
 				$yiaction2 +=  $v2['card']*$kdj;
 			}else if($v2['ctype'] == 2 )
 			{
 				$waction2 +=  $v2['card']*$kdj;
 			}else
 			{
 				$yiaction2 =0;
 				$waction2 = 0;
 			}
 	
 			if($v2['ftype'] == 1 )
 			{
 				$yiaction3 +=  $v2['friends']*$qdj;
 			}else if($v2['ftype'] == 2 )
 			{
 				$waction3 +=  $v2['friends']*$qdj;
 			}else
 			{
 				$yiaction3 =0;
 				$waction3 = 0;
 			}
 	
 			if($v2['dtype'] == 1 )
 			{
 				$yiaction4 +=  $v2['disciple']*$ddj;
 			}else if($v2['dtype'] == 2 )
 			{
 				$waction4 +=  $v2['disciple']*$ddj;
 			}else
 			{
 				$yiaction4 =0;
 				$waction4 = 0;
 			}
 	
 			if($v2['ntype'] == 1 )
 			{
 				$yiaction5 +=  $v2['planning']*$ndj;
 			}else if($v2['ntype'] == 2 )
 			{
 				$waction5 +=  $v2['planning']*$ndj;
 			}else
 			{
 				$yiaction5 =0;
 				$waction5 = 0;
 			}
 	
 	
 		}
 	
 		$arr[$k1]['yecount'] = $hay; //业绩
 		$arr[$k1]['ticount'] = $tiy; //金额
 		$arr[$k1]['y'] += $yiaction+$yiaction2+$yiaction3+$yiaction4+$yiaction5;  //未发
 		$arr[$k1]['w'] += $waction+$waction2+$waction3+$waction4+$waction5;  //已发
 			
 	}
 	$z = 0;
 	foreach ($arr as $y=>$t)
 	{	
 		
 		$arract[$z] = $t;
 		$z++;
 	}
/*  echo "<pre>";
 print_r($arract);
 echo "</pre>";
 exit(); */
 	
 	$dataar = date('Y-m-d H:i:s'); //时间
 	$fileName=$dataar.'讲师数据';
 	$fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称
 	
 	import("Org.Util.PHPExcel");
 	$objPHPExcel = new \PHPExcel();
 	$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
 	$objPHPExcel->getActiveSheet(0)->setCellValue('A1', '姓名');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('B1', '联系电话');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('C1', '业绩');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('D1', '提成');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('E1', '已发');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('F1', '未发');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('G1', '学员');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('H1', '学员电话');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('I1', '备注');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('J1', '时间');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('K1', '分类');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('L1', '数量');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('M1', '金额');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('N1', '提成比');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('O1', '提成');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('P1', '状态');//销售额赋内容
 	$objPHPExcel->getActiveSheet(0)->setCellValue('Q1', '操作');//销售额赋内容
 	
 	
 	foreach ($arract as $key1=>$val1)
 	{	
 		if($key1 ==0)
 		{
 			
 		
 		$objPHPExcel->getActiveSheet(0)->setCellValue("A".($key1+2), $val1['uname']);//销售额赋内容
 		$objPHPExcel->getActiveSheet(0)->setCellValue("B".($key1+2), $val1['umobile']);//销售额赋内容
 		$objPHPExcel->getActiveSheet(0)->setCellValue("C".($key1+2), $val1['yecount']);//销售额赋内容
 		$objPHPExcel->getActiveSheet(0)->setCellValue("D".($key1+2), $val1['ticount']);//销售额赋内容
 		$objPHPExcel->getActiveSheet(0)->setCellValue("E".($key1+2), $val1['y']);//销售额赋内容
 		$objPHPExcel->getActiveSheet(0)->setCellValue("F".($key1+2), $val1['w']);//销售额赋内容
 		foreach ($val1['count'] as $kks=>$vvs)
 		{
 			$objPHPExcel->getActiveSheet(0)->setCellValue("G".($key1+3), $vvs['tname']);//销售额赋内容
 		}
 			$acr = $key1+3;
 		}else
 		{
 			$objPHPExcel->getActiveSheet(0)->setCellValue("A".($acr+1), $val1['uname']);//销售额赋内容
 			$objPHPExcel->getActiveSheet(0)->setCellValue("B".($acr+1), $val1['umobile']);//销售额赋内容
 			$objPHPExcel->getActiveSheet(0)->setCellValue("C".($acr+1), $val1['yecount']);//销售额赋内容
 			$objPHPExcel->getActiveSheet(0)->setCellValue("D".($acr+1), $val1['ticount']);//销售额赋内容
 			$objPHPExcel->getActiveSheet(0)->setCellValue("E".($acr+1), $val1['y']);//销售额赋内容
 			$objPHPExcel->getActiveSheet(0)->setCellValue("F".($acr+1), $val1['w']);//销售额赋内容
 			 $i = 0;
 			foreach ($val1['count'] as $kks=>$vvs)
 			{
 				$objPHPExcel->getActiveSheet(0)->setCellValue("G".($acr+2+$i), $vvs['tname']);//销售额赋内容
 				$objPHPExcel->getActiveSheet(0)->setCellValue("H".($acr+2+$i), $vvs['tmobile']);//销售额赋内容
 				$objPHPExcel->getActiveSheet(0)->setCellValue("I".($acr+2+$i), $vvs['tnote']);//销售额赋内容
 				$objPHPExcel->getActiveSheet(0)->setCellValue("J".($acr+2+$i), $vvs['times']);//销售额赋内容
 				if($vvs['ticket'] !=0)
 				{
 					
 					if($vvs['ptype']  == 0)
 					{
 						$objPHPExcel->getActiveSheet(0)->setCellValue("G".($acr+2+$i), $vvs['tname']);//销售额赋内容
 					}else if($vvs['ptype']  == 1)
 					{
 						$objPHPExcel->getActiveSheet(0)->setCellValue("G".($acr+2+$i), $vvs['tname']);//销售额赋内容
 					}else
 					{
 						$objPHPExcel->getActiveSheet(0)->setCellValue("G".($acr+2+$i), $vvs['tname']);//销售额赋内容
 					}
 				}
 				$i++;
 			}
 			
 			$acr = $acr+1+$i;
 		}
 	} 
 	
 	
 	
 	
 	header('pragma:public');
 	header('Content-type:application/vnd.ms-excel;charset=utf-8;name=$fileName.xls');
 	header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
 	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 	$objWriter->save('php://output');
 	exit;
 }
 
public function dateindex(){
	    $eid = I('post.eid');
	    if($eid){
            $uid  = $eid;	//分公司登录用户 
			$info = M('enterprise')
				->where(array('id'=>$uid))
				->select();	//查询企业信息
	    }else{
	    	$id = session("uid");
	        $type = session("type");
	        if($type==10){
	            $admin = M('admin')->where(array('pid'=>$id,'type'=>3))->select();//企业登陆
	            foreach($admin as $k => $v){
	                $shopid[$k]  = $v['id'];
	        }
	        $data['aid']= array(array('in', $shopid));
	        $info = M('enterprise')
					->where($data)
					->select();
	        }else if($type==9){
	            $admin = M('admin')->where(array('id'=>$id,'type'=>3))->find();//大区登陆
	            $info = M('enterprise')
					->where(array('aid'=>$admin['id']))
					->select();
	        }else{
	        	$uid  = 79;	//分公司登录用户 
				$info = M('enterprise')
					->where(array('id'=>$uid))
					->select();	//查询企业信息
	        }
	        foreach($info as $k => $v){
					$uid[] = $v['id'];
				}
	    }
	    
        	 
				// echo '<pre>';print_r($info);die;
			$db = C('DB_PREFIX');
			
			
			
			$ini['o.qid'] = array('in',$uid);
			$timeyear = I('post.timeyear');
			$timemonth = I('post.timemonth');
			$timeday = I('post.timeday'); 
			$beforeyea = I('post.beforeyea');
			$datayear = I('post.datayear');
			$day = I('post.day');
			$lsor =  I('post.lsor');
			if($timeyear == 0 || $timemonth == 0 || $timeday == 0)
			{
				$ktimes = $beforeyea.'-'.$datayear.'-'.'1';
				$jtimes = $beforeyea.'-'.$datayear.'-'.$day;
			}else
			{
				$ktimes = $timeyear.'-'.$timemonth.'-'.$timeday;
				$jtimes = $beforeyea.'-'.$datayear.'-'.$day;
			}
			
			//$ini['o.times'] = array('lt',$ktimes);
			$ini['o.times'] = array(array('lt',$jtimes),array('gt',$ktimes));
			$ini['o.qid'] =array('in',$uid);
			if(!empty($lsor))
			{
				$ini['u.name'] = array('like',"%$lsor%");
			}
		 	// $student = M('student')->where(array('qid'=>$uid))->select();//学员数据 
			$a = M('order as o')->join($db.'user as u on o.uid=u.id','left')
			->join($db.'student as t on o.sid = t.id ','left')
			->where($ini)
			->field('o.qid as qids ,o.id as id , o.times as times ,u.name as uname ,u.id as uuid, u.mobile as umobile, t.mobile as tmobile , t.note as tnote , t.name as tname,o.ticket as ticket,o.card as card,o.friends as friends,o.planning as planning,o.disciple as disciple,o.ptype,o.ctype,o.ftype,o.dtype,o.ntype ')
			->select();
			
			 // echo '<pre>';print_r($a);die;
			
			foreach ($a as $k=>$v)
			{	
			  	$arr[$v['uuid']]['umobile']= $v['umobile'];
				$arr[$v['uuid']]['uname'] = $v['uname'];  
				$arr[$v['uuid']]['count'][$k] = $v;
				
			}
		 	foreach ($arr as $k1=>$v1)
			{		
				$hay = "";
				$tiy ="";
				$yiaction = "";
				$waction = "";
				$yiaction2 = "";
				$waction2 = "";
				$yiaction3= "";
				$waction3 = "";
				$yiaction4 = "";
				$waction4 = "";
				$yiaction5 = "";
				$waction5 = "";
				
				foreach ($v1['count'] as $k2=>$v2)
				{			
					
				foreach ($info as $k3=>$v3){
					
				
					if($v3['id'] == $v2['qids']){
						
						$pdj = round(($v3['leafletstc']/100)*$v3['tc'],2);
						$kdj = round(($v3['leafletscc']/100)*$v3['cc'],2);
						$qdj = round(($v3['leafletsfc']/100)*$v3['fc'],2);
						$ddj = round(($v3['leafletsdc']/100)*$v3['dc'],2);
						$ndj = round(($v3['leafletsnc']/100)*$v3['nc'],2);
						if($v2['ticket'] !=0)
						{
							$count1 = 1;
						}
						if($v2['card'] !=0)
						{
							$count1++;
						}
						if($v2['friends'] !=0)
						{
							$count1++;
						}
						if($v2['planning'] !=0)
						{
							$count1++;
						}
						if($v2['disciple'] !=0)
						{
							$count1++;
						}
					 	$arr[$k1]['count'][$k2]['pcount'] = $v2['ticket']*$pdj;
						$arr[$k1]['count'][$k2]['kcount'] = $v2['card']*$kdj;
						$arr[$k1]['count'][$k2]['qcount'] = $v2['friends']*$qdj;
						$arr[$k1]['count'][$k2]['dcount'] = $v2['planning']*$ddj;
						$arr[$k1]['count'][$k2]['ncount'] = $v2['disciple']*$ndj;
						$arr[$k1]['count'][$k2]['pil'] = $v3['tc'];
						$arr[$k1]['count'][$k2]['kil'] = $v3['cc'];
						$arr[$k1]['count'][$k2]['qil'] = $v3['fc'];
						$arr[$k1]['count'][$k2]['dil'] = $v3['dc'];
						$arr[$k1]['count'][$k2]['nil'] = $v3['nc'];
						$arr[$k1]['count'][$k2]['cssbiao'] = $count1;
						$arr[$k1]['count'][$k2]['ticketall'] = $v2['ticket']*$v3['leafletstc'];
						$arr[$k1]['count'][$k2]['cardall'] = $v2['card']*$v3['leafletscc'];
						$arr[$k1]['count'][$k2]['friendsall'] =  $v2['friends']*$v3['leafletsfc'];
						$arr[$k1]['count'][$k2]['planningall'] = $v2['planning']*$v3['leafletsnc'];
						$arr[$k1]['count'][$k2]['discipleall'] = $v2['disciple']*$v3['leafletsdc'];
						$hay += $arr[$k1]['count'][$k2]['ticketall']+$arr[$k1]['count'][$k2]['cardall']+$arr[$k1]['count'][$k2]['friendsall']+$arr[$k1]['count'][$k2]['planningall']+$arr[$k1]['count'][$k2]['discipleall'];
						$tiy += ($v2['ticket']*$pdj)+($v2['card']*$kdj)+($v2['friends']*$qdj)+($v2['planning']*$ddj)+($v2['disciple']*$ndj);
						if($v2['ptype'] == 1 )
						{
							$yiaction +=  $v2['ticket']*$pdj;
						}else if($v2['ptype'] == 2 )
						{
							$waction +=  $v2['ticket']*$pdj;
						}else
						{
							$yiaction =0;
							$waction = 0;
						}
						
						if($v2['ctype'] == 1 )
						{
							$yiaction2 +=  $v2['card']*$kdj;
						}else if($v2['ctype'] == 2 )
						{
							$waction2 +=  $v2['card']*$kdj;
						}else
						{
							$yiaction2 =0;
							$waction2 = 0;
						}
						
						if($v2['ftype'] == 1 )
						{
							$yiaction3 +=  $v2['friends']*$qdj;
						}else if($v2['ftype'] == 2 )
						{
							$waction3 +=  $v2['friends']*$qdj;
						}else
						{
							$yiaction3 =0;
							$waction3 = 0;
						}
						
						if($v2['dtype'] == 1 )
						{
							$yiaction4 +=  $v2['disciple']*$ddj;
						}else if($v2['dtype'] == 2 )
						{
							$waction4 +=  $v2['disciple']*$ddj;
						}else
						{
							$yiaction4 =0;
							$waction4 = 0;
						}
						
						if($v2['ntype'] == 1 )
						{
							$yiaction5 +=  $v2['planning']*$ndj;
						}else if($v2['ntype'] == 2 )
						{
							$waction5 +=  $v2['planning']*$ndj;
						}else
						{
							$yiaction5 =0;
							$waction5 = 0;
						}
						
				}	
				}
				}
				
				$arr[$k1]['yecount'] = $hay; //业绩
				$arr[$k1]['ticount'] = $tiy; //金额
		 		$arr[$k1]['y'] += $yiaction+$yiaction2+$yiaction3+$yiaction4+$yiaction5;  //未发
				$arr[$k1]['w'] += $waction+$waction2+$waction3+$waction4+$waction5;  //已发
			
			} 
			
			echo json_encode($arr);
		}
		
		function adddat()
		{
			$act = I('post.act');
			$id = I('post.id');
			$r = I('post.r');
			if($r == 1)
			{	
				$order =M('order')->where(array('id'=>$id))->save(array('ptype'=>$act));
				
			}else if($r == 2)
			{
				$order =M('order')->where(array('id'=>$id))->save(array('ntype'=>$act));
			}else if($r == 3)
			{
				$order =M('order')->where(array('id'=>$id))->save(array('ctype'=>$act));
			}else if($r == 4)
			{
				$order = M('order')->where(array('id'=>$id))->save(array('ftype'=>$act));
			}else if($r == 5)
			{
				$order = M('order')->where(array('id'=>$id))->save(array('dtype'=>$act));
			}
			if($order !== false)
			{
				echo json_encode("1");
			}else
			{
				echo json_encode("2");
			}
		}

	public function lised(){
		//分公司副总经理列表
		//$id = 70;
        $p = I('post.page');
        $type = I('post.type');
        $role=I('get.role');
        $limit = 6;

        $first = ($p-1) * $limit;
        $user = M('user')->field('account,pwd,mobile,name,id')->limit($first,$limit)->where(array('role'=>$role))->order('id desc')->select();//先不安uid区分，全排出来
        echo json_encode($user); 
	}
	public function editpwd(){
		$id = I('post.id');
        $newpwd = md5(I('post.newpwd'));
        $oldpwd = md5(I('post.oldpwd'));
        //修改员工的密码
        	$pwd = M('user')->where(array('id'=>$id))->find();
        	if($pwd['pwd'] == $oldpwd){
                $data['pwd'] = $newpwd;                
                $user =  M('user')->where(array('id'=>$id))->save($data);
                if(!empty($user)){
                    $result['errcode'] = 1;
                    $result['errmsg'] ='修改密码成功';
                }else{
                     $result['errcode'] = 0;
                     $result['errmsg'] ='修改密码失败';
                }
            }else{
                    $result['errcode'] = 3;
                    $result['errmsg'] ='旧密码错误';
            }    
            echo json_encode($result);    
	}
	//删除员工
	public function delacc(){
		        $id = I('post.id');          
                $delname = M('user')->where(array('id'=>$id))->delete();
                if($delname){
                    $result['errcode'] = 1;
                    $result['errmsg'] ='删除成功';
                }else{
                    $result['errcode'] = 2;
                    $result['errmsg'] ='删除失败';
                }
                echo json_encode($result);
           }
           
	
	//分页

	public function page(){
	    $uid=session("id");
      
//         if($_SESSION["type"]==10){
//             $adsel=M('admin')->where(array("pid"=>$uid))->getField("id",true);
//             if(empty($adsel)){
//             	$result['errcode'] = 3;
//             	die;
//             }
//             $aid=implode(",", $adsel);
//             $users = M('user')->field('account,pwd,mobile,name,id')->where("prid in ($aid)")->where(array('role'=>7))->order('id desc')->count();//先不安uid区分，全排出来
          
//         }else if($_SESSION["type"]==9){
//             $users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>7,'prid'=>$uid))->order('id desc')->count();//先不安uid区分，全排出来
           
//         }
        $users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>7,'uid'=>getcompanyId()))->order('id desc')->count();//先不安uid区分，全排出来
        $users = ceil($users/6);
       
        echo json_encode($users);
    }
    
    public function page6(){
        $uid=session("id");
        
//        if($_SESSION["type"]==10){
//            $adsel=M('admin')->where(array("pid"=>$uid))->getField("id",true);
//            if(empty($adsel)){
//            	$result['errcode'] = 23;
//            	die;
//            }
//            $aid=implode(",", $adsel);
//            $users = M('user')->field('account,pwd,mobile,name,id')->where("prid in ($aid)")->where(array('role'=>6))->order('id desc')->count();//先不安uid区分，全排出来
//        }else if($_SESSION["type"]==9){
//            $users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>6,'prid'=>$uid))->order('id desc')->count();//先不安uid区分，全排出来
//        }
		$users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>6,'uid'=>getcompanyId()))->order('id desc')->count();//先不安uid区分，全排出来
        $users = ceil($users/6);
        echo json_encode($users);
    }
    
    public function page5(){
        $uid=session("id");
        
//        if($_SESSION["type"]==10){
//            $adsel=M('admin')->where(array("pid"=>$uid))->getField("id",true);
//            if(empty($adsel)){
//            	$result['error'] = 3;
//            	die;
//            }
//
//            $aid=implode(",", $adsel);
//
//            $users = M('user')->field('account,pwd,mobile,name,id')->where("prid in ($aid)")->where(array('role'=>5))->order('id desc')->count();//先不安uid区分，全排出来
//        }else if($_SESSION["type"]==9){
//            $users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>5,'prid'=>$uid))->order('id desc')->count();//先不安uid区分，全排出来
//        }
		$users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>5,'uid'=>getcompanyId()))->order('id desc')->count();//先不安uid区分，全排出来
        $users = ceil($users/6);
        echo json_encode($users);
    }

        public function page9(){
        $uid=session("id");
//        if($_SESSION["type"]==10){
//            $adsel=M('admin')->where(array("pid"=>$uid))->getField("id",true);
//            if(empty($adsel)){
//            	$result['errcode'] = 3;
//            	die;
//            }
//            $aid=implode(",", $adsel);
//
//            $users = M('user')->field('account,pwd,mobile,name,id')->where("prid in ($aid)")->where(array('role'=>8))->order('id desc')->count();//先不安uid区分，全排出来
//
//        }else if($_SESSION["type"]==9){
//            $users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>8,'prid'=>$uid))->order('id desc')->count();//先不安uid区分，全排出来
//
//        }

		$users = M('user')->field('account,pwd,mobile,name,id')->where(array('role'=>8,'uid'=>getcompanyId()))->order('id desc')->count();//先不安uid区分，全排出来
        $user = ceil($users/6);
        echo json_encode($user);
    }
    //修改账号
    public function savacc(){
     	$id = I('post.id');
         $val = I('post.val');       
         $data['account'] = $val;
         $account = M('user')->where(array('account'=>$val))->select();
         if($account){
              $result['errcode'] = 3;
              $result['errmsg'] ='企业账号已被占用';
              echo json_encode($result);die;
         }else{
              M('user')->where(array('id'=>$id))->save($data);
          }
      
         $result['errcode'] = 1;
         $result['errmsg'] ='修改成功';
         echo json_encode($result);
    }

    // public function savname(){
    // 	 $id = I('post.id');
    //      $val = I('post.val');
    //      $data['name'] = $val;
    //      M('user')->where(array('id'=>$id))->save($data);     
    //      $result['errcode'] = 1;
    //      $result['errmsg'] ='修改成功';
    //      echo json_encode($result);
    // }
    public function showchekd(){
   //  $id = I('post.id');
         $id = session("id");
         $type=session("type");
         // echo '<pre>';print_r($type);die;
         if($type==10||$type==11||$type==12||$type==13){
    //          $account = M('admin')->where(array('pid'=>$id,'type'=>3))->select();
    //          echo '<pre>';print_r($account);die;
    //          foreach($account as $k => $v){
    //              $shopid[$k]  = $v['id'];
    //          }
			 // if(empty($shopid)){
				//  $result["error"]=1;
				//  $result["msg"]="您还没有分公司";
				//  echo json_encode($result);
				//  exit();
			 // }
    //          $where['aid'] = array(array('in', $shopid));
             $enterprise = M('enterprise')->where(array("id"=>getcompanyId()))->select();
              // echo '<pre>';print_r($enterprise);die;
             $result['acc'] = $account;
             $result['ent'] = $enterprise;
         }else if($type==9){
             $where['aid'] = $id;
             $enterprise = M('enterprise')->where($where)->select();
             $result['acc'] = $id;
             $result['ent'] = $enterprise;
         }else{
             $userfind=M('user')->where(array('id'=>$id))->find();
             $result['acc'] = $userfind["prid"];
             $result['ent'] = $userfind["uid"];
         }
        
        
         $result['status'] = $type;
         //$result['acc'] = $account;
        
         //$result['ent'] = $enterprise;
         
         echo json_encode($result); 
   }

   // public function savmob(){
   // 	$mobile = I('post.dmob');
   // 	$id = I('post.id');
   // 	$usermoble = M('user')->where(array('mobile'=>$mobile))->find();
   // 	if($usermoble){
   // 		$result['errcode'] = 3;
   // 		$result['errmsg'] = '手机号已被占用';
   // 		echo json_encode($result);die;
   // 	}else{
   // 		$data['mobile'] = $mobile;
   // 		M('user')->where(array('id'=>$id))->save($data);
   // 	}
 		// $result['errcode'] = 1;
   // 		$result['errmsg'] = '手机号修改成功';
   // 		echo json_encode($result);

   // }
   	public function saveacc(){
   		$id = I('post.id');
   		$user = M('user')->where(array('id'=>$id))->find();
   		echo json_encode($user);
   		// echo '<pre>';print_r($user);die;
   	}

   	//修改信息
   	public function saveinfo(){
   		$id = I('post.id');
   		$arr['name'] = I('post.adname');
   		$arr['mobile'] = I('post.admob');
   		$arr['account'] = I('post.adacc');
   		$arr['lectype'] = I('post.jwyes');//讲师
   		$arr['finatype'] = I('post.cwnone');//财务
		$account = M('user')->where(array('account'=>$arr['account']))->find();
		$accountid = M('user')->where(array('account'=>$arr['account'],'id'=>$id))->find();
	    $mobile = M('user')->where(array('mobile'=>$arr['mobile']))->find();
	    $mobileid = M('user')->where(array('mobile'=>$arr['mobile'],'id'=>$id))->find();

   		if($accountid['account'] == I('post.adacc') && $accountid['name'] == I('post.adname') && $accountid['mobile'] == I('post.admob') && $accountid['lectype'] == I('post.jwyes') && $accountid['finatype'] == I('post.cwnone')){
   			    $result['errcode'] = 10;
   			    $result['errmsg'] = '未做任何修改';
   			    echo json_encode($result);die;
   		}
   		// echo '<pre>';print_r($accountid);
   		
   		if(empty($account) || $accountid){
   			if(empty($mobile) || $mobileid){
   				$user = M('user')->where(array('id'=>$id))->save($arr);
   				$result['errcode'] = 1;
   			    $result['errmsg'] = '修改成功';
   				
   			}else{
   				$result['errcode'] = 3;
   			    $result['errmsg'] = '手机号已被占用';
   			}
   		}else{
   			$result['errcode'] = 0;
   			$result['errmsg'] = '账号已被占用';
   		}
   		echo json_encode($result);
   	}
    public function uploadCate() 
    {
        $uid = session('uid');
        $getid = 1;
        // $getid = I('post.getid');   //企业id
        // $map['id'] = $uid;
        // $map['uid'] = $getid;
        // $map['type'] = 2;
        // $cstatus = M('user')->where($map)->find();
        // unset($map);
        // if(empty($cstatus)){
        //     $data['code'] = 0;
        //     $data['errmsg'] = '您没有操作权限！';
        //     echo json_encode($data);
        //     die();
        // }
        //Excel配置文件
        $config = array(
            'maxSize'    =>    553145728,
            'rootPath'   =>    './Public/Uploads/',
            'savePath'   =>    $uid.'jexcel/',    
            'saveName'   =>    array('uniqid'),
            'exts'       =>    array('xls','xlsx'),
            'autoSub'    =>    true,
            'replace'    =>    true,
            'subName'    =>    false,
        );

        $upload = new \Think\Upload($config);// 实例化上传类

        $info   =   $upload->upload();

        if(!$info){
            $res['errcode'] = "0";
            $res['errmsg'] = "上传失败";
            return $res;
        }

        import("Org.Util.PHPExcel");
        $filename = "./Public/Uploads/".$uid."jexcel/".$info['file']['savename'];

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
        unlink("./Public/Uploads/".$uid."jexcel/".$info['file']['savename']);
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

        array_shift($excelDatas);//去数组头部

        foreach ($excelDatas as $key => $value) {  //检测日期格式
            if(strlen($value[0]) < 5){
                $hang = $key+2;
                $data['code'] = 0;
                $data['errmsg'] = '第'.$hang.'行日期格式不正确！';
                $data['sample'] = '格式:例如2017年1月1日';
                echo json_encode($data);
                die();
            }else{
                $excelDatas[$key][0] = date('Y-m-d',$shared->ExcelToPHP($value[0]));
            }
        }

        foreach ($excelDatas as $key => $value) {
            if($value[0] == '' )
            {
                unset($excelDatas[$key]);
            }
        }

        foreach ($excelDatas as $key => $value) {
            $datalist[$key]['time'] = $value[0];
            $datalist[$key]['jname'] = $value[1];
            $datalist[$key]['jphone'] = $value[2];
            $datalist[$key]['xname'] = $value[3];
            $datalist[$key]['xphone'] = $value[4];
            $datalist[$key]['type'] = $value[5];
            $datalist[$key]['num'] = $value[6];
            $datalist[$key]['amount'] = $value[7];
        }

        foreach ($datalist as $key => $value) { //检测学员姓名格式
            $hang = $key+2;
            if(!empty($value['jname']) && !empty($value['xname']) && !empty($value['xphone']) && !empty($value['jphone']) && !empty($value['type']) && !empty($value['num']) && !empty($value['amount'])){  //进账检测数据
                if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $value['xname'])){
                    $data['code'] = 0;
                    $data['errmsg'] = '第'.$hang.'行学员姓名格式不正确！';
                    $data['sample'] = '格式:例如"张三"';
                    echo json_encode($data);
                    die();
                }
                if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $value['jname'])){
                    $data['code'] = 0;
                    $data['errmsg'] = '第'.$hang.'行讲师姓名格式不正确！';
                    $data['sample'] = '格式:例如"张三"';
                    echo json_encode($data);
                    die();
                }
                if(!preg_match("/^(1(([35][0-9])|(47)|[8][0126789]))\d{8}$/", $value['xphone'])){
                    $data['code'] = 0;
                    $data['errmsg'] = '第'.$hang.'行学员手机号格式不正确！';
                    $data['sample'] = '格式:例如15804652776';
                    echo json_encode($data);
                    die();
                }
                if(!preg_match("/^(1(([35][0-9])|(47)|[8][0126789]))\d{8}$/", $value['jphone'])){
                    $data['code'] = 0;
                    $data['errmsg'] = '第'.$hang.'行讲师手机号格式不正确！';
                    $data['sample'] = '格式:例如15804652776';
                    echo json_encode($data);
                    die();
                }
                if($value['type'] == '小票' || $value['type'] == '卡' || $value['type'] == '朋友圈' || $value['type'] == '弟子学员' || $value['type'] == '九大规划门票'){ //类别验证
	                if(is_numeric($value['num'])){
						if($value['type'] == '小票'){
		                    $datalist[$key]['type'] = 1;
		                }else if($value['type'] == '卡'){
		                    $datalist[$key]['type'] = 2;
		                }else if($value['type'] == '朋友圈'){
		                    $datalist[$key]['type'] = 3;
		                }else if($value['type'] == '弟子学员'){
		                    $datalist[$key]['type'] = 5;
		                }else if($value['type'] == '九大规划门票'){
		                    $datalist[$key]['type'] = 4;
		                }else{
		                    $data['code'] = 0;
		                    $data['errmsg'] = '第'.$hang.'行类别不正确！';
		                    $data['sample'] = '格式:例如"小票"';
		                    echo json_encode($data);
		                    die();
		                }
	                }else{
	                    $data['code'] = 0;
	                    $data['errmsg'] = '第'.$hang.'行数量格式不正确！';
	                    $data['sample'] = '格式:例如"1"';
	                    echo json_encode($data);
	                    die();
	                }
                }else{
                    $data['code'] = 0;
                    $data['errmsg'] = '第'.$hang.'行类别不正确！';
                    $data['sample'] = '格式:例如"票"';
                    echo json_encode($data);
                    die();
                }
                $datalist[$key]['qid'] = $getid;
                $map['name'] = $value['jname'];
                $map['mobile'] = $value['jphone'];
                $map['type'] = 1;
                // $map['uid'] = $getid;	//企业id
                $jid = M('user')->field('id,uid')->where($map)->find();	//验证讲师信息
                unset($map);
                if(!empty($jid)){
                	$datalist[$key]['tid'] = $jid['id'];
                	unset($datalist[$key]['jname']);
                	unset($datalist[$key]['jphone']);
                }else{
	                $data['code'] = 0;
	                $data['errmsg'] = '第'.$hang.'行讲师信息不正确！';
	                $data['sample'] = '请仔细检查';
	                echo json_encode($data);
	                die();
                }

                $map['name'] = $value['xname'];
                $map['mobile'] = $value['xphone'];
                $map['qid'] = $jid['uid'];
                $map['uid'] = $jid['id'];
                $xid = M('student')->field('id')->where($map)->find();	//验证学员信息
                unset($map);
                if(!empty($jid)){
                	$datalist[$key]['sid'] = $xid['id'];
                	unset($datalist[$key]['xname']);
                	unset($datalist[$key]['xphone']);
                }else{
	                $data['code'] = 0;
	                $data['errmsg'] = '第'.$hang.'行学员信息不正确！';
	                $data['sample'] = '请仔细检查';
	                echo json_encode($data);
	                die();
                }              
            }else{
                $data['code'] = 0;
                $data['errmsg'] = '第'.$hang.'行数据缺失！';
                $data['sample'] = '请仔细检查';
                echo json_encode($data);
                die();
            }
        }
        if(!empty($datalist)){
            $data['code'] = 1;
            $data['datalist'] = $datalist;
        }else{
            $data['code'] = 0;
            $data['errmsg'] = '表格数据为空！';
        }
        echo json_encode($data);
    }
   public function uploadCategory(){
        $uid = session('uid');
        // $getid = I('post.getid');   //企业id
        $excelDatas = I('post.datalist');

        if(!empty($excelDatas)){
        	foreach ($excelDatas as $key => $value) {
        		if($value['type'] == 5)
        		{
        			$map['tid'] = $value['tid'];
        			$map['qid'] = $value['qid'];
        			$map['sid'] = $value['sid'];
        			$map['type'] = $value['type'];
        			$map['amount'] = $value['amount'];
        			$status = M('orders')->where($map)->find();
        			unset($map);
        			if(!empty($status)){
        				$map['id'] = $status['id'];
        				$ldata['times'] = $value['times'];
        				$ldata['amount'] = $value['amount']+$status['amount'];
        				$ldata['num'] = $value['num']+$status['num'];
        				$ostatus = M('orders')->where($map)->save($ldata);
        				if($ostatus){
        					unset($excelDatas[$key]);
        				}
        			}
        		}
        	}
            //事务处理（对数据库进行添加操作和修改操作）
            M()->startTrans(); 
            foreach ($excelDatas as $key => $value) {
                $data = M('bill')->add($value);
                $excelDatas[$key]['id'] = $data;
                if(!$data)
                {
                    M()->rollback();
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

    //下拉
   public function usekl(){
   	     $id = session("uid");
         $type=session("type");
          if($type==10){
               $admin = M('admin')->where(array('pid'=>$id,'type'=>3))->select();//企业登陆
	        }else if($type==9){
	            $admin = M('admin')->where(array('id'=>$id,'type'=>3))->select();//大区或许财务总监登陆
	        }
          foreach($admin as $k => $v){
                $shopid[$k]  = $v['id'];
	        }
        if(empty($shopid)){
        	$result['errcode'] = 3;
        	$result['errmsg'] = '企业旗下尚未有分公司';
        	echo json_encode($result);die;
        }
	     $where['aid']= array(array('in', $shopid));
         $enterprise = M('enterprise')->where($where)->select();
         echo json_encode($enterprise);
	        // echo '<pre>';print_r($enterprise);

   }
   //一个讲师对应多个学员
   public function ename(){
   	$eid = I('post.id');//讲师id
   	$lsor =  I('post.lsor');//搜索内容

   	$timeyear = I('post.timeyear');
	$timemonth = I('post.timemonth');
	$timeday = I('post.timeday'); 
	$beforeyea = I('post.beforeyea');
	$datayear = I('post.datayear');
	$day = I('post.day');
	$eid = 229;
	if($timeyear == 0 || $timemonth == 0 || $timeday == 0){
				$ktimes = $beforeyea.'-'.$datayear.'-'.'1';
				$jtimes = $beforeyea.'-'.$datayear.'-'.$day;
			}else{
				$ktimes = $timeyear.'-'.$timemonth.'-'.$timeday;
				$jtimes = $beforeyea.'-'.$datayear.'-'.$day;
			}
			
			// $ini['o.times'] = array('lt',$ktimes);
			$where['o.times'] = array(array('elt',$jtimes),array('egt',$ktimes));
			$ini['u.id'] = $eid;
			$ini['u.lectype'] = 1;
   

   	$tea = M('user as u')
			   	->join(C('DB_PREFIX').'enterprise as e on u.uid = e.id')
			   	->where($ini)
			   	->field('u.name as jname,u.mobile as jmobile,u.uid as uid,e.tc as tc,e.cc as cc,e.fc as fc,e.nc as nc,e.dc as dc,e.leafletstc,e.leafletscc,e.leafletsfc,e.leafletsdc,e.leafletsnc')
			   	->find();
			   		// echo '<pre>';print_r($tea);
	if(!empty($lsor)){
				$where['s.name'] = array('like',"%$lsor%");
			}
   	$where['s.qid'] =  $tea['uid'];//分公司id
   	$where['s.uid'] = $eid;//讲师id
   	$employ = M('student as s')
			   	->join(C('DB_PREFIX').'orders as o on o.sid = s.id','left')
			   	->where($where)
			   	->select();

	// echo '<pre>';print_r($employ);

   	$yf = 0;
   	$wf = 0;
	foreach($employ as $key => $val){
		$tea['perfor'] += $val['amount'];

		$tea['deduct'] += $val['num']*$tea['tc']*$val['amount']/100;
		if($employ[$key]['status'] == 1){
			$tea['yifa'] += $yf+1;
		}

	    if($employ[$key]['status'] == 0){
			$tea['weifa'] += $wf+1;
		}
		foreach($employ as $k => $v){
			$tea['data'][$key]['sid'] = $employ[$key]['sid'];
			$tea['data'][$key]['mobile'] = $employ[$key]['mobile'];
			$tea['data'][$key]['name'] = $employ[$key]['name'];
			$tea['data'][$key]['note'] = $employ[$key]['note'];
			$tea['data'][$key]['times'] = $employ[$key]['times'];
			
			if($employ[$key]['type'] == 1){
				$tea['data'][$key]['ticket'] = $employ[$key]['num'];//各种票卡的数量
				$tea['data'][$key]['ptype'] = $employ[$key]['status'];//各种票卡的状态
				$tea['data'][$key]['tamount'] = $employ[$key]['amount'];//各种票卡的金额
			}
			if($employ[$key]['type'] == 2){
				$tea['data'][$key]['card'] = $employ[$key]['num'];
				$tea['data'][$key]['ctype'] = $employ[$key]['status'];
				$tea['data'][$key]['camount'] = $employ[$key]['amount'];
			}
			if($employ[$key]['type'] == 3){
				$tea['data'][$key]['friends'] = $employ[$key]['num'];
				$tea['data'][$key]['ftype'] = $employ[$key]['status'];
				$tea['data'][$key]['famount'] = $employ[$key]['amount'];
			}
			if($employ[$key]['type'] == 4){
				$tea['data'][$key]['planning'] = $employ[$key]['num'];
				$tea['data'][$key]['ntype'] = $employ[$key]['status'];
				$tea['data'][$key]['pamount'] = $employ[$key]['amount'];
			}
			if($employ[$key]['type'] == 5){
				$tea['data'][$key]['disciple'] = $employ[$key]['num'];
				$tea['data'][$key]['dtype'] = $employ[$key]['status'];
				$tea['data'][$key]['damount'] = $employ[$key]['amount'];
			}
			$tea['data'][$key]['type'] = $employ[$key]['type'];
		}
		    $ropan = 0;
		    if($employ[$key]['num'] != 0){
					$tea['data'][$key]['ropan'] += $ropan +1;
				}


	} 
	
    $array=array();
    $id=array();
    $key=0;
	foreach($tea['data'] as $k=>$val){
		//$key=array_search($val["sid"],$id);
	    unset($tea['data']);
		if(!in_array($val["sid"],$id)){
			$id[]=$val["sid"];
			$tea['data1'][$k]['mobile']=$val['mobile'];
	        $tea['data1'][$k]['name']=$val['name'];
	        $tea['data1'][$k]['note']=$val['note'];
	        $tea['data1'][$k]['times']=$val['times'];
	        $key=$k;
		}
        switch ($val['type']) {
        	case '1':
        		$tea['data1'][$key]['ticket']=$val['ticket']; 
				$tea['data1'][$key]['ptype']=$val['ptype']; 
				$tea['data1'][$key]['tamount']=$val['tamount']; 
				$tea['data1'][$key]['ropan'] += $val['ropan']; 
        		break;
        	case '2':
        		$tea['data1'][$key]['card']=$val['card']; 
				$tea['data1'][$key]['ctype']=$val['ctype']; 
				$tea['data1'][$key]['camount']=$val['camount']; 
				$tea['data1'][$key]['ropan'] += $val['ropan']; 
        		break;
        	case '3':
        		$tea['data1'][$key]['friends']=$val['friends']; 
				$tea['data1'][$key]['ftype']=$val['ftype']; 
				$tea['data1'][$key]['famount']=$val['famount']; 
				$tea['data1'][$key]['ropan'] += $val['ropan']; 
        		break;
        	case '4':
        		$tea['data1'][$key]['planning']=$val['planning']; 
				$tea['data1'][$key]['ntype']=$val['ntype']; 
				$tea['data1'][$key]['pamount']=$val['pamount']; 
				$tea['data1'][$key]['ropan'] += $val['ropan']; 
        		break;
        	case '5':
        		$tea['data1'][$key]['disciple']=$val['disciple']; 
				$tea['data1'][$key]['dtype']=$val['dtype']; 
				$tea['data1'][$key]['damount']=$val['damount']; 
				$tea['data1'][$key]['ropan'] += $val['ropan']; 
        		break;			
        	
        }
	}

 echo json_encode($tea);
   	 // echo '<pre>';print_r($tea);
   	 // echo '<pre>';print_r($employ);
   	}


 } 