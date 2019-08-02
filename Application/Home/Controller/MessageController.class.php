<?php
namespace Home\Controller;
use Think\Controller;

class MessageController extends BaseController {

	public function index(){
       //SELECT * FROM lecturer.le_message where id =11 and (eid like "%,3,%" or eid like "3,%" or eid like "%,3")
       session("nav","message");
       $this->assign("meg",session("message"));
       $this->display();		
	}

  public function Msgdata()
  {
     $uid = session("id");
     $type=session("type");
     $yids=session('yid');

     if(($type==11||$type==12||$type==13||$type==10)&&$yids!=1){
         $uidc=$yids;
     }else{
         $uidc=0;
     }
    
     $p = I('post.page');
     $limit = 5;
     $first = ($p-1) * $limit;
     

    $where['uid'] = getcompanyId();
    $msgdata = M("message")->where($where)->order('id desc')->limit($first,$limit)->select();


    foreach($msgdata as $k => $v){
        if($type==10||$type==11||$type==12||$type==13){
            $msgdata[$k]['close']=1;
        }else{
            $msgdata[$k]['close']=0;
        }
        if($uid==1){
            $aarray=explode(",", $v['aid']);
            $flaga=in_array($uid, $aarray);//admin表里的
            
            $msgdata[$k]['flaga']=$flaga;
        }else if($uidc!=0){
            $aarray=explode(",", $v['aid']);
            $flaga=in_array($uidc, $aarray);//admin表里的
            $msgdata[$k]['flaga']=$flaga;
        }else if($type == 9 || $type == 10 || $type == 11 || $type == 12|| $type == 13 ){
            $aarray=explode(",", $v['aid']);
            $flaga=in_array($uid, $aarray);//admin表里的
            
            $msgdata[$k]['flaga']=$flaga;
        }else{
            $earray=explode(",", $v['eid']);
            $flag=in_array($uid, $earray);
            $msgdata[$k]['flag']=$flag;
        }
    }   

    echo json_encode($msgdata);
  }

  public function delmes()
  {
    $delmes = M('message')->where(array("id"=>I('get.id')))->delete();
    if($delmes !== false)
    {
      echo json_encode(1);
    }
  }
  public function saveid()
  {
      //先查询
      // echo '<pre>';print_r(session("id"));
      $id = I('post.mid');
      $uid=session("id");
      $messageid=M('message')->where(array('id'=>$id))->find();
      $typeArray=array("9","10","11","12","13");
      if($uid==1){
          if(empty($messageid["aid"])){
              $ini['aid'] = $uid;
          }else{
              $ini['aid'] = $messageid["aid"].",".$uid;
          }
      }else if(in_array(session('type'),$typeArray)){//admin
          $yid=session('yid');
          
          if($yid!=1){
              $uid=$yid;
          }
          if(empty($messageid["aid"])){
              $ini['aid'] = $uid;
          }else{
             $ini['aid'] = $messageid["aid"].",".$uid;
          }
      }
      else{
         if(empty($messageid["eid"])){
              $ini['eid'] = session("id");
          }else{
              $ini['eid'] = $messageid["eid"].",".session("id");
          }
      }
      
      $message = M('message')->where(array('id'=>I('post.mid')))->save($ini);
      $isread=isreadmessage();

      if($message !== false)
      {
        echo json_encode(array("result"=>1,"isread"=>$isread));
      }

  }
  public function page(){
     $uid = session("id");
     $type=session("type");
     $yid=session('yid');

     if($type==10&&$yid!=1){
         $uidc=$yid;
     }else{
         $uidc=0;
     }
    
//     $p = I('post.page');
//     $limit = 5;
//     $first = ($p-1) * $limit;
//
   
//     //admin表的登陆
//     if($uid==1){
//         $where=' 1=1 ';
//     }else if($type==10){//企业登陆
//        $admin = M('admin')->where(array('pid'=>$uid,'type'=>3))->select();
//        foreach($admin as $k => $v){
//          $adminid[$k] = $v['id'];//大区总裁ID
//        }
//        if(!empty($adminid)){
//            $data['aid'] = array('in', $adminid);
//        }
//
//        $enterprise = M('enterprise')->where($data)->select();
//        if(empty($enterprise)){
//          echo json_encode(111);die;
//        }
//        foreach($enterprise as $key => $val){
//          $eidshop[$key] = $val['id'];//分公司id
//        }
//        $where['uid'] = array('in', $eidshop);
//        // echo '<pre>';print_r($eid);die;
//    }else if($type==9){
//          $admin = M('admin')->where(array('id'=>$uid,'type'=>3))->find();
//          $aid = $admin['id'];//大区总裁id
//          $user = M('enterprise')->where(array('aid'=>$aid))->select();
//             if(empty($user)){
//          echo json_encode(111);die;
//        }
//          foreach($user as $k => $v){
//            $uidshop[$k] = $v['id'];
//          }
//          $where['uid'] = array('in', $uidshop);
//    }else if($type == 7 || $type == 6 || $type ==5){//讲师登陆
//        $user = M('user')->where(array('id'=>$uid))->find();
//        $where['uid'] = $user['uid'];
//    }else{//分公司财务登陆
//       $user = M('user')->where(array('id'=>$uid))->find();
//       $where['uid'] = $user['uid'];
//    }

    $where['uid'] = getcompanyId();
    $msgdata = M("message")->where($where)->order('id desc')->count();
     
 
    $msgdata = ceil($msgdata/5);
    
    echo json_encode($msgdata);
  }
}