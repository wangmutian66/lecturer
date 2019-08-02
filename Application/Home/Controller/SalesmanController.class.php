<?php
namespace Home\Controller;
use Think\Controller;

class SalesmanController extends BaseController {
    private $pagecount=6;
	public function index(){
		session("rightnav","salesman");
		$this->display();
	}
	//列表
	public function listsal(){
		 $id = session("id");
		 $type = session('type');
		 $p = I('post.page');
         $limit = 6;
         $first = ($p-1) * $limit;
		 if($type >= 9){//大区总裁登陆

		 	$result['type'] = 111;


		 }else if($type == 8 || $type == 7 || $type == 6 || $type == 5){//分公司登陆

		 	$result['type'] =  333;
		 }
		$data['uid']=getcompanyId();
		$salesman = M('salesman')->where($data)->limit($first,$limit)->order('id desc')->select();
		$result['salesman'] = $salesman;
		echo json_encode($result);
	}
	
	
	public function chanenter(){
	    $page=I('post.page',1);
	    $id=I('post.id');
	    $patecount=$this->pagecount;
	    $limit=($page-1)*$patecount;
	    
	    $salesman=M('salesman')->where(array("uid"=>$id))->limit($limit,$patecount)->select();
	    $salesmancount=M('salesman')->where(array("uid"=>$id))->count();
	    $currpage=ceil($salesmancount/$patecount);
	    echo json_encode(array("list"=>$salesman,"count"=>$currpage));
	}
	
	public function page(){
		 $id = session("id");
		 $type = session('type');
		 if($type == 9){//大区总裁登陆
		 	$where['aid'] =$id;
		 	$enterprise = M('enterprise')->where($where)->select();
		 	foreach($enterprise  as $k => $v){
		 		$idd[$k] = $v['id'];
		 	}
		 	if(empty($idd)){
		 		echo json_encode(array());
	            		die;
		 	}
		 	$data['uid'] = array('in', $idd);
		 	$salesman = M('salesman')->where($data)->count();
		 	$enterprise1 = ceil($salesman/6);

		 }else if($type == 8 || $type == 7 || $type == 6 || $type == 5){//分公司登陆
		 	$user = M('user')->where(array('id'=>$id))->find();
		 	$salesman = M('salesman')->where(array('uid'=>$user['uid']))->count();
		 	$enterprise1 = ceil($salesman/6);

		 }else if($type == 10){//企业登陆
		 	$admin = M('admin')->where(array('pid'=>$id))->select();
		 	if(empty($admin)){
		 		echo json_encode(array());
	            		die;
		 	}
		 	foreach($admin as $k => $v){
		 		$aid[$k] = $v['id'];
		 	}
		 	$where['aid'] = array('in', $aid);
		 	$enterprise = M('enterprise')->where($where)->select();
		 	foreach($enterprise as $k => $v){
		 		$aaid[$k] = $v['id'];
		 	}
		 	$data['uid'] = array('in', $aaid);
		 	$salesman = M('salesman')->where($data)->count();
		 	$enterprise1 = ceil($salesman/6);

		 }
		$data['uid'] = getcompanyId();
		$salesman = M('salesman')->where($data)->count();
		$enterprise1 = ceil($salesman/6);
		 // echo '<pre>';print_r($salesman);die;
		 echo json_encode($enterprise1);
		}

		 public function savepwd(){
		 	$newpwd = md5(I('post.newpwd'));
		 	$oldpwd = md5(I('post.oldpwd'));
		 	$id = I('post.id');
		 	$salesman = M('salesman')->where(array('id'=>$id))->find();
		 	if($salesman['pwd'] == $oldpwd){
		 		$data['pwd'] = $newpwd;
		 		M('salesman')->where(array('id'=>$id))->save($data);
		 		$result['errcode'] = 1;
		 	    $result['errmsg'] =  '修改成功';

		 	}else{
		 		$result['errcode'] = 3;
		 	    $result['errmsg'] =  '旧密码错误';
		 	}
		 	 // echo '<pre>';print_r($oldpwd);die;
 						echo json_encode($result);

		 }
//删除
		 public function delacc(){
		 	$id = I('post.id');
		 	$salesman = M('salesman')->where(array('id'=>$id))->delete();
		 	if($salesman){
		 		$result['code'] = 1;
		 	    $result['msg'] =  '删除成功';
		 	}else{
		 		$result['code'] = 0;
		 	    $result['msg'] =  '删除失败';
		 	}
		 	echo json_encode($result);

		 }
//修改名称和名字
	public function savacc(){
		$id = I('post.id');
		$type = I('post.type');
		$val = I('post.val');
		if($type == 1){
			$salesman = M('salesman')->where(array('account'=>$val))->find();
			if($salesman){
				$result['code'] = 3;
		 	    $result['msg'] =  '账号已被占用';
			}else{
				$data['account'] = $val;
				M('salesman')->where(array('id'=>$id))->save($data);
				$result['code'] = 1;
		 	    $result['msg'] =  '账号或名字修改成功';
			}
		}else{
			    $data['name'] = $val;
				M('salesman')->where(array('id'=>$id))->save($data);
				$result['code'] = 1;
		 	    $result['msg'] =  '账号或名字修改成功';
		}
		echo json_encode($result);
	}
	//分公司人添加
	public function addregion(){
		$mob = I('post.mob');
		$account = I('post.acc');
		$user = M('user')->where(array('mobile'=>$mob))->select();
		$salesman = M('salesman')->where(array('mobile'=>$mob))->select();
		if($user || $salesman){
			echo json_encode(10);
			die;
		}
		$salesman = M('salesman')->where(array('account'=>$account))->select();
		if($salesman){
			$result['code'] = 3;
	 	    $result['errmsg'] =  '账号已存在';
	 	    echo json_encode($result);die;
		}
		$data['account'] = $account;
		$data['name'] = I('post.name');
		$data['pwd'] = md5(I('post.pwd'));
		$data['role'] = I('post.jiaose');
		$data['mobile'] = $mob;

	    $id = session("id");
	    $type = session('type');
		 // if($type == 8){
		 	$admin = M('user')->where(array('id'=>$id))->find();
		 	$data['uid'] = $admin['uid'];
		 	$data['aid'] = $admin['prid'];
		 	M('salesman')->add($data);
		 	$result['code'] = 1;
	 	    $result['errmsg'] =  '添加成功';
		 // }
		 echo json_encode($result);
		 // echo '<pre>';print_r($id);die;

	}
	public function addsaladmin(){
         $result=$this->salesmancontent();
         echo json_encode($result); 
   }
   
   public function editsalesman(){
       $id=I('post.id');
       $salesman=M('salesman')->where(array("id"=>$id))->find();
       $result=$this->salesmancontent();
       $result["salesman"]=$salesman;
       echo json_encode($result);
   }
   
   private function salesmancontent(){
       $id = session("id");
       $type=session("type");
       if($type==10||$type==11||$type==12||$type==13){
           // $account = M('admin')->where(array('pid'=>$id,'type'=>3))->select();
           // foreach($account as $k => $v){
           //     $shopid[$k]  = $v['id'];
           // }
           // $where['aid'] = array(array('in', $shopid));
           // 
           $enterprise = M('enterprise')->where(array("id"=>getcompanyId()))->select();
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
       return $result;
   }
   
   public function dxchange(){
       $id=I('post.id');
       $enterprise=M('enterprise')->where(array('aid'=>$id))->select();
       
       echo json_encode($enterprise);
   }

   public function addregionadmin(){
		$mob = I('post.mob');
		$user = M('user')->where(array('mobile'=>$mob))->select();
		$salesman = M('salesman')->where(array('mobile'=>$mob))->select();
		if($user || $salesman){
			echo json_encode(10);
			die;
		}

   		$data['aid'] = I('post.presid');
   		$data['name'] = I('post.gsname');
   		$data['pwd'] = md5(I('post.gspwd'));
   		$data['account'] = I('post.account');
   		$data['uid'] = I('post.cid');
   		$data['role'] =  I('post.jiaose');
   		$data['mobile'] = $mob;
   		$salesman = M('salesman')->where(array('account'=>$data['account']))->find();
   		$account = M('admin')->where(array('account'=>$data['account']))->find();
   		$account3 = M('user')->where(array('account'=>$data['account']))->find();
   		if($salesman || $account || $account3){
   			$result = 3;
   		}else{
   			M('salesman')->add($data);		
			$result = 1;
   		}
   		
		 echo json_encode($result);

   }
   
   
   public function saveregionadmin(){
       $id=I('post.id');
       $data['aid'] = I('post.province');
       $data['name'] = I('post.name');
       $data['account'] = I('post.account');
       $data['uid'] = I('post.uid');
       $data['role'] =  I('post.role');
       $data['mobile'] = I('post.mobile');
       $id = I('post.id');

  	    $account = M('salesman')->where(array('account'=>$data['account']))->find();
		$accountid = M('salesman')->where(array('account'=>$data['account'],'id'=>$id))->find();
	    $mobile = M('salesman')->where(array('mobile'=>$data['mobile']))->find();
	    $mobileid = M('salesman')->where(array('mobile'=>$data['mobile'],'id'=>$id))->find();

	    if(empty($account) || $accountid){
   			if(empty($mobile) || $mobileid){
   				$user = M('salesman')->where(array('id'=>$id))->save($data);
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

}
