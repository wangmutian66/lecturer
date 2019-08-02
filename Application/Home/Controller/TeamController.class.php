<?php
namespace Home\Controller;
use Think\Controller;

class TeamController extends Controller {

    public function index(){
        session("menu","team");
        $type=session("type");
        $uid=getcompanyId();
        $comid=0;
        if($type!=9||$type!=10){
           $comid=$uid[1];
        }


        $enterpris=M('enterprise')->where(array("id"=>$uid))->select();
        $this->assign("comid",$comid);
        $this->assign("enterprise",$enterpris);
        $this->display();
    }

    public function Teams()
    {
         $p = I('post.page');
         $fcomid = I('post.fcomid','');
         $limit = 5;
         $first = ($p-1) * $limit;
         $uid=getcompanyId();//
         $sales=C('SALES_POWER_SESSION');
         $eid=0;
         if(session('type') == $sales)
         {
              $eid=session("id");
              $team_member = M("team_member")->where(array('eid'=>$eid))->getField('tid',true); //主管id 和成员 id 所在的团队

              $teams = M("team")->where(array('sid'=>$eid))->getField('id',true);   //上级领导所在的团队

              if(!empty($teams)&&!empty($team_member)){

                  $team_member=array_merge($teams,$team_member); //合并数组
                  $team_member=array_unique($team_member) ;// 清除重复数组
              }else if(!empty($teams)&&empty($team_member)){
                  $team_member=$teams;
              }


              
              $gid = implode(',',$team_member);

              //$where = array("id"=>array("in",$gid));
              $where["uid"]=getcompanyId();
              if(empty($gid)){

                  echo json_encode(array());
                  exit();
              }
              $where["id"]=array("in",$gid);


         }
         else
         {
               if($fcomid==''){
                   $where = array('uid'=>$uid);
               }else{
                   $where = array('uid'=>$fcomid);
               }

         }
         $team = M('team')->where($where)->limit($first,$limit)->order('id desc')->select();

        foreach ($team as $key => $value)
        {
          //上级领导
          $salesman = M('salesman')->field('name,role')->where(array('id'=>$value['sid'],'uid'=>$uid))->find();

          $result[$key]['tname'] = $value['tname'];
          $result[$key]['gname'] = $salesman['name'];
          $result[$key]['role'] = $salesman['role'];
          $result[$key]['id'] = $value['id'];
          $result[$key]['power']=2;



          $teams=M('team_member')->where(array("tid"=>$value["id"],"iszg"=>1))->getField("eid",true);

          if(!empty($teams)){
              $teamss=M('team_member')->where(array("tid"=>$value["id"],"eid"=>$eid,"iszg"=>1))->getField("eid",true);
              if($eid!=0 && !empty($teamss)){
                  $result[$key]['power']=1;
              }

              $where = array('id'=>array('in',implode(",", $teams)));

              $count = M('salesman')->where($where)->select();

              foreach ($count as $k => $v)
              {
                  $result[$key]['gsum'][] = $v;//管理总数

              }
              foreach ($result as $ks => $vs)
              {
                  $result[$ks]['gcount'] = count($vs['gsum']);  //管理总数
              }
          }
          $teamseid=M('team_member')->where(array("tid"=>$value["id"],"iszg"=>0))->getField("eid",true);

          if(!empty($teamseid)){

              $teamss=M('team_member')->where(array("tid"=>$value["id"],"eid"=>$eid,"iszg"=>0))->getField("eid",true);
              if($eid!=0 && !empty($teamss)){
                  $result[$key]['power']=0;
              }
              $wheres = array('id'=>array('in',implode(",", $teamseid)));

              $students = M('salesman')->where($wheres)->select();

              foreach ($students as $ksy => $ves)
              {
                  $result[$key]['esum'][] = $ves;//管理总数

              }
              foreach ($result as $kse => $vse)
              {
                  $result[$kse]['ecount'] = count($vse['esum']);  //管理总数
              }
          }
        }
        echo json_encode($result);
    }



    public function page()//财务总监分页
    {
        $uid=I('post.comid','');

        if($uid==''){
          $uid=getcompanyId();
        }

        $eid=session("id");
        $team_member = M("team_member")->where(array('eid'=>$eid))->getField('tid',true); //主管id 和成员 id 所在的团队

        $teams = M("team")->where(array('sid'=>$eid))->getField('id',true);   //上级领导所在的团队
        if(!empty($teams)){
            $team_member=array_merge($teams,$team_member); //合并数组
            $team_member=array_unique($team_member) ;// 清除重复数组
        }


        $gid = implode(',',$team_member);
        //$where = array("id"=>array("in",$gid));
        $where["uid"]=$uid;
        if(!empty($gid)){
            $where["id"]=array("in",$gid);
        }

        $team = M('team')->where($where)->count();
      
        $team = ceil($team/5);
        echo json_encode($team);
    }
     public function delteam()
    {
        $id = I('get.id');
        $team = M('team')->where(array('id'=>$id))->delete();
        if($team !== false)
        {
         $result['code'] = 1;
        }
        echo json_encode($result);
    }
    public function teamdata()
    {
      $type=session("type");
      $uid=getcompanyId();

      $enterprise=M('enterprise')->where(array("id"=>$uid))->field("id,Qyname")->select();
      $comid=0;
      $salesman = array();
      if($type!=9||$type!=10){
          $comid=$uid[1];
          $salesman = M('salesman')->where(array('uid'=>$uid))->select();
      }
      echo json_encode(array("salesman"=>$salesman,"enterprise"=>$enterprise));
    }
    public function changeenterprise(){
        $uid=I('post.uid');
        $salesman = M('salesman')->where(array('uid'=>$uid))->select();
        echo json_encode($salesman);
    }

    public function addteam()
    {
      $type=session("type");
      $uid=session("uid");
      
      $post = I('post.');
      $ini['tname'] = $post['tname'];
      $ini['sid'] = $post['sid'];
      $ini['uid'] = $post['uid'];
      
      if($type!=10||$type!=9){
          $uid=getcompanyId();
         // $ini['uid'] = $uid[1];
      }
      
      $team = M('team')->add($ini);

      $gidarray=explode(",",$post['gid']);
      $eidarray=explode(",",$post['eid']);
      foreach ($gidarray as $row){
          $arr=array();
          $arr["tid"]=$team;
          $arr["eid"]=$row;
          $arr["iszg"]=1;
          M('team_member')->add($arr);
      }
      foreach ($eidarray as $row){
          $arr=array();
          $arr["tid"]=$team;
          $arr["eid"]=$row;
          $arr["iszg"]=0;
          M('team_member')->add($arr);
      }
 
     
      if($ini != false)
      {
        echo json_encode(1);
      }

    }
    public function teaminfo()
    {
      $uid=getcompanyId();  
      $id=I('post.id');
      $eids=$gids=array();


      $sname = M('team')->where(array('id'=>$id))->find();
      $salesman = M('salesman')->where(array('uid'=>$sname["uid"]))->select();
      $teameid1=M("team_member")->where(array('tid'=>$id,'iszg'=>1))->getField("eid",true);

      $teameid0=M("team_member")->where(array('tid'=>$id,'iszg'=>0))->getField("eid",true);
      if(!empty($teameid1)){
           $gids = M('salesman')->where(array('uid'=>$uid,'id'=>array('in',implode(",", $teameid1))))->select();
      }
      if(!empty($teameid0)){
           $eids = M('salesman')->where(array('uid'=>$uid,'id'=>array('in',implode(",", $teameid0))))->select();
      }

      $enterprise=M('enterprise')->where(array("id"=>$uid))->field("id,Qyname")->select();
      $result = array('salesman'=>$salesman,'enterprise'=>$enterprise,'sname'=>$sname,'gids'=>$gids,'eids'=>$eids);

      echo json_encode($result);
    }
    public function saveteam()
    {
      //$uid=getcompanyId();

      $post = I('post.');
      $uid=$post['uid'];
      $ini['tname'] = $post['tname'];
      $ini['sid'] = $post['sid'];
      $ini['uid'] = $uid;

      $gid = $post['gid'];
      $eid = $post['eid'];
      M('team_member')->where(array('tid'=>$post['id'],"eid"=>array("not in",$gid),"iszg"=>1))->delete();
      M('team_member')->where(array('tid'=>$post['id'],"eid"=>array("not in",$eid),"iszg"=>0))->delete();

      $gidarray=explode(",",$gid);
      $eidarray=explode(",",$eid);

      foreach ($gidarray as $row){
          M('team_member')->add(array('tid'=>$post['id'],"eid"=>$row,"iszg"=>1));

      }

      foreach ($eidarray as $row){
          M('team_member')->add(array('tid'=>$post['id'],"eid"=>$row,"iszg"=>0));
      }

      $teammber=M('team_member')->where(array('tid'=>$post['id']))->getField("eid",true);

      $sal_uid=M('salesman')->where(array("id"=>array("in",implode(",",$teammber))))->getField("uid");
      $result["error"]=0;

      if($sal_uid!=$uid){
          $result["error"]=1;
          $result["msg"]="所选公司与团队成员不在一个公司";
          echo json_encode($result);
          exit();
      }
      $team = M('team')->where(array('id'=>$post['id']))->save($ini);

      if($ini != false)
      {
        echo json_encode($result);
      }
    }
    
    public function delmember(){
        $tid=I('post.tid');
        $eid=I('post.eid');
        M('team_member')->where(array('tid'=>$tid,'eid'=>$eid))->delete();
    }
    
    public function addchengyuan(){
        $tid=I('post.tid');
        $eid=I('post.eid');
        $type=I('post.type');
        $tfind=M('team_member')->where(array("tid"=>$tid,"eid"=>$eid))->find();
        $team=M('team')->where(array("id"=>$tid,"sid"=>$eid))->find();

        $arr["error"]=0;
        if($tfind||$team){
            $arr["error"]=1;
            $arr["msg"]="成员名称重复";
            echo json_encode($arr);
            exit();
        }else{
            M('team_member')->add(array("tid"=>$tid,"eid"=>$eid,'iszg'=>$type));
        }
        echo json_encode($arr);
        
    }
    
}