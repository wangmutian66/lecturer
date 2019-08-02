<?php
namespace Home\Controller;
use Think\Controller;

class SetsailController extends BaseController {

	public function index(){


        // if (session("?uid")){
        //     $uid = session('id');
        //     $admin =  M('admin')->where(array('id'=>$uid))->find();

        //     if(empty($admin)){
        //         $this->redirect('Home/Login/login');
                 
        //      }else{
               
        //         $this->display();
        //      }
        // }else{
        //     $this->redirect('Home/Login/login');
        // }
        // 
        $admin = M("admin")->where(array('pid'=>1,'type'=>1))->select();
        $role = C('ROTA_STATE.branch');
      
        $this->assign('role',$role);
        $this->assign('admin',$admin);
        $this->display();

	}
	
    //企业添加大区总裁和财务总监
    public function addregion(){
            $id = session("id");
            $type = I('post.type');
            $ini['account'] = I('post.acc');
            $account = M('admin')->where(array('account'=>$ini['account']))->find();
            $account2 = M('salesman')->where(array('account'=>$ini['account']))->find();
            $account3 = M('user')->where(array('account'=>$ini['account']))->find();
            if($account || $account2 || $account3){
                    $reslue['errcode'] = 3;
                    $reslue['errmsg'] = '企业账号已被占用';
                    echo json_encode($reslue);die;
            }else{
                    $ini['pwd'] = md5(I('post.pwd'));
                    if($type == 1){
                        $ini['type'] = 2;
                    }else{
                        $ini['type'] = 3;
                    }
                    $ini['pid'] = $id;
                    $ini['mname'] = I('post.name');
                    
                    $pidadmin=M('admin')->where(array('id'=>$id))->find();
                    $ini['qyname'] =$pidadmin["qyname"] ;
                    
                    M('admin')->add($ini);
                    $reslue['errcode'] = 1;
                    $reslue['errmsg'] = '添加成功';
                    echo json_encode($reslue);
            }
            
    }
    //大区
    public function lised(){
            $id = session("id");
            $p = I('post.page');
            //$type=session("type");
            $type = I('post.type');
            $limit = 8;
            $first = ($p-1) * $limit;
           
            if($type == 1){
                $admin = M('admin')->where(array('pid'=>$id,'type'=>2))->limit($first,$limit)->order('id desc')->select();
            }else if($type == 2){
                $admin = M('admin')->where(array('pid'=>$id,'type'=>3))->limit($first,$limit)->order('id desc')->select();
            }
           
            
            echo json_encode($admin); 
    }
    
    //分公司
    // public function lised1(){
    //         $type = session("type");
           
    //         $id = session("id");
    //         $p = I('post.page');
    //         $limit = 6;
    //         $first = ($p-1) * $limit;
    //         if($type==10){
    //            $admin = M('admin')->where(array('pid'=>$id,'type'=>3))->select();
    //         }else if($type==9){
    //             $admin = M('admin')->where(array('id'=>$id,'type'=>3))->select();
    //         }
    //         foreach($admin as $k => $v){
    //                 $shopid[$k]  = $v['id'];
    //         }
            
    //         $aid= array(array('in', $shopid));
    //         $where='';
    //         if(!empty($aid)){
    //             $where['aid']=$aid;
    //         }
    //         $enterprise = M('enterprise')->where($where)->limit($first,$limit)->order('id desc')->select();
    //         echo json_encode($enterprise); 
    // }
    //分公司
    public function lised1(){
        $type = session("type");
        $id = session("id");
        $p = I('post.page');
        $limit = 8;
        $first = ($p-1) * $limit;
        $status=0;
        

        $enterprise = M('enterprise')->where(array("id"=>getcompanyId()))->limit($first,$limit)->order('id desc')->select();
        foreach($enterprise as $ke => $ve){
          $enterprise[$ke]['mname'] =M('admin')->where(array("id"=>$ve['aid']))->getField("mname");
        }
     
        echo json_encode(array('enterprise'=>$enterprise,'status'=>$status,'user'=>$type));
        
    }
    //讲师
    public function lised2(){
             $id = 17;
            $p = I('post.page');
            $limit =8;
            $first = ($p-1) * $limit;
            $admin = M('admin')->where(array('pid'=>$id))->select();
            foreach($admin as $k => $v){
                    $shopid[$k]  = $v['id'];
            }
            $where['prid'] = array(array('in', $shopid));
            $user = M('user')->where($where)->limit($first,$limit)->order('id desc')->select();
           
            echo json_encode($user); 
    }
    
    public function page()//财务总监分页
    {
        $id = session("id");
        $type = I('post.type');

        $users = M('admin')->where(array('pid'=>$id,'type'=>2))->count();

        // $users = M("admin")->where(array('pid'=>$id))->count();
        $users = ceil($users/8);
        echo json_encode($users);
    }
    public function page1()//分公司分页
    {
        
//        $id = session("id");
//        $type = session("type");
//
//        if($type==10){
//            $admin = M('admin')->where(array('pid'=>$id,'type'=>3))->select();
//        }else if($type==9){
//            $admin = M('admin')->where(array('id'=>$id,'type'=>3))->select();
//        }
//        else{
//          $users = M("user")->where(array('id'=>$id))->count();
//          echo json_encode($users);
//          die;
//        }
//        foreach($admin as $k => $v){
//                $shopid[$k]  = $v['id'];
//        }
//        if(empty($admin)){
//          echo json_encode(1);die;
//        }
//        $aid= array('in', $shopid);
//        $where='';
//
//        if(!empty($aid[0][1])){
//             $where['aid']=$aid;
//        }
//        $users = M("enterprise")->where($where)->select();
//        if(empty($users)){
//          echo json_encode(1);die;
//        }
//        // echo '<pre>';print_r($users);die;
//        foreach($users as $k => $v){
//          $ushopid[$k] = $v['id'];
//        }
//        $userid = array('in', $ushopid);
        $enterprise = M('enterprise')->where(array('id'=>getcompanyId()))->count();

        // echo '<pre>';print_r($enterprise);die;
        $enterprise1 = ceil($enterprise/8);
        echo json_encode($enterprise1);
    }
    public function page2()//大区总裁分页
    {
       $id = session("id");
       // echo '<pre>';print_r($id);die;
        $users = M("admin")->where(array('pid'=>$id,'type'=>3))->count();
        $users = ceil($users/8);
        echo json_encode($users);
    }

    //修改密码
    public function editpwd(){
        $id = I('post.id');
        $newpwd = md5(I('post.newpwd'));
        $oldpwd = md5(I('post.oldpwd'));
        $type = I('post.type');
        if($type == 1){//修改大区密码
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

        }
        if($type == 2){
                $pwd = M('enterprise')->where(array('id'=>$id))->find();
                if($pwd['pwd'] == $oldpwd){
                $data['pwd'] = $newpwd;
                $admin =  M('enterprise')->where(array('id'=>$id))->save($data);
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

        }
        if($type == 3){
                $pwd = M('user')->where(array('id'=>$id))->find();
              if($pwd['pwd'] == $oldpwd){
                $data['pwd'] = $newpwd;
                $admin =  M('user')->where(array('id'=>$id))->save($data);
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
        }
        echo json_encode($result);
    }

    //删除分公司
    public function delacc(){

         $id = I('post.id');
         $delname = M('enterprise')->where(array('id'=>$id))->delete();              
         $user = M('user')->where(array('uid'=>$id))->delete(); 
         	
         if($delname){
          $result['errcode'] = 1;
          $result['errmsg'] ='删除成功';
          }else{
              $result['errcode'] = 2;
              $result['errmsg'] ='删除失败';
          }

               
           echo json_encode($result);
    }
    //修改企业账号
    public function savacc(){
        $id = I('post.id');
        $type = I('post.type');
        $val = I('post.val');
        if($type == 1){
                $data['account'] = $val;
                $account = M('admin')->where(array('account'=>$val,'id'=>array("neq",$id)))->select();
                if($account){
                         $result['errcode'] = 3;
                         $result['errmsg'] ='企业账号已被占用';
                         echo json_encode($result);die;
                }else{
                         M('admin')->where(array('id'=>$id))->save($data);
                 }
        }
        if($type == 2){
                $data['account'] = $val;
                $account = M('enterprise')->where(array('account'=>$val,'id'=>array("neq",$id)))->select();
                if($account){
                         $result['errcode'] = 3;
                         $result['errmsg'] ='企业账号已被占用';
                         echo json_encode($result);die;
                }else{
                        M('enterprise')->where(array('id'=>$id))->save($data);
                }
        }
        if($type == 3){
                $account = M('user')->where(array('account'=>$val,'id'=>array("neq",$id)))->select();
                if($account){
                         $result['errcode'] = 3;
                         $result['errmsg'] ='企业账号已被占用';
                         echo json_encode($result);die;
                }else{
                        $data['account'] = $val;
                        M('user')->where(array('id'=>$id))->save($data);
                }
                
        }
        $result['errcode'] = 1;
        $result['errmsg'] ='修改成功';
        echo json_encode($result);
    }
	//修改公司名称
   public function savname(){
        $id = I('post.id');
        $type = I('post.type');
        $val = I('post.val');
        if($type == 1){
                 $data['mname'] = $val;
                 M('admin')->where(array('id'=>$id))->save($data);
        }
        if($type == 2){
                 $data['Qyname'] = $val;
                 M('enterprise')->where(array('id'=>$id))->save($data);
        }
        if($type == 3){
                 $data['name'] = $val;
                 M('user')->where(array('id'=>$id))->save($data);
        }
        $result['errcode'] = 1;
        $result['errmsg'] ='修改成功';
        echo json_encode($result);
   } 

   
   public function biy(){ 
       session("nav","biy");
       $id=getenterpriseid();

       $finatype=session('finatype');

       $proportion=M('admin')->where(array("id"=>$id))->getField("proportion");
       $this->assign("proportion",$proportion);

       $this->assign("finatype",$finatype);
       $this->display("biy");
   }

    public function getcomlist(){
        $page=I('post.page',1);
        $currpage=6;
        $limit=($page-1)*$currpage;
        $comlist=M('enterprise')->where(array("id"=>getcompanyId()))->limit($limit,$currpage)->select();
        $comlistcount=M('enterprise')->where(array("id"=>getcompanyId()))->count();
        $countpage=ceil($comlistcount/$currpage);
        echo  json_encode(array("comlist"=>$comlist,"currpage"=>$countpage));
    }

    public function savtcom(){
        $post=I('post.');
        $result=M('enterprise')->where(array("id"=>$post["id"]))->save($post);
        if($result){
            $res["error"]=0;
            $res["msg"]="修改成功";
        }else{
            $res["error"]=1;
            $res["msg"]="未做任何操作";
        }
        echo json_encode($res);
    }

   

    public function ranking()
    {
        /* $aid = session('id'); */
        $db = C('DB_PREFIX');
        $etime=I('post.timeyear','');
        $stime=I('post.beforeyea','');
        $name=I('post.lsor','');
        $type=session("type");
        $uid=session("id");
        $where='';
        
        $uid=getcompanyId();

        $where.=" e.id in (".$uid[1].") ";
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
        if(!file_exists("./Public/Uploads/ranking/")){
            mkdir("./Public/Uploads/ranking/",0777,true);
        }

        if($name==""){
            $numk=1;
            foreach ($enterprise as $k=>$item) {
                $enterprise[$k]["numk"]=$numk; //排名
                $numk++;
            }
            file_put_contents("./Public/Uploads/ranking/ranking_".session("id").".txt",json_encode($enterprise));
        }else{
            $ranking=file_get_contents("./Public/Uploads/ranking/ranking_".session("id").".txt");
            $enterprise1=json_decode($ranking,true);

            foreach ($enterprise1 as $k=>$row){
                foreach ($enterprise as $key=>$val){
                    if($row["id"]==$val["id"]){
                        $enterprise[$key]=$enterprise1[$k];
                    }
                }
            }

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
       /* $aid = session('id'); */
       $db = C('DB_PREFIX');
       $etime=I('post.timeyear','');
       $stime=I('post.beforeyea','');
       $name=I('post.lsor','');
       $type=session("type");
       $uid=session("id");
       $where='';
       /*
       switch ($type){
           case 10:
               $admin=M('admin')->where(array("id"=>$uid))->find();
               if($admin["type"]==2){
                   $admin=M('admin')->where(array("pid"=>$admin["pid"],"type"=>3))->getField("id",true);
                   $aid=implode(",", $admin);
               }else if($admin["type"]==1){
                   $admin=M('admin')->where(array("pid"=>$uid,"type"=>3))->getField("id",true);
                   $aid=implode(",", $admin);
               }
             	if(!empty($admin)){
               $where = "e.aid in ($aid) ";
             	}
               break;
           case 9:
               $admin=M('admin')->where(array("id"=>$uid))->find();
               $aid=$admin['id'];
               $where = array('e.aid'=>$aid);
               break;
           default:
               $user=M('user')->where(array("id"=>$uid))->find();
               $aid=$user["uid"];
               $where = "e.id in (".$aid.")";
               break;
       }
       */
       $uid=getcompanyId();

       $where.=" e.id in (".$uid[1].") ";

       if(!empty($uid[1])){
           if($name!=''){
              $where .="and e.Qyname like '%".$name."%' and (b.time between '".$etime."' AND '".$stime."' or isnull(b.time))"  ;
           }else{
              $where .= 'and (b.time between "'.$etime.'" AND "'.$stime.'" or isnull(b.time))';
           }

           $enterprise = M('enterprise as e')
             ->join($db.'bill as b on b.uid = e.id','left')
             ->where($where)      
             ->field('e.Qyname,e.aid,b.uid,b.aid as aaid,b.time,b.houston,sum(b.houston) as count,e.id as id')
             ->group("e.id")
             ->order("count desc")
             ->select();

           $numk=1;
           foreach ($enterprise as $k=>$v)
           {

              $admin = M('admin')->where(array('id'=>$v['aid']))->find(); //找到大区总监的id
              $admin = M('admin')->where(array('id'=>$admin['pid']))->find(); //再找到企业id
              if($v['count']==null){
                 $enterprise[$k]['count']=0;
              }
              $enterprise[$k]['riskfee']=$v["count"]*$admin["proportion"]/100; //风险金
              $enterprise[$k]["numk"]=$numk; //排名
              $numk++;
           }
       } 
       echo json_encode($enterprise);
   }
   
   
   
   

  public function showchekd(){
       //  $id = I('post.id');
        $id = session("id");
        $type=session("type");
        if($type>=10){
            $account = M('admin')->where(array('pid'=>$id,'type'=>3))->select();
        }else if($type==9) {
            $account = M('admin')->where(array('id'=>$id,'type'=>3))->select();
        }
        
        
        echo json_encode($account);
   }    
   
   public function addzjl(){          
          $info = I("post.");
          $ini['Qyname'] = $info['gsname'];      
          $ini['aid'] = $info['presid'];
          $admin = M('admin')->where(array('id'=>session('id')))->find();
          if(session('type') == 13 || session('type') == 12 || session('type') == 11 || session('type') == 10){
            $ini['ppid'] = session('id');
          }else{
            $ini['ppid'] = $admin['pid'];
          }
          

          //$ini['phone'] = $info['gspwd'];
          $ini['ltype'] = $info['letype'];
          $enter=M('enterprise')->where($ini)->find();
          if(!empty($enter)){
              echo json_encode(3);
              exit;
          }

          $user = M('enterprise')->add($ini);

          if($user !== false)
          {
                echo json_encode(1);
          }
          else
          {
                echo json_encode(0);
          }
          
    }   
    public function addfgs(){
        $info = I("post.");
        $uid = session('id');
        $ini['name'] = $info['gsname'];
        $ini['prid'] = $info['presid'];
        $ini['account'] = $info['account'];
        $ini['mobile'] = $info['gsmobile'];
        $ini['lectype'] = $info['letype'];//讲师
        $ini['finatype'] = $info['cwtype'];//财务
        $ini['pwd'] = md5(I('post.gspwd'));
        $ini['role']=$info['role'];
        $ini['uid'] =  $info['cid'];
        $ini['times'] = date('Y-m-d');
        $users = M("user")->where(array('account'=>I('post.account')))->find();

        $mobile = M("user")->where(array('mobile'=>I('post.gsmobile')))->find();
        $mobile2 = M("salesman")->where(array('mobile'=>I('post.gsmobile')))->find();

        $account = M('admin')->where(array('account'=>I('post.account')))->find();
        $account2 = M('salesman')->where(array('account'=>I('post.account')))->find();
      
         
        if(!empty($users) || !empty($account2) || !empty($account)){
            
            echo json_encode(3);
        }else{
            
          if(!empty($mobile) || !empty($mobile2)){
              echo json_encode(4);
          }else{
              $user = M('user')->add($ini);
              if($user !== false){
                  echo json_encode(1);
              }else{
                  echo json_encode(0);
              }
          }
        }
    }
    public function management(){
        session("nav","branch");
        session("rightnav","management");
        $this->display();
    }

    public function manager(){
        session("nav","branch");
        session("rightnav","manager");
        $this->display();
    }
    public function division(){
       
        session("nav","branch");
        session("rightnav","branch");
        $this->display("division");
    }
    public function branch(){
        $admin = M('admin')->where(array('id'=>session('id')))->find();
        $this->assign('admin',$admin);
        session("rightnav","branchindex");

        $this->display();
    }
    
    // public function updatamomey(){
    //     $id=I('post.id');
    //     $enterprise = M('enterprise')->where(array('id'=>$id))->field("leafletstc,leafletscc,leafletsfc,leafletsdc,leafletsnc")->find();
        
    //     echo json_encode($enterprise);
    // }
    
    public function editmomeny(){
        $post=I('post.');
        $id=$post['id'];
        $result=M('enterprise')->where(array('id'=>$id))->save($post);
        if($result){
            $arr["errcode"]=0;
            $arr["errmsg"]="修改成功";
        }else{
            $arr["errcode"]=1;
            $arr["errmsg"]="修改失败";
        }  
        echo json_encode($arr);
    }
    
    // public function branch(){
    //   //分公司管理

    //   $this->display("branch");
    // } 
    //  public function branch1(){
    //      //分公司管理
    //      $p = I("post.page");//分页接过来当前页
    //      $uid = session("id");
    //      $uid = 46;//大区总裁
    //      M("admin")->where(array('id'=>$uid))->
    //      //$enterprise = M('enterprise')->field('Qyname,tc,cc,fc,dc,nc')->where(array("aid"=>$uid))->select();
         
    //   echo json_encode(1);
    // }
      // 分公司
    public function branch1(){
            $type = session("type");//区分type类型
           
            $id = session("id");//登陆企业的账号
            $p = I('post.page');
            $limit = 8;
            $first = ($p-1) * $limit;
            if($type==10){
               $admin = M('admin')->where(array('pid'=>$id,'type'=>3))->select();//查的是大区总裁上一级的企业ceo
            }else if($type==9){
                $admin = M('admin')->where(array('id'=>$id,'type'=>3))->select();//这是查的是大区总裁
            }
            foreach($admin as $k => $v){
                    $shopid[$k]  = $v['id'];
            }
            
            $where['aid'] = array(array('in', $shopid));
            $enterprise = M('enterprise')->where($where)->limit($first,$limit)->order('id desc')->select();
           
            echo json_encode($enterprise); 
    }

    //admin所有公司比例
    public function branchdata()
    {
      $type=session("type");
      $id=session('id');
      if($type==9){
          $id=M('admin')->where(array("id"=>$id))->getField("pid");
      }
      $where['type'] = array(array('neq',0));
      $admin = M("admin")->where(array('id'=>$id),$where)->select();

      echo json_encode($admin);

    }
    public function money(){
        $admin = M('admin')->where(array('id'=>session('id')))->find();
        $this->assign('admin',$admin);
        session("rightnav","money");
        $this->display();

    }
    public function bradnmoney()
    {
      $where['type'] = array(array('neq',0));
      $admin = M("admin")->where(array('id'=>session('id')),$where)->select();
    }

    //修改企业名称
    public function savname1(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['Qyname'] = $val;
          $Qyname = M('admin')->where(array('Qyname'=>$val,'id'=>$id))->select();
          if($Qyname){
                 $result['errcode'] = 3;
                 $result['errmsg'] ='企业名称已被占用';
                 echo json_encode($result);die;
          }else{
                M("admin")->where(array('id'=>$id))->save($wh);
          }

          $result['errcode'] = 1;
          $result['errmsg'] ='修改成功';
           echo json_encode($result);    
    }
    //修改单张票提成比例
    public function savtc1(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id 
          $wh['tc'] = $val;//单张票提成比例
          $tc = M('admin')->where(array('tc'=>$val,'id'=>$id))->select();
          $admin = M('admin')->where(array('pid'=>$id))->select();
          foreach($admin as $k => $v){
            $shopid[$k] = $v['id'];
          }
          if($tc){
                 $result['errcode'] = 3;
                 $result['errmsg'] ='修改成功';
                 echo json_encode($result);die;
          }else{
                $admindata = M("admin")->where(array('id'=>$id))->save($wh);
                if($admindata!==false){
                  $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                  if($pids==false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                      $enterpri = M('enterprise')->where(array('aid'=>array('in',implode(",", $pids))))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                   }
                }
          }

           echo json_encode($result); 
    }
    //修改卡提成比例
     public function savcc1(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['cc'] = $val;//单张票提成比例
          $cc = M('admin')->where(array('cc'=>$val,'id'=>$id))->select();
          $admin = M('admin')->where(array('pid'=>$id))->select();
          foreach($admin as $k => $v){
            $shopid[$k] = $v['id'];
          }
          if($cc){
                 $result['errcode'] = 3;
                 $result['errmsg'] ='修改成功';
                 echo json_encode($result);die;
          }else{
                $admindata = M("admin")->where(array('id'=>$id))->save($wh);
                if($admindata!==false){
                  $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                  if($pids==false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                      $enterpri = M('enterprise')->where(array('aid'=>array('in',implode(",", $pids))))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                   }
                }
          }
          echo json_encode($result); 
     }

     //修改朋友圈提成比例
       public function savfc1(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['fc'] = $val;//单张票提成比例
          $admin = M('admin')->where(array('pid'=>$id))->select();
          foreach($admin as $k => $v){
            $shopid[$k] = $v['id'];
          }
          $fc = M('admin')->where(array('fc'=>$val,'id'=>$id))->select();
          if($fc){
                 $result['errcode'] = 3;
                 $result['errmsg'] ='修改成功';
                 echo json_encode($result);die;
          }else{
                $admindata = M("admin")->where(array('id'=>$id))->save($wh);
                if($admindata!==false){
                  $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                  if($pids==false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                      $enterpri = M('enterprise')->where(array('aid'=>array('in',implode(",", $pids))))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                   }
                }
          }

          
          echo json_encode($result);
    }
    //修改弟子提成比例
     public function savdc1(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['dc'] = $val;//单张票提成比例
          $dc = M('admin')->where(array('dc'=>$val,'id'=>$id))->select();
          $admin = M('admin')->where(array('pid'=>$id))->select();
          foreach($admin as $k => $v){
            $shopid[$k] = $v['id'];
          }
          if($dc){
                 $result['errcode'] = 3;
                 $result['errmsg'] ='修改成功';
                 echo json_encode($result);die;
          }else{
                $admindata = M("admin")->where(array('id'=>$id))->save($wh);
                if($admindata!==false){
                  $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                  if($pids==false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                      $enterpri = M('enterprise')->where(array('aid'=>array('in',implode(",", $pids))))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                   }
                }
          }

          
          echo json_encode($result);
    }
    //修改九大规划门票比例
    public function savnc1(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['nc'] = $val;//单张票提成比例

          $nc = M('admin')->where(array('nc'=>$val,'id'=>$id))->select();
          $admin = M('admin')->where(array('pid'=>$id))->select();
          foreach($admin as $k => $v){
            $shopid[$k] = $v['id'];
          }
          if($nc){
                 $result['errcode'] = 3;
                 $result['errmsg'] ='修改成功';
                 echo json_encode($result);die;
          }else{
                $admindata = M("admin")->where(array('id'=>$id))->save($wh);
                if($admindata!==false){
                  $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                  if($pids==false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                      $enterpri = M('enterprise')->where(array('aid'=>array('in',implode(",", $pids))))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                   }
                }
          }
          echo json_encode($result);
    }
    
    //删除大区总裁或财务总监
    public function delacc1(){
       $id = I('post.id');//删除当前的值id
       // echo '<pre>';print_r($id);die;
       $enterprise = M("admin")->where(array('id'=>$id))->delete();//删除大区总裁
       // $user = M('enterprise')->field('id')->where(array('aid'=>$id))->select();
       // if(empty($user)){
       //      $result['errcode'] = 1;
       //      $result['errmsg'] ='删除成功';
       //      die;
       // }
       // foreach($user as $k => $v){
       //  $delid[$k] = $v['id'];
       // }
       // $sakdelid['uid'] = array('in',$delid);
        // echo '<pre>';print_r($sakdelid);die;
       
        // $admin = M('enterprise')->where(array('aid'=>$id))->delete();//删除分公司

        // if($delid){
        //     $userdel = M('user')->where($sakdelid)->delete();//删除user表里的角色
        //     $salesman = M('salesman')->where($sakdelid)->delete();//删除销售人员 
        // }
        

       if($enterprise){
                 $result['errcode'] = 1;
                 $result['errmsg'] ='删除成功';
               
       }else{
                 $result['errcode'] = 2;
                 $result['errmsg'] ='删除失败';
       }
       echo json_encode($result);
    }

      //修改单张票提成比例
    public function leafle1(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['leafletstc'] = $val;//单张票提成比例
          // $tc = M('admin')->where(array('leafletstc'=>$val,'id'=>$id))->find();
          // if($tc){
          //        $result['errcode'] = 3;
          //        $result['errmsg'] ='修改成功';
          //        echo json_encode($result);die;
          // }else{
               
                $admindata = M("admin")->where(array('id'=>$id))->save($wh); //修改的自己金额
                if($admindata!==false){
                   $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                   if($pids==false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                      $enterpri = M('enterprise')->where(array("id"=>getcompanyId()))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                   }
                  
                }              
          // }
           echo json_encode($result); 
    }
    //修改卡提成比例
     public function leafle2(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['leafletscc'] = $val;//单张票提成比例
          // $cc = M('admin')->where(array('leafletscc'=>$val,'id'=>$id))->find();
          // if($cc){
          //        $result['errcode'] = 3;
          //        $result['errmsg'] ='修改成功';
          //        echo json_encode($result);die;
          // }else{
               $admindata = M("admin")->where(array('id'=>$id))->save($wh); //修改的自己金额
               if($admindata!==false){
                  $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                  if($pids == false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                  }else{
                      $enterpri = M('enterprise')->where(array("id"=>getcompanyId()))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                  }                 
               }                
          // }
          echo json_encode($result); 
     }

     //修改朋友圈提成比例
       public function leafle3(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['leafletsfc'] = $val;//单张票提成比例
          // $fc = M('admin')->where(array('leafletsfc'=>$val,'id'=>$id))->select();
          // if($fc){
          //        $result['errcode'] = 3;
          //        $result['errmsg'] ='修改成功';
          //        echo json_encode($result);die;
          // }else{
                $admindata = M("admin")->where(array('id'=>$id))->save($wh); //修改的自己金额
                if($admindata!==false){
                   $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                   if($pids == false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                      $enterpri = M('enterprise')->where(array("id"=>getcompanyId()))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                   }  
                }    
          // }       
          echo json_encode($result);
    }
    //修改弟子提成比例
     public function leafle4(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['leafletsdc'] = $val;//单张票提成比例
          // $dc = M('admin')->where(array('leafletsdc'=>$val,'id'=>$id))->select();
          // if($dc){
          //        $result['errcode'] = 3;
          //        $result['errmsg'] ='修改成功';
          //        echo json_encode($result);die;
          // }else{
                $admindata = M("admin")->where(array('id'=>$id))->save($wh); //修改的自己金额
                if($admindata!==false){
                   $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                   if($pids == false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                   }else{
                     $enterpri = M('enterprise')->where(array("id"=>getcompanyId()))->save($wh);
                     $result['errcode'] = 1;
                     $result['errmsg'] ='修改成功';
                   }
                }              
          // }
          echo json_encode($result);
    }
    //修改九大规划门票比例
    public function leafle5(){
          $val = I('post.val');//要修改的值
          $id = I('post.id');//修改当前的值id
          $wh['leafletsnc'] = $val;//单张票提成比例

          // $nc = M('admin')->where(array('leafletsnc'=>$val,'id'=>$id))->select();
          // if($nc){;
          //        $result['errcode'] = 3;
          //        $result['errmsg'] ='修改成功';
          //        echo json_encode($result);die;
          // }else{
               $admindata = M("admin")->where(array('id'=>$id))->save($wh); //修改的自己金额
               if($admindata!==false){
                  $pids=M("admin")->where(array('pid'=>$id))->getField("id",true);
                  if($pids == false){
                      $result['errcode'] = 4;
                      $result['errmsg'] ='修改成功';
                  }else{
                      $enterpri = M('enterprise')->where(array("id"=>getcompanyId()))->save($wh);
                      $result['errcode'] = 1;
                      $result['errmsg'] ='修改成功';
                  }  
               }             
          // }   
          echo json_encode($result);
    }

    public function savefee()
    {
        $id = I('post.id');
        $val = I('post.val');
        $admin = M('admin')->where(array('id'=>$id))->setfield('proportion',I('post.val'));
        $aid = M('admin')->where(array('pid'=>$id))->getField('id',true);

        $proportion = M('admin')->where(array('id'=>$id))->getField('proportion');




        if(!empty($aid)){
            $aids=implode(",", $aid);
            $where = "aid in ($aids) ";
            $eid = M('enterprise')->where($where)->getField('id',true);

            $eids = implode(",",$eid);

            $where = "uid in ($eids)";

            $bills = M("bill")->where($where)->select();
            foreach ($bills as $row) {
                $data['risk'] =round(($row["houston"]*$proportion/100),2);
                M("bill")->where(array('id'=>$row["id"]))->save($data);
            }

        }

        if($admin !== false)
        {
            echo json_encode(1);
        }
    }
    
    public function manageaccount(){
        $this->display();
    }

    public function manage(){
        $info = I("post.");
        $id = session('id');

        $ini['account'] = $info['account'];

        $ini['pwd'] = md5(I('post.gspwd'));
        $ini['type']=4;

        //qyname mname pid
        $admin=M('admin')->where(array("id"=>$id))->find();

        $ini['pid']=$id;
        $ini['qyname']=$admin["qyname"];
        $ini['mname']=$admin["mname"];
        $users = M("user")->where(array('account'=>I('post.account')))->find();
        $mobile = M("user")->where(array('mobile'=>I('post.gsmobile')))->find();
        $mobile2 = M("salesman")->where(array('mobile'=>I('post.gsmobile')))->find();
        $account = M('admin')->where(array('account'=>I('post.account')))->find();
        $account2 = M('salesman')->where(array('account'=>I('post.account')))->find();


        if(!empty($users) || !empty($account2)){
            echo json_encode(3);
        }else{
            if(!empty($mobile) || !empty($mobile1) || !empty($mobile2)){
                echo json_encode(4);
            }else{
                $user = M('admin')->add($ini);
                if($user !== false){
                    echo json_encode(1);
                }else{
                    echo json_encode(0);
                }
            }
        }
    }
    
    public function lisedmanage(){
        $p = I('post.page',1);
        $id=session("id");
        $type = I('post.type');
        $role=I('get.role');
        $limit = 6;

        $first = ($p-1) * $limit;
        $user = M('admin')->limit($first,$limit)->where(array('type'=>4,"pid"=>$id))->order('id desc')->select();//先不安uid区分，全排出来
        echo json_encode($user);
    }
    
}
