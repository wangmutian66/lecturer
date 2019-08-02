<?php
namespace Home\Controller;
use Think\Controller;

class LossController extends Controller {

	public function index(){
    // GROUP BY YEAR(established_time) DESC,MONTH( established_time)

    $this->display();		
	}
  public function Highch()
  {
    $id=I('post.id');
    $bill = M('bill')
       ->where(array('uid'=>$id))
       ->where("YEAR(time) = YEAR(now())")
       ->field('sum(houston) as houston,sum(outs) as outs,MONTH(time) as time')
       ->group('MONTH(time)')
       ->select();

     
    $houston = array(); 
    $outsarr=array();
    $loss = array();
    foreach ($bill as $key => $value)
    {
      if($value['time']!=0){
        $houston[$value['time']-1] = (int)$value['houston'];
        $outsarr[$value['time']-1] = (int)$value['outs'];
        $loss[$value['time']-1] = $value['houston']-$value['outs'];
      }
      
    }
    
    for($i=0;$i<12;$i++){
        if(!isset($houston[$i])){
            $houston[$i]=0;
        }
        if(!isset($outsarr[$i])){
            $outsarr[$i]=0;
        }
        if(!isset($loss[$i])){
            $loss[$i]=0;
        }
    }
    
    $result=array('houston'=>$houston,'outsarr'=>$outsarr,'loss'=>$loss);

    echo json_encode($result);
  }
   
}