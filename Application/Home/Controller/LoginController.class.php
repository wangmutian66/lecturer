<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function login(){
        //退出登录 session 关掉
        session_unset();
        session_destroy();
  
        $this->display('login');
    }
    
    public function loginverify(){
        $username = I('post.user');
        $password = I('post.pwd');
    
        if(!empty($username) && !empty($password)){
            $map['account'] = $username;
            $map['pwd'] = md5($password);
                
                $admin = M('admin')->where($map)->find();

                $enterprise = M('enterprise')->where($map)->find();
                $user = M('user')->where($map)->find();
                $salesman = M('salesman')->where($map)->find();
                if(!empty($admin)){ //ceo 财务总监  大区总裁
                    session('id', $admin['id']); //记录用户uid
                    if($username!='admin'){
                        if($admin['type']==1){ // 企业
                            session("yid",1);
                        }else if($admin['type']==2 || $admin['type']==4 || $admin['type']==5){ //2.财务  4.ceo  5.管理员
                            $ad=M('admin')->where(array("id"=>$admin['pid'],"type"=>1))->find();
                            session('id',$ad['id']);
                            session("yid",$admin["id"]); //此处记录的当前真实id
                        }

                        session("name",$admin["mname"]);
                        if($admin['type']==1){ //企业总账号
                            session('type',13);
                        }else if($admin['type']==2){
                            session('type',10);
                            $arr['type'] = 10;
                        }else if($admin['type']==3){ //大区总裁
                            session('type',9);
                            $arr['type'] = 9;
                        }else if($admin['type']==4){ //ceo账号
                            session('type',11);
                        }else if($admin['type']==5){ //管理员账号
                            session('type',12);
                        }
                        
                        $arr['errcode'] = 0;
                    }else{
                        session('finatype',1);
                        session("yid","admin"); 
                        $arr['errcode'] = 2;
                    }
                    $arr['errmsg'] = "登录成功";
                   
                }else if(!empty($user)){  // 分总经理 下面的角色
                    
                    session('id', $user['id']); //记录用户uid
                    $userid=M('user')->where(array('id'=>$user['id']))->getField("finatype");
                    session('finatype',$userid);//财务权限
                    session("name",$user["name"]);
                    session('type',$user["role"]);
                    $arr['type'] = $user["role"];
                    $arr['errcode'] = 0;
                    $arr['errmsg'] = "登录成功";
                 
                }else if(!empty($salesman)){
                    session('id', $salesman['id']); //记录用户uid
                    session("name",$salesman["name"]);
                    $sales=C('SALES_POWER_SESSION');
                    session('type',$sales);
                    $arr['errcode'] = 0;
                    $arr['errmsg'] = "登录成功";    
                    
                }else{
                    $arr['errcode'] = 1;
                    $arr['errmsg'] = '账号或密码错误!';
                }
             
        }else{
            $arr['errcode'] = 0;
            $arr['errmsg'] = '请填写账号密码!';
            
        }
        echo json_encode($arr);
    }
    
    public function loginverify1(){
   		$username = I('post.user');
    	$password = I('post.pwd');

        if(!empty($username) && !empty($password)){
            $map['account'] = $username;
            $map['pwd'] = md5($password);
            if($username == 'admin'){
                $admin = M('admin')->where($map)->find();
                if(!empty($admin)){
                    session('aid', $admin['id']); //记录用户uid
                    if($admin['type'] == 1){
                         session('admin', 1); //记录用户类型
                         $arr['errcode'] = 1;
                         $arr['errmsg'] = "admin";   //跳转管理员
                         $this->ajaxReturn($arr);
                    }else if($admin['type'] == 2){
                         session('caiwu', 1); //记录用户类型
                         $arr['errcode'] = 1;
                         $arr['errmsg'] = "caiwu";   //跳转财务总监页面
                         $this->ajaxReturn($arr);
                    }else if($admin['type'] == 3){
                         session('dqzc', 1); //记录用户类型
                         $arr['errcode'] = 1;
                         $arr['errmsg'] = "dqzc";   //跳转大区总裁页面
                         $this->ajaxReturn($arr);
                    }                   
                }else{
                    $arr['errcode'] = 2;
                    $arr['errmsg'] = '账号或密码错误!';
                    $this->ajaxReturn($arr);                    
                }
            }else{
                $enterprise = M('enterprise')
                ->field('id,account,pwd,Qyname')
                ->where(array('account'=>$username))
                ->find(); //查询企业账号
                if(!empty($enterprise)){
                    if($enterprise['pwd'] == $map['pwd']){
                        session('eid', $enterprise['id']); //记录用户uid
                        // session('name', $enterprise['Qyname']); //记录用户名
                        if($enterprise['ltype'] == 0){
                            session('enterprise', 1); //记录用户类型
                            $arr['errcode'] = 1;
                            $arr['errmsg'] = "enterprise";  //跳转分公司页面
                            $this->ajaxReturn($arr);
                        }else if($enterprise['ltype'] == 1){
                            session('jiangshi', 1); //记录用户类型
                            $arr['errcode'] = 1;
                            $arr['errmsg'] = "jiangshi";  //跳转讲师页面
                            $this->ajaxReturn($arr);
                        }
                                              
                    }else{
                        $arr['errcode'] = 2;
                        $arr['errmsg'] = '账号或密码错误!';
                        $this->ajaxReturn($arr);                         
                    }
                }else{
                    $user = M('user')->where(array('account'=>$username))->find(); //查询财务或讲师账号
                    if(!empty($user)){
                        if($user['pwd'] == $map['pwd']){
                            session('uid', $user['id']); //记录用户uid
                            //session('name', $user['name']); //记录用户名
                            if($user['lectype'] == 1){
                                session('alsystem', 1); //记录用户类型
                                // session('type', 'alsystem');
                                $arr['errcode'] = 1;
                                $arr['errmsg'] = "alsystem";    //跳转讲师
                                $this->ajaxReturn($arr);      
                            }else if($user['finatype'] == 1){  
                                session('affairs', 1); //记录用户类型
                                // session('type', 'affairs');
                                $arr['errcode'] = 1;
                                $arr['errmsg'] = "affairs"; //跳转财务
                                $this->ajaxReturn($arr);                                      
                            }
                        }else{
                            $arr['errcode'] = 2;
                            $arr['errmsg'] = '账号或密码错误!';
                            $this->ajaxReturn($arr);                         
                        }       
                    }else{
                        $arr['errcode'] = 3;
                        $arr['errmsg'] = '账号不存在!';
                        $this->ajaxReturn($arr);                             
                    }            
                }
            }
        }else{
            $arr['errcode'] = 0;
            $arr['errmsg'] = '请填写账号密码!';
            $this->ajaxReturn($arr);
        }
    }

}