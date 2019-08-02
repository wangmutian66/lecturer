<?php
namespace Home\Controller;
use Think\Controller;

class AdminController extends BaseController {

	public function index(){
        if (session("?id")){
            $uid = session('id');
            $admin =  M('admin')->where(array('id'=>$uid))->find();

            if(empty($admin)){
                $this->redirect('Home/Login/login');
                 
             }else{
               
                $this->display();
             }
        }else{
            $this->redirect('Home/Login/login');
        }
		
	}
	public function adminpwd1(){
		session("rightnav","adminpwd1");
		session("nav","branch");
        
		$this->display();

	}

	public function ranking()
	{
		/* $aid = session('id'); */
		$db = C('DB_PREFIX');
		$etime=I('post.etime','');
		$stime=I('post.stime','');
		$name=I('post.lsor','');
		$type=session("type");
		$uid=session("id");
		$where='';

		$uid=getcompanyId();







		$enterprise = M('enterprise as e')
			->where(array("id"=>$uid))
			->where("e.Qyname like '%".$name."%'")
			->select();

		$numk=1;
		foreach ($enterprise as $k=>$v)
		{
			$bill=M('bill as b')->field('b.uid,b.aid as aaid,b.time,b.houston,sum(b.houston) as count')->where(array("uid"=>$v["id"]))->where('(b.time between "'.$etime.'" AND "'.$stime.'" or isnull(b.time))')->group("b.uid")->find();

			
			$enterprise[$k]["uid"]=$bill["uid"];
			$enterprise[$k]["aaid"]=$bill["aaid"];
			$enterprise[$k]["time"]=$bill["time"];
			$enterprise[$k]["houston"]=$bill["houston"];
			$enterprise[$k]["count"]=$bill["count"];
			$admin = M('admin')->where(array('id'=>$v['aid']))->find(); //找到大区总监的id
			$admin = M('admin')->where(array('id'=>$admin['pid']))->find(); //再找到企业id
			if($bill["count"]==null){
				$enterprise[$k]['count']=0;
			}
			$enterprise[$k]['riskfee']=$bill["count"]*$admin["proportion"]/100; //风险金
			$enterprise[$k]["numk"]=$numk; //排名
			$numk++;
		}

		$enterprise=$this->arr_sort($enterprise,"count","desc");
		$numk=1;
		foreach ($enterprise as $k=>$item) {
			$enterprise[$k]["numk"]=$numk; //排名
			$numk++;
		}
		echo json_encode($enterprise);
	}

	//二维数组 排序
	function arr_sort($array,$key,$order="asc"){//asc是升序 desc是降序

		$arr_nums=$arr=array();

		foreach($array as $k=>$v){

			$arr_nums[$k]=$v[$key];

		}

		if($order=='asc'){

			asort($arr_nums);

		}else{

			arsort($arr_nums);

		}


		$i=0;
		foreach($arr_nums as $k=>$v){

			$arr[$i]=$array[$k];
			$i++;
		}

		return $arr;

	}


    public function ranking1()
   {
       /* $aid = session('uid'); */
       $db = C('DB_PREFIX');
       $name=I('post.name','');
       $type=session("type");
       $uid=session("id");

     
       $search=' 1=1 ';
       if($name!=''){
           $search="e.Qyname like '%$name%'"  ;
       }
       

     $enterprise = M('enterprise as e')
       ->join($db.'bill as b on b.uid = e.id','left')
       // ->where($where)
       ->where($search)
       ->field('e.Qyname,e.aid,b.uid,b.aid as aaid,b.time,b.houston,sum(b.houston) as count,e.id as id')
       ->group("b.uid")
       ->order("count desc")
       ->select();
       
       
       
       $numk=1;
       foreach ($enterprise as $k=>$v)
       {
           

           $admin = M('admin')->where(array('id'=>$v['aid']))->find();
           
           $admin = M('admin')->where(array('id'=>$admin['pid']))->find();

         
           if($enterprise[$k]['count']==null){
               $enterprise[$k]['count']=0;
           }
           $enterprise[$k]['riskfee']=$v["count"]*$admin["proportion"]/100;
       
           $enterprise[$k]["numk"]=$numk;
           $numk++;
       }
          
       
       
       echo json_encode($enterprise);
   
   }



	public function admindata()
	{
		
		$p = I('post.page');
	    $limit = 6;
	    $first = ($p-1) * $limit;

	    $admin = M('enterprise')
	    ->limit($first,$limit)
	    ->order('id desc')
	    ->select();
	    echo json_encode($admin);	
	}
		public function deluser()
	{
		$id = I('get.id');
        $limit = 6;//每页显示条数
        $p = I("get.p");
        $first = ($p-1) * $limit;
        $enterprise = M('enterprise')->limit($first,$limit)->order('id desc')->select(); //查询一遍数据方便分页显示
        if(empty($enterprise))
        {
            $p = $p-1;
        } 
        if($id)
        {
           $lvfee = M('enterprise')->where(array('id'=>$id))->delete();
           $result['code'] = 1;
        }
        $result['p'] = $p;
        echo json_encode($result);
	}

    public function page()
	{
        $users = M("enterprise")->count();
        $users = ceil($users/6);
        echo json_encode($users);
    }
     public function page1()
    {
        $users = M("admin")->where(array('type'=>1))->count();
        $users = ceil($users/8);
        echo json_encode($users);
    }

    public function adduser()
    {
    	$ini['Qyname'] = I('post.Qyname');
    	$ini['person'] = I('post.person');
    	$ini['phone'] = I('post.phone');
    	$ini['account'] = I('post.account');
    	$ini['pwd'] = md5(I('post.pwd'));
    	$admin = M('enterprise')->where(array('account'=>I('post.account')))->find();
    	if(!empty($admin))
    	{    
    		$code = 3;
    	}
    	else
    	{
          
            $adminphone = M('enterprise')->where(array('phone'=>I('post.phone')))->find();
            if(!empty($adminphone))
            {
                echo 4;die;
            }
        
		 	$adminadd = M('enterprise')->add($ini);
	    	if($adminadd != false)
	    	{
	    		$code = 1;
	    	}
	    	else
	    	{
	    		$code = 2;
	    	}
    	}
    	echo json_encode($code);
    }
    //讲师数据  -----------------------------------------------------------------
    public function learn() 
    {
			$this->display();
    }
   
    function jsajax(){
    	$post = I('post.');
    	$page = $post['page'] ? floor($post['page']) : 1 ; //第几页
    	$type = $post['type'] ? $post['type'] : 1 ; //讲师
    	$uid4 = I('post.uid');
    	$limit = 8;//每页显示条数
    	$data = array();
    	$data['p'] = $page;
    
    	$map['type'] = $type; //讲师或财务
    	$map['uid'] = $uid4; //登录用户
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
    	->where(array('id'=>$uid4))
    	->find();	//查询企业信息

    	$pdj = round(($info['leafletstc']/100)*$info['tc'],2);
    	$kdj = round(($info['leafletscc']/100)*$info['cc'],2);
    	$qdj = round(($info['leafletsfc']/100)*$info['fc'],2);
    	$ddj = round(($info['leafletsdc']/100)*$info['dc'],2);
        //----------------------------------新增的九大规划start--------------------------------------
        $edj = round(($info['leafletsnc']/100)*$info['nc'],2);
        //----------------------------------新增的九大规划end--------------------------------------

    	$db = C('DB_PREFIX');
    	if(!empty($ids)){
    		$ini['qid'] = $uid4;
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
    		$ins['qid'] = $uid4;
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
    						$pt2[$value1['uid']] += $value1['ticket']*$pdj;//4
    					}
                      
    					if($value1['ctype'] == '1'){
    						$ct1[$value1['uid']] += $value1['card']*$kdj;
    					}

    					if($value1['ctype'] == '2'){
    						$ct2[$value1['uid']] += $value1['card']*$kdj;//4
    					}
                        
    					if($value1['ftype'] == '1'){
    						$ft1[$value1['uid']] += $value1['friends']*$qdj;
    					}

    					if($value1['ftype'] == '2'){
    						$ft2[$value1['uid']] += $value1['friends']*$qdj;//4
    					}
                         
    					if($value1['dtype'] == '1'){
    						$dt1[$value1['uid']] += $value1['disciple']*$ddj;
    					}
    					if($value1['dtype'] == '2'){
    						$dt2[$value1['uid']] += $value1['disciple']*$ddj;//4
    					}
                        
                        if($value1['ntype'] == '1'){
                            $ff[$value1['uid']] += $value1['planning']*$edj;//6
                        }

                        if($value1['ntype'] == '2'){
                            $ff2[$value1['uid']] += $value1['planning']*$edj;//6
                        }
                         
    				}
    			}
    		}
    
    		foreach($tt as $key => $val){	//每个人已发金额
    			$sums[$key] = $val + $ff[$key]+ $ct1[$key] + $ft1[$key] + $dt1[$key] + $pt[$key];
                
    		}

    		foreach($tt as $key => $val){	//每个人未发金额
    			$sums2[$key] = $val + $ct2[$key] + $ft2[$key] + $dt2[$key] + $pt2[$key]+ $ff2[$key];
    		}
  
    		foreach($tt as $key => $val){	//每个人应发发金额
    			$sums1[$key] = $val + $sums2[$key] + $sums[$key];//16
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
    
    
    public function pushpage()
    {
    	$wh = I('post.uid');
    	$users = M("user")->where(array('type'=>1,'uid'=>$wh))->count();
    	$users = ceil($users/8);
    	echo json_encode($users);
    }
	public function echange()
	{
		
		$ytime = I('post.ytime');
		$ntime = I('post.ntime');
	    $uid = I('post.uid');
		
		$t = strtotime($ytime);
		$n = strtotime($ntime);
            
		if ($t>$n) 
		{	
			$ini['error'] = 0;
			
		}else
		{
			$limit = 8;
			$p= I('post.page',1);
			$first = ($p-1) * $limit;
			$map['times'] = array(array('egt',$ytime),array('elt',$ntime));
			$map['type'] = 1;
			$map['uid'] = $uid;
			
			$student = M('user')->limit($first,$limit)->where($map)->order('id desc')->select();
			foreach ($student as $k=>$v)
			{
				$xueyuen = M('student')->where(array('uid'=>$v['id'],'qid'=>$uid))->count();
				$student[$k]['student'] = $xueyuen;
			
			}
			$studentCount = M('user')->where($map)->count();
			$ini["pagecount"]=ceil($studentCount/$limit);
			
			$ini['error'] = 1;
			$ini['data'] = $student;
			
		}
		
		echo json_encode($ini);
		
	}
       public function saveuser()
      {
    	$id = I('post.id');
    	$where['id'] = $id;
    	$qname =I('post.qname');
    	$faren = I('post.qfaren');
    	$name = I('post.name');
    	$qphone = I('post.qphone');
    	$account = I('post.qaccount');
    
      
            if($name == 'qname')
            {
                $enterprise = M('enterprise')->where($where)->setfield('Qyname',$qname);
            }
            if($name == 'qfaren')
            {
                $enterprise = M('enterprise')->where($where)->setfield('person',$faren);
            }
            if($name == 'qphone')
            {
                $ini['phone'] = $qphone;
                $enterpri = M('enterprise')->where($ini)->find();
             
                if(!empty($enterpri))
                {
                     echo 3;die;
                }
                else
                {
                    $enterprise = M('enterprise')->where($where)->setfield('phone',$qphone);
                }  
                
            }
            if($name == 'qaccount')
            {
                $ini['account'] = $account;
                $enterpri = M('enterprise')->where($ini)->find();
                if(!empty($enterpri))
                {
                     echo 4;die;
                }
                else
                {
                    $enterprise = M('enterprise')->where($where)->setfield('account',$account);
                }
               
            }
            if($enterprise != false)
            {
                $code = 1;
            }
            else
            {
                $code = 2;
            }

        
    	
    	echo json_encode($code);
   
    }
    public function editpwd()
    {
    	$id = I('post.id');
    	$oldpwd = I('post.oldpwd'); // 旧密码
    	$newpwd = I('post.newpwd'); // 新密码
    	$enterprise = M('enterprise')->where(array('id'=>$id))->find();
    	if($enterprise['pwd'] != md5($oldpwd))
    	{
    		$code = 3;
    	}
    	else
    	{
    		$ente = M('enterprise')->where(array('id'=>$id))->setfield('pwd',md5($newpwd));
		if($ente != false)
		{
			$code = 1;
		}
		else
		{
			$code = 2;
		}

    	}
		
    	
    	echo json_encode($code);
    }
    public function lister()
    {
    // $map['id'] = session('uid');
        $uid = I('get.uid');
        
        $this->assign('uid', $uid);
        $map['id'] = $uid;
        $info = M('enterprise')
        ->where($map)
        ->find();   //查询企业信息
        if(!$info){
            echo "暂无该企业！";
            die();
        }

        if($info['id']){
            $maps['qid'] = $info['id']; 
            $data = M('student')
            ->field('id,name,mobile,note')
            ->where($maps)
            ->order('id desc')
            ->select(); //企业下所有学员
		
            foreach ($data as $key => $value) {
                $ids[] = $value['id'];
            }
           
            if($ids == '')
            {
                
                echo "公司暂无信息";
                $this->display('lister');
                die();
               
            }else
            {
                 $ini['o.sid'] = array('in',$ids);
                 $db = C('DB_PREFIX');
                 $orders = M("order as o")
                            ->field('u.name,u.mobile,o.id,o.sid,o.times,o.ticket,o.card,o.friends,o.disciple,o.ptype,o.ctype,o.ftype,o.dtype,o.planning as planning,o.ntype as ntype')
                            ->where($ini)
                            ->join($db.'user as u on o.uid = u.id','left')
                            ->order('o.id desc')
                            ->select(); //查询学员相对应的订单列表与讲师信息

                $user = M('user')
                            ->field('id,name')
                            ->where(array('uid'=>$map['id']))
                            ->select(); //查询该企业下所有讲师与财务姓名
           

                $date = date('Y年m月d日');
                $this->assign('date', $date);
                $this->assign('user', $user);
                $this->assign('orders', $orders);
                $this->assign('data', $data);

            }
           
        }
      
        $this->assign('info', $info);
        $this->display();
     }

    /**
     * 讲师数据表格下载
     * @param  null
     * @return void
     */
    function jreport(){
        $get = I('get.uid');
        $map['uid'] = $get;
        $map['type'] = 1;
        $data = jexcel($map);
        echo $data;
    }


     public function hata()
     {
        $uid = I('post.uid'); 
        $student = M('student')->where(array('qid'=>$uid))->select();
        // echo '<pre>';
        // print_r($student);die;
       
        if($student)
        {
            $result['code'] = 1;
        }else{
            $result['code'] =2;
        }
        echo json_encode($result);
     }
     public function hataa()
     {
        $uid = I('post.uid');
        if($uid !== '')
        {
            $result['code'] = 1;
        }else{
             $result['code'] =2;
        }
         echo json_encode($result);
     }

    /**
     * 导出Excel
     * @param  null
     * @return void
     */
    function report(){//导出Excel
        // $map['id'] = session('uid');
       
        
        $map['id'] = I('get.uid');
        $wh = I('get.uid');
        $info = M('enterprise')
        ->where($map)
        ->find();   //查询企业信息
       
        $maps['qid'] = I('get.uid'); 
        $xlsData = M('student')
        ->field('id,name,mobile,note')
        ->where($maps)
        ->order('id desc')
        ->select(); //企业下所有学员



        foreach ($xlsData as $key => $value) {
                $ids[] = $value['id'];
        }
         
        $ini['o.sid'] = array('in',$ids);
        $db = C('DB_PREFIX');
        $orders = M("order as o")
        ->field('u.name,u.mobile,o.id,o.sid,o.times,o.ticket,o.card,o.friends,o.disciple,o.ptype,o.ctype,o.ftype,o.dtype')
        ->where($ini)
        ->join($db.'user as u on o.uid = u.id','left')
        ->order('o.id desc')
        ->select(); //查询学员相对应的订单列表与讲师信息 

        header('Content-type: text/html; charset=utf-8');
        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        $filename="学员数据";
        $filename=iconv("utf-8", "gb2312", $filename);
        header("Content-Disposition:filename=$filename.xls");

        $str="<table border='1'>";
        $str .= '<tr><td>序号</td><td>姓名</td><td>联系电话</td><td>备注</td><td>讲师</td><td>讲师电话</td><td>时间及分类</td><td>数量</td><td>提成</td><td>金额</td><td>状态</td></tr>';
        
        foreach ($xlsData as $key => $value) {
            $data['id'] = $value['id'];
            $data['mobile'] = $value['mobile'];
            $data['note'] = $value['note'];

            $str.="<tr><td>".$key."</td><td>".$value["name"]."</td><td>".$data['mobile']."</td><td>".$data['note']."</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";

                foreach ($orders as $key1 => $value1) {
                    if($value['id'] == $value1['sid']){
                        $str.="<tr>";
                        $str .= '<td></td><td></td><td></td><td></td><td>'.$value1['name'].'</td><td>'.$value1['mobile'].'</td><td>'.$value1['times'].'</td><td></td><td></td><td></td><td></td></tr>';
                        $str .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>票</td><td>'.$value1['ticket'].'</td><td>'.$info['tc'].'%</td><td>'.money($value1['ticket'],$info['tc'],$info['leafletstc']).'元</td><td>'.$this->statu($value1['ptype']).'</td></tr>';
                        $str .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>卡</td><td>'.$value1['card'].'</td><td>'.$info['cc'].'%</td><td>'.money($value1['card'],$info['cc'],$info['leafletscc']).'元</td><td>'.$this->statu($value1['ctype']).'</td></tr>';
                        $str .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>朋友圈</td><td>'.$value1['friends'].'</td><td>'.$info['fc'].'%</td><td>'.money($value1['friends'],$info['fc'],$info['leafletsfc']).'元</td><td>'.$this->statu($value1['ftype']).'</td></tr>';
                        $str .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>弟子</td><td>'.$value1['disciple'].'</td><td>'.$info['dc'].'%</td><td>'.money($value1['disciple'],$info['dc'],$info['leafletsdc']).'元</td><td>'.$this->statu($value1['dtype']).'</td></tr>';
                    }
                }
            $str.="</tr>";
            
        }
        $str.="</table>";
        echo $str;
    }

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
     
    public function admininfo(){
        session("nav","branch");
        $this->display("admininfo");
    }
    public function branch(){
        session("nav","biy");
        $this->display("branch");
    }
    //admin 主页显示
    public function firm(){
        $p = I('post.page');
        $limit = 8;
        $first = ($p-1) * $limit;
        $admin = M('admin')->where(array('type'=>1))->limit($first,$limit)->order('id desc')->select();
        echo json_encode($admin); 

    }
    //admin主页修改密码
    public function adminpwd(){
        $oldpwd = md5(I('post.oldpwd'));
        $newpwd = md5(I('post.newpwd'));
        $id = I('post.id');
        $pwd = M('admin')->where(array('id'=>$id))->find();
        if($pwd['pwd'] == $oldpwd){
                $data['pwd'] = $newpwd;
                $admin =  M('admin')->where(array('id'=>$id))->save($data);
                if(!empty($admin)){
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

    public function blocker(){
        $id = I('post.id');
        $type = I('post.type');
        if($type == 1){//企业账号
            $val  = I('post.val');
            $acc = M('admin')->where(array('account'=>$val))->find();
            if($acc){
                     $result['errcode'] = 3;
                     $result['errmsg'] ='企业账号已被占用';
                      echo json_encode($result); die;
            }else{
                 $data['account'] = $val;
                 $user = M('admin')->where(array('id'=>$id))->save($data);
            }
            $result['errcode'] = 1;
            $result['errmsg'] ='修改企业账号成功';
            echo json_encode($result); 
        }
         if($type == 2){//企业名称
            $val  = I('post.val');
            $data['qyname'] = $val;
            $data['mname'] = $val;
            $user = M('admin')->where(array('id'=>$id))->save($data);
            $result['errcode'] = 1;
            $result['errmsg'] ='修改企业名称成功';
            echo json_encode($result); 
        }
    }

    public  function delacc(){
        $id = I('post.id');
        $delname = M('admin')->where(array('id'=>$id))->delete();
        if($delname){
            $result['errcode'] = 1;
            $result['errmsg'] ='删除成功';
        }else{
            $result['errcode'] = 1;
            $result['errmsg'] ='删除失败';
        }
        echo json_encode($result); 
    }

    public function adduid(){
        $ini['account'] = I('post.account');
        $acc = M('admin')->where(array('account'=>$ini['account']))->find();
        $bcc = M('salesman')->where(array('account'=>$ini['account']))->find();
        $account3 = M('user')->where(array('account'=>$ini['account']))->find();
        if($acc || $bcc || $account3){
             $result['errcode'] = 3;
             $result['errmsg'] ='账号已被占用';
             echo json_encode($result); die;
        }
        if(I('post.typey') == 1){
            $ini['qyname'] = I('post.name');
            $ini['mname'] = I('post.name');
            $ini['pwd'] = md5(I('post.pwd'));
            $ini['pid'] = session('id');
            $ini['type'] = 5;
        }else if(I('post.typey') == 2){
            $ini['qyname'] = I('post.name');
            $ini['mname'] = I('post.name');
            $ini['pwd'] = md5(I('post.pwd'));
            $ini['pid'] = session('id');
            $ini['type'] = 4;
        }else{
            $ini['qyname'] = I('post.name');
            $ini['mname'] = I('post.name');
            $ini['pwd'] = md5(I('post.pwd'));
            $ini['type'] = 1;
        }
        M('admin')->add($ini);
        $result['errcode'] = 1;
        $result['errmsg'] ='添加成功';
        echo json_encode($result); 

    }

    Public function editpwd1(){
         $type=session("type");
         $uid=session("id");
         $oldpwd = md5(I('post.oldpwd'));
         $newpwd = md5(I('post.newpwd'));

         if($_SESSION['type']==C('SALES_POWER_SESSION')){
            $salesman = M('salesman')->where(array('id'=>$uid))->find();
            if($oldpwd == $salesman['pwd']){
                $data['pwd'] = $newpwd;
                M('salesman')->where(array('id'=>$uid))->save($data);
                 $result['errcode'] = 1;
                 $result['errmsg'] = '修改成功';

            }else{
                $result['errcode'] = 3;
                $result['errmsg'] = '旧密码错误';
            }
             
         }
         if($type == 5 || $type == 6 || $type == 7 || $type == 8){
             $user = M('user')->where(array('id'=>$uid))->find();
               if($oldpwd == $user['pwd']){
                $data['pwd'] = $newpwd;
                M('user')->where(array('id'=>$uid))->save($data);
                 $result['errcode'] = 8;
                 $result['errmsg'] = '修改成功';

                    }else{
                        $result['errcode'] = 3;
                        $result['errmsg'] = '旧密码错误';
                    }
         } 
         if($type == 10 || $type == 9 || $uid == 1){
          $user = M('admin')->where(array('id'=>$uid))->find();
               if($oldpwd == $user['pwd']){
                $data['pwd'] = $newpwd;
                M('admin')->where(array('id'=>$uid))->save($data);
                 $result['errcode'] = 8;
                 $result['errmsg'] = '修改成功';

                    }else{
                        $result['errcode'] = 3;
                        $result['errmsg'] = '旧密码错误';
                    }
         }

        echo json_encode($result); 
    }

    //账号管理员
    public function listnub(){
        $uid = session('id');
        // echo '<pre>';print_r($uid);die;
        $p = I('post.page');
        $limit = 6;
        $first = ($p-1) * $limit;
        if(I('post.typey') == 100){
            $admin = M('admin')->where(array('type'=>4,'pid'=>$uid))->limit($first,$limit)->order('id desc')->select();
        }else{
            $admin = M('admin')->where(array('type'=>5,'pid'=>$uid))->limit($first,$limit)->order('id desc')->select();
        }
        if($admin){
            $result['errcode'] = 1;
            $result['data'] = $admin;
        }else{
            $result['errcode'] = 0;
            $result['errmsg'] = '查询为空';
        }
        echo json_encode($result); 
        // echo '<pre>';print_r($admin);die;
    }
    public function pagenub(){
           $uid = session('id');
           $admin = M('admin')->where(array('type'=>5,'pid'=>$uid))->count();
           $users = ceil($admin/6);
           echo json_encode($users);
        
    } 
     public function pagenub2(){
         $uid = session('id');
         $admin = M('admin')->where(array('type'=>4,'pid'=>$uid))->count();
         $users = ceil($admin/6);
         echo json_encode($users);
    }

	public function adminceo(){

		session("rightnav","ceo");
		session("nav","branch");
		$this->display();
	}

	public function adminnub(){
		session("rightnav","adminnub");
		$this->display();
	}



}