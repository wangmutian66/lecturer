<?php
namespace Home\Controller;
use Think\Controller;
class BillController extends BaseController {

    public function index(){
        $getid=I('get.id');
        $lecturer=M('enterprise')->where(array("id"=>$getid))->getField("Qyname");
        $finatype=session('finatype');
        $finatype=!isset($finatype)?0:$finatype;
        $this->assign("lecturername",$lecturer);
        $this->assign("finatype",$finatype);
        $this->assign("getid",$getid);
        $this->assign("ad",I('get.act'));
        $this->display();
    }
    
    
    public function tasklist(){
        $post = I('post.');
        $stype = $post["stype"];    //类别
        $smoney = $post["smoney"];    //进出账
        if($smoney == 1){ //进账
            $map["fid"]=array('neq','');
        }else if($smoney == 2){ //出账
            $map["outs"]=array('neq',0);
        }
        if($stype != 0){
            $map["type"]=$stype;
        }
        if(!empty($post["lsor"])){ //收缩客户拒名称
            $name = trim($post["lsor"]);
            $ini['uid'] = $post["getid"];
            $ini['name'] = array("like","%$name%");
            $ids = M('information')->field('id')->where($ini)->select();
            foreach ($ids as $key => $value) {
                $isd['id'] = $value['id'];
            }
            if(empty($isd)){
                $result["error"]=1;
                $result["msg"]="为搜索到客户名称";
                echo json_encode($result);
                exit();
            }
            $map['fid'] = array('in',$isd);
        }
        $ntime = $post["timeyear"].'-'.$post["timemonth"].'-'.$post["timeday"]; //开始时间
        $etime = $post["beforeyea"].'-'.$post["datayear"].'-'.$post["day"]; //结束时间
        $map["uid"]=$post["getid"];
        $map["time"]=array(array('EGT',$ntime),array('ELT',$etime),'and');
        if($post["xuanxiang"]!=0){
            $map["deposit"]=1;
        }
        $bill = M('bill');


        $data = $bill
            ->where($map)
            ->order('time desc,id desc')
            ->select();   //查询公司所有财务信息

        foreach ($data as $k=>$row){
            $data[$k]["fname"]=M('information')->where(array("id"=>$row["fid"]))->getField("name");
            $data[$k]["cname"]=M('course')->where(array("id"=>$row["cid"]))->getField("classname");
            $data[$k]["courseorder"]=M('course_com_order')->where(array("id"=>$row["orderid"]))->find();
        }
        if($stype != 0 ){
            $map["type"]=array(array('neq',0),array('eq',$stype),'and');
        }else{
            $map["type"]=array('neq',0);
        }

        $jz = $bill
            ->field('SUM(houston) as jz')
            ->where($map)
            ->select();   //查询公司进账

        unset($map["type"]);
        if($stype == 0){
            $cz = $bill
                ->field('SUM(outs) as cz')
                ->where($map)
                ->select();   //查询公司进账
        }
        
        foreach ($data as $key => $value) {
            if(empty($value['name'])){
                $data[$key]['name'] = '';
            }
            if(empty($value['period'])){
                $data[$key]['period'] = '';
            }
            if(empty($value['reason'])){
                $data[$key]['reason'] = '';
            }
            if(empty($value['remarks'])){
                $data[$key]['remarks'] = '';
            }           
        }
        foreach ($data as $key => $value) {
            if(!empty($value['name'])){
                if($value['type'] == 0){
                    unset($data[$key]);
                }
            }
        }
        
        if(!empty($data)){
            $arr['code'] = 1;
            $arr['datalist'] = $data;
            $arr['jz'] = $jz[0]['jz'];
            $arr['cz'] = $cz[0]['cz'];
            $arr['zz'] = $jz[0]['jz']-$cz[0]['cz'];
        }else{
            $arr['code'] = 0;
            $arr['datalist'] = '';
        }
        echo json_encode($arr);
    }
    
    public function Alsomoney()
    {
        $post = I('post.');

        if(!empty($post['money']) && !empty($post['Alsotime']) && !empty($post['period'])){
            if(is_numeric($post['period'])){ //验证期数格式
                $data = M('bill')->where(array('id'=>$post['id'],'uid'=>$post['getid']))->find();   //查询初始订单信息   
                if(!empty($data)){
                    $houston = M('bill')->field('SUM(houston) as sun')->where(array('pid'=>$post['id'],'uid'=>$post['getid']))->select();
                    $zong = $houston[0]['sun']+$data['houston']+$post['money'];
                    $numls = $data['amount']-$zong;
                    $period = explode('/', $data['period']);
                    if($post['period'] == $period[1]){//最后一期还款
                        if($numls == 0){
                            $ini['pid'] = $data['id'];
                            $ini['uid'] = $data['uid'];
                            $ini['num'] = $data['num'];
                            $ini['amount'] = $data['amount'];
                            $ini['houston'] = $post['money'];
                            $ini['deposit'] = 0;
                            $ini['period'] = $post['period'].'/'.$period[1];
                            $ini['type'] = $data['type'];
                            $ini['time'] = $post['Alsotime'];
                            $ini['fid'] = $data['fid'];
                            $ini['cid'] = $data['cid'];
                            $ini['orderid'] = $data['orderid'];

                            $status = M('bill')->add($ini);
                            if(!empty($status) || $status !== false){
                                $fname = M('information')->where(array('id'=>$data['fid']))->getField('name');
                                $ini["uid"]=$data['uid'];
                                $ini["stuname"]=$fname;
                                $ini["time"]=date("Y-m-d H:i:s");
                                $ini["content"]=" 申报弟子学员 在".$post['period']."/".$period[1]."次还款中已完款 ".$post['money']."元";
                                // echo '<pre>';print_r($data['orderid']);die;
                                 
                                M('course_com_order')->where(array('id'=>$data['orderid']))->save(array('typed'=>1));//更改成入账  
                                $sta = M('message')->add($ini);
                                if($sta){
                                    $arr['code'] = 1;
                                    $arr['errmsg'] = '还款完毕!';  
                                }else{
                                    $arr['code'] = 0;
                                    $arr['errmsg'] = '保存信息失败!';
                                }
                            }else{
                                $arr['code'] = 0;
                                $arr['errmsg'] = '还款失败!';
                            }
                        }else{
                            $arr['code'] = 0;
                            $arr['errmsg'] = '进账金额不正确！';
                        }
                    }else{
                        if($numls > 0){
                            $ini['pid'] = $data['id'];
                            $ini['uid'] = $data['uid'];
                            $ini['num'] = $data['num'];
                            $ini['amount'] = $data['amount'];
                            $ini['houston'] = $post['money'];
                            $ini['deposit'] = 0;
                            $ini['period'] = $post['period'].'/'.$period[1];
                            $ini['type'] = $data['type'];
                            $ini['time'] = $post['Alsotime'];
                            $ini['fid'] = $data['fid'];
                            $ini['cid'] = $data['cid'];
                            $ini['orderid'] = $data['orderid'];
                            $status = M('bill')->add($ini);
                            if(!empty($status) || $status !== false){
                                $fname = M('information')->where(array('id'=>$data['fid']))->getField('name');
                                $ini["uid"]=$data['uid'];
                                $ini["stuname"]=$fname;
                                $ini["time"]=date("Y-m-d H:i:s");
                                $ini["content"]=" 申报弟子学员 在".$post['period']."/".$period[1]."次还款中已还款 ".$post['money']."元";
                                $sta = M('message')->add($ini);
                                if($sta){
                                    $arr['code'] = 1;
                                    $arr['errmsg'] = '还款完毕!';  
                                }else{
                                    $arr['code'] = 0;
                                    $arr['errmsg'] = '保存信息失败!';
                                }
                            }else{
                                $arr['code'] = 0;
                                $arr['errmsg'] = '还款失败!';
                            }
                        }else{
                            if($numls < 0){
                                $arr['code'] = 0;
                                $arr['errmsg'] = '当前还款期数进账金额超出总金额'.$numls.'元请重新填写！';
                            }else{
                                $arr['code'] = 0;
                                $arr['errmsg'] = '当前期数不能还全款！';
                            }  
                        }
                    }
                }else{
                    $arr['code'] = 0;
                    $arr['errmsg'] = '订单信息错误！';
                } 
            }else{
                $arr['code'] = 0;
                $arr['errmsg'] = '分期数格式不正确！';
            }
        }else{
            $arr['code'] = 0;
            $arr['errmsg'] = '缺少信息请重新填写完整信息！';
        }
        echo json_encode($arr);
    }

/***************************************上传表格数据************************************/
    public function uploadCate() 
    {
       
        $uid = session('id'); //个人用户id
        $getid = I('post.getid');   //企业id
        $map['id'] = $uid;
        $map['uid'] = $getid;
        $map['finatype'] = 1;
        $cstatus = M('user')->where($map)->find();
        unset($map);
//        if(empty($cstatus)){
//            $data['code'] = 0;
//            $data['errmsg'] = '您没有操作权限！';
//            echo json_encode($data);
//            die();
//        }

        //Excel配置文件
        $config = array(
            'maxSize'    =>    553145728,
            'rootPath'   =>    './Public/Uploads/',
            'savePath'   =>    $uid.'excel/',    
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
        $filename = "./Public/Uploads/".$uid."excel/".$info['file']['savename'];

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
        unlink("./Public/Uploads/".$uid."excel/".$info['file']['savename']);
        rmdir("./Public/Uploads/".$uid."excel");
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

        $data['errmsg']=array();
        $k=0;
        array_shift($excelDatas);//去数组头部
        
        foreach ($excelDatas as $key => $value) {  //检测日期格式
           
            if(strlen($value[0]) < 5){
                $hang = $k;
//                 $data['code'] = 0;
//                 $data['errmsg'][] = '第'.$hang.'行日期格式不正确！';
//                 $data['sample'] = '格式:例如2017年1月1日';
            }else{
                $excelDatas[$key][0] = date('Y-m-d',$shared->ExcelToPHP($value[0]));
            }
            $k++;
        }
        
        foreach ($excelDatas as $key => $value) {
            if($value[0] == ''&&$value[1] == ''&&$value[2] == ''&&$value[3] == ''&&$value[4] == ''&&$value[5] == ''&&$value[6] == ''&&$value[7] == ''&&$value[8] == ''&&$value[9] == ''&&$value[10] == ''&&$value[11] == '')
            {
                unset($excelDatas[$key]);
            }
        }

        foreach ($excelDatas as $key => $value) {
            $datalist[$key]['time'] = $value[0];
            $datalist[$key]['fidname'] = $value[1];
            $datalist[$key]['repaymentid'] = $value[2]; //repayment
            $datalist[$key]['type'] = $value[3];
            $datalist[$key]['num'] = $value[4];
            $datalist[$key]['amount'] = $value[5];
            $datalist[$key]['houston'] = $value[6];
            $datalist[$key]['deposit'] = $value[7];
            $datalist[$key]['period'] = $value[8];
            $datalist[$key]['outs'] = $value[9];
            $datalist[$key]['reason'] = $value[10];
            $datalist[$key]['remarks'] = $value[11];
        }
        if(count($excelDatas)>101){
            $data["error"]=1;
            $data["errormsg"]="上传数据已经超出100条，已经超出服务器所承受范围，请重新分配上传！";
            echo json_encode($data);
            exit();
        }
           
        foreach ($datalist as $key => $value) { //检测学员姓名格式
            $hang = $key+2;


            //period  if(strstr($a,"exe")) {
            
           
            if(empty($value['outs']) && empty($value['reason']) && empty($value['remarks'])){  //进账检测数据
                /*
                if(strstr($value["period"],"/")==""){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 还款期数应以分数显示';
                    $datalist[$key]['error']=1;
                }*/

                if(empty($value['fidname'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 客户姓名姓名为空';
                    $datalist[$key]['error']=1;
                }

                // else if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $value['fidname'])){
                //     $data['code'] = 0;
                //     $data['errmsg'][] = '行数：'.$hang.' 客户姓名格式错误';
                //     $data['sample'] = '格式:例如"张三"';
                //     $datalist[$key]['error']=1;
                // }
                
                
                // if(empty($value['phone'])){
                //     $data['code'] = 0;
                //     $data['errmsg'][] = '行数：'.$hang.' 手机号为空';
                //     $datalist[$key]['error']=1;
                // }else if(!preg_match("/^(1(([35][0-9])|(47)|[8][0126789]))\d{8}$/", $value['phone'])){
                //     $data['code'] = 0;
                //     $data['errmsg'][] = '行数：'.$hang.' 手机号格式错误';
                //     $data['sample'] = '格式:例如15804652776';
                //     $datalist[$key]['error']=1;                  
                // }
                
                if(empty($value['type'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 类型为空';                  
                    $datalist[$key]['error']=1;
                }
                
                if(empty($value['num'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 数量为空';
                    $datalist[$key]['error']=1;
                }
              
                if(empty($value['amount'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 总金额为空';
                    $datalist[$key]['error']=1;
                }
                /*
                if(empty($value['period'])){
                    $data['code'] = 0;
                    $data['errmsg'][] ='行数：'.$hang.' 还款期数为空';  
                    $datalist[$key]['error']=1;
                }
                */
                if(empty($value['deposit'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 定金状态(是/否)为空';
                    $datalist[$key]['error']=1;
                }

                $fidname=$value['fidname'];
                $fid=M('information')->where(array("name"=>$fidname,'uid'=>$getid,'deletemark'=>0))->getField("id");
                if(empty($fid)){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 客户名称不存在或已在回收站';
                    $datalist[$key]['error']=1;
                }else{
                    $datalist[$key]['fid']=$fid;
                }
                if($value['type'] == '小票' || $value['type'] == '卡会员' || $value['type'] == '朋友圈' || $value['type'] == '弟子学员' || $value['type'] == '九大规划门票'){ //类别验证
                    
                    if($value['type'] == '弟子学员'){  //当类别为弟子时
                        if(!preg_match("/^[0-9]\d*\/[1-9]\d*$/", $value['period'])){
                            $data['code'] = 0;
                            $data['errmsg'][] = '行数:'.$hang.' 还款期数格式错误';
                            $datalist[$key]['error']=1;
                        }
                        
                    }else{

                        if($value['deposit'] == '否'){
                            if($value['type'] != '弟子学员'){
                                if(($value['amount'] != $value['houston'])){ 
                                    $data['code'] = 0;
                                    $data['errmsg'][] = '行数：'.$hang." 该行购买{$value['type']}总金额与进账数应相等";
                                    $datalist[$key]['error']=1;
                                }
                            }
                            
                            if(preg_match("/^\d{1,10}(\.\d+)?$/", $value['amount'])){
                                if(preg_match("/^\d{1,10}(\.\d+)?$/", $value['houston'])){
                                    if(($value['amount'] != $value['houston']) && $value['period']=="1/1"){  
                                        $data['code'] = 0;
                                        $data['errmsg'][] = '行数：'.$hang.' 该行总金额与进账数应相等';
                                        $datalist[$key]['error']=1;
                                    }
                                }else{
                                    $data['code'] = 0;
                                    $data['errmsg'][] = '行数：'.$hang.' 进账金额格式错误';
                                    $data['sample'] = '格式:例如"100.00"';
                                    $datalist[$key]['error']=1;
                                }
                            }else{
                                $data['code'] = 0;
                                $data['errmsg'][] = '行数：'.$hang.' 总金额格式错误';
                                $data['sample'] = '格式:例如"100.00"';
                                $datalist[$key]['error']=1;
                            }
                        }else{
                            /*
                            if(strstr($value['period'],'0/')==""){
                                $data['code'] = 0;
                                $data['errmsg'][] = '行数：'.$hang.' 还款期数应以“0/”开头显示';
                                $datalist[$key]['error']=1;
                            }*/
                        }

                    }
                }else{
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 类别应为(小票/卡会员/朋友圈/弟子学员/九大规划门票)';
                    $data['sample'] = '格式:例如"票"';
                    $datalist[$key]['error']=1;                    
                }
                if(!is_numeric($value['num'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '第'.$hang.' 行数量格式不正确！';
                    $data['sample'] = '格式:例如"3"';
                    $datalist[$key]['error']=1;
                }
                if($value['type'] != '弟子学员' && $value['deposit']=="是"){
                    $data['code'] = 0;
                    $data['errmsg'][] = '第'.$hang.' 非弟子学员不应该填写“是”定金';
                    $data['sample'] = '格式:例如"3"';
                    $datalist[$key]['error']=1;
                }
                // if(!empty($getid)){
                //     $map['qid'] = $getid;
                //     $map['name'] = $value['name'];
                //     $map['mobile'] = $value['phone'];
                //     $id = M('student')->field('id')->where($map)->find();
                   
                //     if(empty($id)){
                //         $uid=session("id");
                         
                //         $uuid=M('user')->where(array('id'=>$uid))->getField("uid");
                //         $array["qid"]=$uuid;
                //         $array["mobile"]=$value["phone"];
                //         $array["name"]=$value["name"];
                //         M('student')->add($array);
                        
                //         $data['code'] = 0;
                //         $data['msg'][] = '行数：'.$hang.' 学员'.$value["name"]." ".$value["phone"]."为新增学员";
                //         unset($array);
                //         unset($map);
                //     }
                // }else{
                //     $data['code'] = 0;
                //     $data['errmsg'] = '请重新登陆';
                //     echo json_encode($data);
                //     die();
                // }
               
             
                
                
                //在相同时间下客户
                
//                if(!empty($value["time"])&& !empty($fid)){
//                    $arr=array();
//                    $arr["time"]=$value["time"];
//                    $arr["fid"]=$fid;
//                    $arr["period"]=$value["period"];
//                    $arr["amount"]=$value["amount"];
//                    $arr["houston"]=$value["houston"];
//
//                    $bill=M('bill')->where($arr)->find();
             
//                    unset($arr);
                    if($value['type'] == '弟子学员' && $value['deposit']=="是"){
                        $period=explode("/",$value["period"]);
                        $repaymentid=$value["repaymentid"];
                        if($period[0]!=0){
                            $data['code'] = 0;
                            $data['errmsg'][] = '行数：'.$hang.' 客户'.$value["fidname"]." 定金还款期数应该为“0/".$period[1]."”";
                            $datalist[$key]['error']=1;
                        }
                        if($repaymentid!=""){
                            $data['code'] = 0;
                            $data['msg'][] = '行数：'.$hang.' 客户'.$value["fidname"]." 将以定金入账，则不需要填写还款ID";
                        }
                    }
                    if($value['type'] == '弟子学员' && $value['deposit']=="否"){
                        if(empty($value["repaymentid"])&&($value["amount"]!=$value["houston"])){  //还款id 未填写并且 进账等于全款
                            $data['code'] = 0;
                            $data['errmsg'][] = '行数：'.$hang.' 客户'.$value["fidname"]." 未填写还款ID";
                            $datalist[$key]['error']=1;
                        }else{
                            $billid=M('bill')->where(array('deposit'=>1,'type'=>4,'fid'=>$fid,'id'=>$value["repaymentid"]))->find();

                            if($billid){
                                if($billid["amount"]!=$value["amount"]){
                                    $data['code'] = 0;
                                    $data['errmsg'][] = '行数：'.$hang.' 总金额'.$value["amount"].'与定金总金额'.$billid["amount"]."不符";
                                    $datalist[$key]['error']=1;
                                }

                                if(($billid["amount"]==$value["amount"])){
                                    $data['code'] = 0;
                                    $data['msg'][] = '行数：'.$hang.' 客户'.$value["fidname"]." 还款数据与ID:".$billid["id"]."定金数据关联";
                                    $datalist[$key]['pid']=$billid["id"];
                                }
                                $bi=explode("/",$billid["period"]);
                                $va=explode("/",$value["period"]);

                                if($bi[1]!=$va[1]){
                                    $data['code'] = 0;
                                    $data['errmsg'][] = '行数：'.$hang.' 还款期数'.$value["period"].'与定金还款期数'.$billid["period"]." 分母不同";
                                    $datalist[$key]['error']=1;
                                }
                                $billidisset=M('bill')->where(array('type'=>4,'fid'=>$fid,'pid'=>$value["repaymentid"],'period'=>$value["period"]))->find();
                                if($billidisset){
                                    $data['code'] = 0;
                                    $data['errmsg'][] = '行数：'.$hang.' 还款期数'.$value["period"].'已存在';
                                    $datalist[$key]['error']=1;
                                }
                                if($va[0]>1){
                                    $billidisset=M('bill')->where(array('type'=>4,'fid'=>$fid,'pid'=>$value["repaymentid"],'period'=>($va[0]-1)."/".$va[1]))->find();

                                    if(empty($billidisset)){
                                        $data['code'] = 0;
                                        $data['errmsg'][] = '行数：'.$hang.' 还款期数'.($va[0]-1)."/".$va[1].'不存在';
                                        $datalist[$key]['error']=1;
                                    }
                                }else if($va[0]==0){
                                    $data['code'] = 0;
                                    $data['errmsg'][] = '行数：'.$hang.' 还款期数不应该为'.$value["period"];
                                    $datalist[$key]['error']=1;
                                }



                            }else{
                                $data['code'] = 0;
                                $data['errmsg'][] = '行数：'.$hang.' 客户'.$value["fidname"]." 未找到还款ID";
                                $datalist[$key]['error']=1;
                            }
                        }
                        if($value["houston"]>$value["amount"]){
                            $data['code'] = 0;
                            $data['errmsg'][] = '行数：'.$hang.' 进账金额不能大于总金额';
                            $datalist[$key]['error']=1;
                        }
                    }

                    
//                }
                
            }else if(empty($value['type']) && empty($value['num']) && empty($value['amount']) && empty($value['houston']) && empty($value['deposit']) && empty($value['period']) ){  //出账检测数据
                //&& !empty($value['outs']) && !empty($value['reason'])
                if(empty($value['outs'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 出账为空';
                }
                if(empty($value['reason'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 原因为空';
                }
                
                if(!empty($arr["outs"])&& !empty($arr["reason"])){
                    $arr=array();
                    $arr["outs"]=$value["outs"];
                    $arr["reason"]=$value["reason"];
                    $bill=M('bill')->where($arr)->find();
                    unset($arr);
                    if($bill){
                        $data['code'] = 0;
                        $data['msg'][] = '行数：'.$hang.' 与已有数据合并';
                        $datalist[$key]['hebin']=$bill["id"];
                    }
                    
                }
                
//                if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $value['reason'])){
//                    $data['code'] = 0;
//                    $data['errmsg'][] = '行数：'.$hang.' 原因格式不正确！';
//                    $data['sample'] = '格式:例如"交水费"';
//                    $datalist[$key]['error']=1;
//                }

                if(!preg_match("/^\d{1,10}(\.\d+)?$/", $value['outs'])){
                    $data['code'] = 0;
                    $data['errmsg'][] = '行数：'.$hang.' 出账格式不正确！';
                    $data['sample'] = '格式:例如"100.00"';
                    $datalist[$key]['error']=1;
                }
                
            }else{
                $data['code'] = 0;
                $data['errmsg'] = '第'.$hang.'行数据不正确！';
                $data['sample'] = '请仔细检查进账出账';
                echo json_encode($data);
                die();
            }
            
        }
        $sjts = count($datalist);
        if($sjts > 100){
            $data['code'] = 0;
            $data['errmsg'] = '上传数据不能大于100条';
        }else{
            $data['code'] = 1;
            $data['datalist'] = $datalist;
        }
        echo json_encode($data);
    }
    function down(){
  
        $file = "Public/Uploads/download/test.xlsx";
       
        if(is_file($file)){
            $length = filesize($file);
            header("Content-Description: File Transfer");
            header('Content-type: application/vnd.ms-excel;');
            header('Content-Length:' . $length);
            $filename="财务模板";
            $filename=iconv("utf-8", "gb2312", $filename);
            header('Content-Disposition: attachment; filename='.$filename.'.xlsx');
            readfile($file);
            exit;               
            
        }
    }

    public function uploadCategory() { //验证数据
        $uid = session('id');
        $getid = I('post.getid');   //企业id
        $excelDatas = I('post.datalist');

        if(!empty($excelDatas)){
            //事务处理（对数据库进行添加操作和修改操作）
            foreach ($excelDatas as $key => $value) {
                $hang = $key+2;
                $excelDatas[$key]['uid'] = $getid;
                if($value['deposit'] == '是'){
                    $excelDatas[$key]['deposit'] = 1;
                }else if($value['deposit'] == '否'){
                    $excelDatas[$key]['deposit'] = 0;
                }
                //小票/卡会员/朋友圈/弟子学员/九大规划门票
                if($value['type'] == '小票'){
                    $excelDatas[$key]['type'] = 1;
                }else if($value['type'] == '卡会员'){
                    $excelDatas[$key]['type'] = 2;
                }else if($value['type'] == '朋友圈'){
                    $excelDatas[$key]['type'] = 3;
                }else if($value['type'] == '弟子学员'){
                    $excelDatas[$key]['type'] = 4;
                }else if($value['type'] == '九大规划门票'){
                    $excelDatas[$key]['type'] = 5;
                }
            }
            foreach ($excelDatas as $key => $value) {
                $hang = $key+2;
                if($value['type'] == 4){
                    $fq = explode('/', $value['period']);
                    if($fq[0] == 0){

                        $map['fid'] = $value['fid'];

                        $map['type'] = 4;
                        $map['uid'] = $getid;
                        $map['amount'] = $value['amount'];
                        $map['houston'] = $value['houston'];
                        $map['deposit'] = $value['deposit'];
                        $map['period'] = $value['period'];
                        $map['gonetype'] = $value['gonetype'];
                        $one_data = M('bill')->where($map)->find();
                        unset($map);
                        if($one_data){
                            $data['code'] = 0;
                            $data['errmsg'] = '第'.$hang.'行数据已存在！';
                            $data['sample'] = '请仔细检查';
                            echo json_encode($data);
                            die();
                        }
                    }
                }
            }
         
            $uid=session("id");
            $uuid=M('user')->where(array("id"=>$uid))->getField("uid"); //公司id
            $adminid=M('enterprise')->where(array("id"=>$uuid))->getField("aid");//找大区ID
            $aaid=M('admin')->where(array('id'=>$adminid))->getField("pid");
            $aaidfind=M('admin')->where(array('id'=>$aaid))->find();
            $proportion=$aaidfind["proportion"]; //该企业下的风险金

            M()->startTrans();

            foreach ($excelDatas as $key => $value) {
                if(empty($value["pid"])){
                    $value["pid"]=0;
                }
                 $value["risk"]=$value["houston"]*$proportion/100;
                 if(!empty($value["huankuan"])){
                     $value["pid"]=$value["huankuan"];
                 }else  if(!empty($value["hebin"])){
                    $data=M('bill')->where(array("id"=>$value["hebin"]))->save($value);
                    //$data = M('bill')->add($value);
                    if(!$data)
                    {
                        M()->rollback();
                    }
                }else if(empty($value["error"])){
                    
                    //添加message
                    if($value['type']==4){
                        $fname = M('information')->where(array('id'=>$value['fid']))->getField('name');
                        if($value['deposit'] == 1){ //交定金

                            $arr=array();
                            
//                             $arr["cid"]=$uid;
//                             $userid=M('user')->where(array("id"=>$uid))->find(); 
                        
                            $arr["uid"]=$getid;
                            $arr["stuname"]=$fname;
                            $arr["time"]=date("Y-m-d H:i:s");
                            $arr["content"]=" 申报弟子学员 交定金 ".$value['houston']."元";
                            M('message')->add($arr);
                            unset($arr);
                        }else{
                            $arr=array();
                            if(($value['amount'] == $value['houston'])){  //交全款
                                $arr["cid"]=$uid;
                                //$userid=M('user')->where(array("id"=>$uid))->find(); 
                                $arr["uid"]=$getid;
                                $arr["stuname"]=$fname;
                                $arr["time"]=date("Y-m-d H:i:s");
                                $arr["content"]=" 申报弟子学员 交全款 ".$value['houston']."元";
                                M('message')->add($arr);
                                unset($arr);
                            }else{//还款
                                $fq = explode('/', $value['period']);
                                if($fq[0] == $fq[1])
                                {
                                    $arr["content"]=" 申报弟子学员 在{$value["period"]}次还款中已完款 ".$value['houston']."元";
                                }else{
                                    $arr["content"]=" 申报弟子学员 在{$value["period"]}次还款中还款".$value['houston']."元";
                                }
                                $arr["cid"]=$uid;
                                //$userid=M('user')->where(array("id"=>$uid))->find(); 
                                $arr["uid"]=$getid;
                                $arr["stuname"]=$fname;
                                $arr["time"]=date("Y-m-d H:i:s");
                                M('message')->add($arr);
                                unset($arr);
                            }
                        }
                         
                    }

                    //message 结束
                    $value['uid']=$getid;
                    unset($value["information"]);
                    //修改状态
                    $orderid=$value["orderid"];
                    switch ($value["type"]){
                        case 1:
                            $result["typep"]=1;
                            break;
                        case 2:
                            $result["typec"]=1;
                            break;
                        case 3:
                            $result["typef"]=1;
                            break;
                        case 4:
                            $result["typed"]=1;
                            break;
                        case 5:
                            $result["typec"]=1;
                            break;
                    }
                    M('course_com_order')->where(array("id"=>$orderid))->save($result);

                    $data = M('bill')->add($value);


                    if(!$data)
                    {
                        M()->rollback();
                    }
                    
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
/***************************************上传表格数据************************************/
    
    //导出列表

    public function jreport()
    {
        $uid = I('get.id');
        $timeyear = I('get.timeyear');
        $beforeyea = I('get.beforeyea');
        $stype = I('get.stype');
        $smoney = I('get.smoney');
        $ini['uid'] = $uid;

        $ini['time'] = array(array('elt', $beforeyea), array('egt', $timeyear));
        $bill = M('bill')->where($ini)->order('id desc,time desc')->select();

        $table="<table border='1'>";
        $table.="<tr><td colspan='4'>&nbsp;</td><td colspan='6' style='text-align: center'>进账</td><td colspan='3'  style='text-align: center'>出账</td></tr>";
        $table.="<tr><td style='text-align: center;'>日期</td><td style='text-align: center;'>ID</td><td style='text-align: center;'>客户名称</td><td style='text-align: center;'>课程信息</td><td style='text-align: center;'>类别</td><td style='text-align: center;'>数量</td><td style='text-align: center;'>总金额</td><td style='text-align: center;'>进账金额</td><td style='text-align: center;'>是否定金</td><td>分期数</td><td>原因</td><td>备注</td><td>出账</td></tr>";
        $jin=0;
        $chu=0;
        foreach ($bill as $item) {
            $fname = M('information')->where(array('id'=>$item['fid']))->getField('name');
            $classname = M('course')->where(array("id"=>$item["cid"]))->getField("classname");
            $ldata = M('course_com_order')->field('id,time,ticket,planning,card,friends,disciple')->where(array("id"=>$item["orderid"]))->find();
            $lname = $classname.' <div>'.$ldata['id'].'# '.$ldata['time'].' 小票('.$ldata['ticket'].')'.' 九大规划门票('.$ldata['planning'].')'.' 卡('.$ldata['card'].')'.' 朋友圈('.$ldata['friends'].')'.'  弟子学员('.$ldata['friends'].')</div>';
            if($item['outs']==0){
                $jin+=$item['houston'];
                $table.="<tr><td style='text-align: center;'>{$item["time"]}</td><td style='text-align: center;'>{$item['id']}</td><td style='text-align: center;'>{$fname}</td><td style='text-align: center;'>{$lname}</div></td><td style='text-align: center;'>".$this->getType($item['type'])."</td><td style='text-align: center;'>{$item['num']}</td><td style='text-align: center;'>{$item['amount']}</td><td style='text-align: center;'>{$item['houston']}</td><td style='text-align: center;'>".(($item['deposit']==1)?"是":"否")."</td><td style='text-align: center;vnd.ms-excel.numberformat:@'>".(($item['period']=="")?"-/-":$item['period'])."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
            }else{
                $chu+=$item['outs'];
                $table.="<tr><td style='text-align: center;'>{$item["time"]}</td><td style='text-align: center;'>{$item['id']}</td><td>&nbsp;</td><td>&nbsp;</div></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>{$item['reason']}</td><td>{$item['remarks']}</td><td>{$item['outs']}</td></tr>";
            }
        }
        $table.="<tr><td colspan='4'>&nbsp;</td><td colspan='6' style='text-align: right'>进账总金额：<div>{$jin}元</div></td><td colspan='3'  style='text-align: right'>出账总金额：<div></div>{$chu}元</td></tr>";
        $totale=$jin-$chu;
        $table.="<tr><td colspan='13'  style='text-align: right'>总金额：<div></div>{$totale}元</td></tr>";
        $table.="</table>";
      
        $html1 = "<meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>";


//        header('Content-type: text/html; charset=utf-8');
//        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        $filename="财务账单";
        $filename=iconv("utf-8", "gb2312", $filename);
//        header("Content-Disposition:filename=$filename.xls");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=$filename.xls" );
        echo $table;

    }

    private function getType($num){
        switch ($num){
            case 1:
                return '小票';
                break;
            case 5:
                return '九大规划门票';
                break;
            case 2:
                return '卡会员';
                break;
            case 3:
                return '朋友圈';
                break;
            case 4:
                return '弟子学员';
                break;
        }
    }

	public function jreport1()
	{
		 $uid = I('get.id'); 
		$timeyear = I('get.timeyear');
		$beforeyea = I('get.beforeyea');
		$stype = I('get.stype');
		$smoney = I('get.smoney');
		$ini['uid'] = $uid;
	
		$ini['time'] = array(array('elt',$beforeyea),array('egt',$timeyear));
		$bill = M('bill')->where($ini)->order('time desc')->select();
		foreach ($bill as $key=>$val)
		{
			if($val['houston']  == 0)
			{
				$bill[$key]['type'] = "";
				$bill[$key]['num'] = "";
				$bill[$key]['amount'] = "";
				$bill[$key]['houston'] = "";
				$bill[$key]['deposit'] = 2;
				$bill[$key]['period'] = "";
			}else
			{
				$bill[$key]['reason'] = "";
				$bill[$key]['remarks'] = "";
				$bill[$key]['outs'] = "";
			}
			
		}
	
		$dataar = date('Y-m-d H:i:s'); //时间
		$fileName=$dataar.'财务账单';
		$fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称
		
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();
		$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
		$objPHPExcel->getActiveSheet(0)->setCellValue('A1', '日期');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('B1', '客户名称');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('C1', '课程信息');//销售额赋内容
		
		$objPHPExcel->getActiveSheet(0)->setCellValue('D1', '类别');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('E1', '数量');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('F1', '总金额');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('G1', '进账金额');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('H1', '定金');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('I1', '还款期数');//销售额赋内容
		
		
		$objPHPExcel->getActiveSheet(0)->setCellValue('J1', '原因');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('K1', '出账金额');//销售额赋内容
		$objPHPExcel->getActiveSheet(0)->setCellValue('L1', '备注');//销售额赋内容

		foreach ($bill as $k=>$v)
		{	
			$fname = M('information')->where(array('id'=>$v['fid']))->getField('name');
            $classname = M('course')->where(array("id"=>$v["cid"]))->getField("classname");
            $ldata = M('course_com_order')->field('id,time,ticket,planning,card,friends,disciple')->where(array("id"=>$v["orderid"]))->find();
            if(!empty($classname)){
                if(!empty($ldata['id'])){
                    $lname = $classname.' '.$ldata['id'].'# '.$ldata['time'].' 小票('.$ldata['ticket'].')'.' 九大规划门票('.$ldata['planning'].')'.' 卡('.$ldata['card'].')'.' 朋友圈('.$ldata['friends'].')'.'  弟子学员('.$ldata['friends'].')';
                }else{
                    $lname = '';
                }
            }else{
                $lname = '';
            }
			$objPHPExcel->getActiveSheet(0)->setCellValue("A".($k+2), $v['time']);//销售额赋内容
			$objPHPExcel->getActiveSheet(0)->setCellValue("B".($k+2), $fname);//销售额赋内容
			$objPHPExcel->getActiveSheet(0)->setCellValue("C".($k+2), $lname);//销售额赋内容
			if($v['type'] == 1)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("D".($k+2), '小票');//销售额赋内容
			}else if($v['type'] == 2)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("D".($k+2), '卡会员');//销售额赋内容
			}else if($v['type'] == 3)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("D".($k+2), '朋友圈');//销售额赋内容
			}else if($v['type'] == 4)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("D".($k+2), '弟子学员');//销售额赋内容
			}else if($v['type'] == 5)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("D".($k+2), '九大规划门票');//销售额赋内容
			}
			
			
			$objPHPExcel->getActiveSheet(0)->setCellValue("E".($k+2), $v['num']);//销售额赋内容
			$objPHPExcel->getActiveSheet(0)->setCellValue("F".($k+2), $v['amount']);//销售额赋内容
			$objPHPExcel->getActiveSheet(0)->setCellValue("G".($k+2), $v['houston']);//销售额赋内容
			if($v['deposit'] == 1)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("H".($k+2), '是');//销售额赋内容
			}else if($v['deposit'] == 0)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("H".($k+2), '否');//销售额赋内容
			}else
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue("H".($k+2), ' ');//销售额赋内容
			}
			
			
			$objPHPExcel->getActiveSheet(0)->setCellValue("I".($k+2), $v['period']);//销售额赋内容
			$objPHPExcel->getActiveSheet(0)->setCellValue("J".($k+2), $v['reason']);//销售额赋内容
			$objPHPExcel->getActiveSheet(0)->setCellValue("K".($k+2), $v['outs']);//销售额赋内容
			$objPHPExcel->getActiveSheet(0)->setCellValue("L".($k+2), $v['remarks']);//销售额赋内容
			
		}
		
		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name=$fileName.xls');
		header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
		
	}
public function deldingdan()
{
    $id = I('get.id');
    $stutu = I('get.stutu');
    if(!empty($id)){
        $data = M('bill')->where(array('id'=>$id))->find();
        if(!empty($data)){
            M()->startTrans();
            if($data['pid'] == 0 && $stutu == 1){
                $datas = M('bill')->where(array('pid'=>$id))->select();
                if(!empty($datas)){
                    $bill = M('bill')->where(array('pid'=>$id))->delete();
                    if($bill){
                        $billl = M('bill')->where(array('id'=>$id))->delete(); 
                        if($billl){
                            $commit = M()->commit();
                        }else{
                            M()->rollback();  
                        }
                    }else{
                        M()->rollback();
                    }
                }else{
                    $billl = M('bill')->where(array('id'=>$id))->delete(); 
                    if($billl){
                        $commit = M()->commit();
                    }else{
                        M()->rollback();  
                    }
                }
            }else{
                $bill = M('bill')->where(array('id'=>$id))->delete(); 
                if(empty($bill)){
                    M()->rollback();
                }else{
                    $commit = M()->commit();
                }
            }
            if($commit){
                $res['errcode'] = 1;
                $res['errmsg'] = "删除成功！";
            }else{
                $res['errcode'] = 0;
                $res['errmsg'] = "删除失败！"; 
            }
        }else{
            $res['errcode'] = 0;
            $res['errmsg'] = "订单ID错误！";    
        }
    }else{
        $res['errcode'] = 0;
        $res['errmsg'] = "缺少信息参数！";   
    }
    echo json_encode($res);  
}

// 全选删除
public function dels()
{
    $ids = I("get.val");         //复选框非初始订单id
    $did = I("get.did");         //复选框初始订单id
    if(!empty($ids) || !empty($did)){
        
        if(!empty($ids) && !empty($did)){
            M()->startTrans();
            $map['id'] = array('in',$ids);
            $data = M("bill")->where($map)->select();  
            if(!empty($data)){
                $status = M("bill")->where($map)->delete();
                if($status){
                    unset($map);
                    $map['id'] = array('in',$did);
                    $data = M("bill")->where($map)->select();
                    foreach ($data as $key => $value) {
                        if($value['deposit'] == 0){
                            $res['errcode'] = 0;
                            $res['errmsg'] = "初始订单信息错误！"; 
                            echo json_encode($res);
                            die();
                        }
                    }
                    M()->startTrans();
                    $ini['pid'] = array('in',$did);
                    $statuss = M("bill")->where($ini)->select();
                    if($statuss){
                        $where['pid'] = array('in',$did);
                        $statuse = M("bill")->where($where)->delete();
                        if($statuse){
                            unset($where);
                            $where['id'] = array('in',$did);
                            $status = M("bill")->where($where)->delete();
                            if(!$status){
                                M()->rollback();
                            }
                        }else{
                            M()->rollback();
                        }
                    }else{
                        $where['id'] = array('in',$did);
                        $status = M("bill")->where($where)->delete();
                        if(!$status){
                            M()->rollback();
                        }
                    }
                    if($status){
                        $commit = M()->commit();
                        if($commit){
                            $res['errcode'] = 1;
                            $res['errmsg'] = "删除成功！";
                        }else{
                            $res['errcode'] = 0;
                            $res['errmsg'] = "删除失败！"; 
                        }
                    }else{
                        M()->rollback(); 
                    }
                }else{
                    M()->rollback();  
                }
            }else{
                M()->rollback();  
            }
        }else if(!empty($ids)){
            M()->startTrans();
            $map['id'] = array('in',$ids);
            $data = M("bill")->where($map)->select();
            if(!empty($data)){
                $status = M("bill")->where($map)->delete();
                if($status){
                    $commit = M()->commit();
                    if($commit){
                        $res['errcode'] = 1;
                        $res['errmsg'] = "删除成功！";
                    }else{
                        $res['errcode'] = 0;
                        $res['errmsg'] = "删除失败！"; 
                    }
                }else{
                    M()->rollback();  
                }
            }else{
                M()->rollback();  
            }
        }else if(!empty($did)){
            $map['id'] = array('in',$did);
            $data = M("bill")->where($map)->select();
            foreach ($data as $key => $value) {
                if($value['deposit'] == 0){
                    $res['errcode'] = 0;
                    $res['errmsg'] = "初始订单信息错误！"; 
                    echo json_encode($res);
                    die();
                }
            }
            M()->startTrans();
            $ini['pid'] = array('in',$did);
            $statuss = M("bill")->where($ini)->select();
            if($statuss){
                $where['pid'] = array('in',$did);
                $statuse = M("bill")->where($where)->delete();
                if($statuse){
                    unset($where);
                    $where['id'] = array('in',$did);
                    $status = M("bill")->where($where)->delete();
                    if(!$status){
                        M()->rollback();
                    }
                }else{
                    M()->rollback();
                }
            }else{
                $where['id'] = array('in',$did);
                $status = M("bill")->where($where)->delete();
                if(!$status){
                    M()->rollback();
                }
            }
            if($status){
                $commit = M()->commit();
                if($commit){
                    $res['errcode'] = 1;
                    $res['errmsg'] = "删除成功！";
                }else{
                    $res['errcode'] = 0;
                    $res['errmsg'] = "删除失败！"; 
                }
            }else{
                M()->rollback(); 
            }
        }
    }else{
        $res['errcode'] = 0;
        $res['errmsg'] = "缺少订单参数！";   
    }
    echo json_encode($res);
}

public function addbill()
{
    $typestut = I('post.typestut');
    $sumnum = I('post.sumnum');
    $numonye = I('post.numonye');
    $val = I('post.val');
    $refund = I('post.refund');
    $time = I('post.time');
    $getId = I('post.getId');
    $houston = I('post.jzje');
    $fid = I('post.fid');
    $cid = I('post.cid');
    $orderid = I('post.orderid');
    $gonetype = I('post.gonetype');

    if(is_numeric($sumnum)){
        if(preg_match('/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/', $numonye)){
            if(!empty($time)){
                if($typestut == 4){
                    if(!empty($refund)){
                        $ini['uid']=$getId;
                        $ini['pid']=0;
                        $ini['time'] = $time;
                        $ini['type'] = $typestut;
                        $ini['num'] = $sumnum;
                        $ini['amount'] = $numonye;
                        $ini['deposit'] = $val;
                        if($val == 0){
                            $ini['period'] = $refund; 
                        }else{
                            $ini['period'] = '0/'.$refund;
                        }
                        $ini['houston'] = $houston;
                        $ini['fid'] = $fid;
                        $ini['cid'] = $cid;
                        $ini['orderid'] = $orderid;
                        $ini['gonetype'] = $gonetype;

                        $bill = M('bill')->add($ini);
                        unset($ini);
                        if(!empty($bill) || $bill !== false){
                            $fname = M('information')->where(array('id'=>$fid))->getField('name');
                            $ini["uid"]=$getId;
                            $ini["stuname"]=$fname;
                            $ini["time"]=date("Y-m-d H:i:s");
                            if($val == 0){
                                $ini["content"]=" 申报弟子学员 交全款 ".$houston."元"; 
                            }else{
                                $ini["content"]=" 申报弟子学员 交定金 ".$houston."元";   
                            }
                            if($refund == 1/1){
                                M('course_com_order')->where(array('id'=>$orderid))->save(array('typed'=>1));//更改成入账                                
                            }
                            $sta = M('message')->add($ini);
                            if(!empty($sta)){
                                $res['errcode'] = 1;
                                $res['errmsg'] = "添加成功！"; 
                            }else{
                                $res['errcode'] = 0;
                                $res['errmsg'] = "添加失败！";
                            }
                        }else{
                            $res['errcode'] = 0;
                            $res['errmsg'] = "添加失败！";   
                        } 
                    }else{
                        $res['errcode'] = 0;
                        $res['errmsg'] = "分期数不能为空！"; 
                    }
                }else{

                    $ini['uid']=$getId;
                    $ini['gonetype'] = $gonetype;
                    $ini['pid']=0;
                    $ini['time'] = $time;
                    $ini['type'] = $typestut;
                    $ini['num'] = $sumnum;
                    $ini['amount'] = $numonye;
                    $ini['deposit'] = $val;
                    $ini['period'] = '-/-';
                    $ini['houston'] = $houston;
                    $ini['fid'] = $fid;
                    $ini['cid'] = $cid;
                    $ini['orderid'] = $orderid;//订单id
               

                    M('course_com_order')->where(array('id'=>$orderid))->setField('gonetype',$gonetype);

                    if($gonetype  =="1")    
                    {
                         if($typestut == 1)
                         {      
                              $information = M('information')->where(array('id'=>$fid))->getField('remaining');
                            
                              $information =  $information + $sumnum;

                              M('information')->where(array('id'=>$fid))->setField('remaining',$information);

                              M('course_com_order')->where(array('id'=>$orderid))->save(array('typet'=>1));//更改成入账 小票
                         }
                         if($typestut == 2)
                         {
                             M('course_com_order')->where(array('id'=>$orderid))->save(array('typec'=>1));//更改成入账 卡
                         }
                         if($typestut == 3)
                         {
                             M('course_com_order')->where(array('id'=>$orderid))->save(array('typef'=>1));//更改成入账 朋友圈
                         }
                         if($typestut == 5)
                         {
                             M('course_com_order')->where(array('id'=>$orderid))->save(array('typep'=>1));//更改成入账 九大弟子规划门票
                         }

                    }
                    
                   
                    $bill = M('bill')->add($ini);
                    unset($ini);
                    if(!empty($bill) || $bill !== false){
                        $res['errcode'] = 1;
                        $res['errmsg'] = "添加成功！";  
                    }else{
                        $res['errcode'] = 0;
                        $res['errmsg'] = "添加失败！";   
                    } 
                }
            }else{
                $res['errcode'] = 0;
                $res['errmsg'] = "请选择日期！";  
            }
        }else{
            $res['errcode'] = 0;
            $res['errmsg'] = "总金额请输入有效数字,小数点后保留两位！"; 
        }
    }else{
        $res['errcode'] = 0;
        $res['errmsg'] = "数量需填整数！"; 
    }
    echo json_encode($res);
}

public function addchz()
{
    $why = I('post.why');
    $money = I('post.money');
    $timesdemo = I('post.timesdemo');
    $qid = I('post.qid');   //企业id
    $bz = I('post.bz');
    if(!empty($money)){
        if(preg_match('/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/', $money)){
            if(!empty($timesdemo)){
                $ini['uid']=$qid;
                $ini['pid']=0;
                $ini['name'] = '';
                $ini['phone'] = '';
                $ini['period'] = '';
                $ini['reason'] = $why;
                $ini['outs'] = $money;
                $ini['time'] = $timesdemo;
                $ini['remarks'] = $bz;
                $bill = M('bill')->add($ini);
                if(!empty($bill) || $bill !== false){
                    $res['errcode'] = 1;
                    $res['errmsg'] = "添加成功！"; 
                }else{
                    $res['errcode'] = 0;
                    $res['errmsg'] = "添加失败！";  
                }
            }else{
                $res['errcode'] = 0;
                $res['errmsg'] = "日期不能为空！"; 
            }
        }else{
            $res['errcode'] = 0;
            $res['errmsg'] = "请输入有效数字！"; 
        }
    }else{
        $res['errcode'] = 0;
        $res['errmsg'] = "金额不能为空！";
    }
    echo json_encode($res);
}

 public function savebill()
    {
      $act = I('post.act');
      $val = I('post.val');
      $id = I('post.id');

      
      $where['id'] = $id;
      if($act == '1')
      {
        $ini['name'] = $val;
     
      }
      if($act == '2')
      {
        $ini['phone'] = $val;
       
      }

       if($act == '4')
      {
        $ini['num'] = $val;
       
      }
       if($act == '5')
      {
        $ini['amount'] = $val;
       
      }
      if($act == '6')
      {
        $ini['houston'] = $val;
       
      }
       if($act == '7')
      {
        $ini['reason'] = $val;
       
      }
       if($act == '8')
      {
        $ini['outs'] = $val;
       
      }


       $saveshop = M('bill')->where($where)->save($ini);

       if($saveshop !== false)
       {
          echo json_encode(1);
       }
       else
       {
         echo json_encode(0);
       }
    }

    public function userdata()
    {
        $p = I('post.page');
        $limit = 6;
        $first = ($p-1) * $limit;

        $uid = getcompanyId();
        $uid=I('post.uid');
        $where = array('uid'=>$uid,'deletemark'=>0);

        $information = M("information")->where($where)->limit($first,$limit)->order('id desc')->select();
        // echo '<pre>';
        // print_r($information);die;
        echo json_encode($information);
    }

    public function page2(){
         $uid = getcompanyId();
         $uid=I('post.uid');
         $where = array('uid'=>$uid,'deletemark'=>0);
         $information = M("information")->where($where)->count();
         $enterprise = ceil($information/6);
         echo json_encode($enterprise);

    }

    public function seeknam()
    {
        $name = I('post.name');
        if($name == '')
        {
            $arr = array('uid'=>getcompanyId());
        }
        $arr = array('uid'=>getcompanyId());
        $arr["name"]=array("like","%$name%");

        $information = M("information")->where($arr)->select();

        echo json_encode($information);
    }
    public function searcname()
    {
       $where["id"]=array("in",I('post.val'));
       $information = M("information")->field('id,name')->where($where)->select();
       echo json_encode($information);
    }

    public function getRelationship(){
        $getRelationship=I('post.datalist');
        $id=getcompanyId();
        foreach ($getRelationship as $k=>$row){
            $getRelationship[$k]["information"]=M('information')->where(array("uid"=>$id,"deletemark"=>0))->select();
            $cids=M('course_com')->where(array("fid"=>$row["fid"]))->getField("cid",true);

            if(!empty($cids)){
                $getRelationship[$k]["course"]=M('course')->where(array("uid"=>$id,"id"=>array("in",implode(",",$cids))))->select();
            }else{
                $getRelationship[$k]["course"]=array();
            }
        }

        echo json_encode($getRelationship);
       // var_dump($getRelationship);
//        $info=M('information')->where(array("uid"=>$id))->select();
//        $course=M('course')->where(array("uid"=>$id))->select();
//        echo json_encode(array("information"=>$info,"course"=>$course));
    }

    public function getcid(){
        $id=I('post.id');
        $uid=getcompanyId();
        $course=M('course_com')->where(array("fid"=>$id))->getField("cid",true);
        $courseSelect=array();
        if(!empty($course)){
            $courseSelect=M('course')->where(array("uid"=>$uid,"id"=>array("in",implode(",",$course))))->select();
        }
        echo json_encode($courseSelect);
    }

    public function getorderid(){
        $cid=I('post.cid');
        $fid=I('post.fid');
        $course_com_id=M('course_com')->where(array("cid"=>$cid,"fid"=>$fid))->getField("id",true);
        $course_com_order= M('course_com_order')->where(array("comid"=>array("in",implode(",",$course_com_id))))->select();
        echo json_encode($course_com_order);
    }
    
    
    public function qiqiu()
    {
    	$id = I('post.ida');
    	$bill = M('bill')->where(array('id'=>$id))->find();
    	if(!empty($bill['fid']))
    	{
    		//进账
    		$ty['act'] = 1;
    		$bill['kuname']  = M('information')->where(array('id'=>$bill['fid']))->getField('name');
            $billcid=M('course_com')->where(array("fid"=>$bill['fid']))->getField("cid",true);
            $bill['classname'] = array();
            if(!empty($billcid)){
                $bill['classname'] = M('course')->where(array('id'=>array("in",implode(",",$billcid))))->field('id,classname')->select();
            }


            $comid=M('course_com')->where(array("fid"=>$bill['fid'],"cid"=>$bill['cid']))->getField("id",true);
            $bill['course_com_order'] =array();
            if(!empty($comid)){
                $bill['course_com_order'] = M('course_com_order')->where(array('comid'=>array("in",implode(",",$comid))))->select();
            }
            $fq = explode('/', $bill['period']);
            $bill['period'] = $fq[1];
            $bill['period2'] = $fq[0];
    		//$bill['redercount'] =$course_com_order['id']."# ".$course_com_order['time']." 小票(".$course_com_order['ticket'].") 九大规划门票(".$course_com_order['planning'].") 卡(".$course_com_order['card'].") 朋友圈(".$course_com_order['friends'].") 弟子(".$course_com_order['disciple'].")";
            $ty['data'] = $bill;
    	}else
    	{	
    		//出账
    		$ty['act'] = 2;
    		$ty['data'] = $bill;
    	}
    	echo json_encode($ty);
    }
    
    public function qiqiusave()
    {
    	$idn = I('post.idn');
    	$to1 = I('post.to1');
    	$to2  = I('post.to2');
    	$to3  = I('post.to3');
    	$to4  = I('post.to4');
    	$ini['reason'] = $to1;
    	$ini['remarks'] = $to2;
    	$ini['outs'] = $to3;
    	$ini['time'] = $to4;
    	
    	$bill = M('bill')->where(array('id'=>$idn))->save($ini);
    	if($bill === false)
    	{
    		$rt['act'] = 2;
    	}else
    	{
    		$rt['act'] = 1;
    	}
    	echo json_encode($rt);
    }
    public function tgp()
    {
        $id = I('post.id');
        $pid = I('post.pid');
        $type= I('post.typestut');
        $num = I('post.sumnum');
        $amount = I('post.numonye');
        $houston = I('post.jzje');
        $deposit = I('post.yesorno');
        $time = I('post.time');
        $period = I('post.refund');
        $fid = I('post.fid');
        $cid = I('post.formcid');
        $orderid = I('post.formorderid');
        if(is_numeric($num)){
            if(preg_match('/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/', $amount)){
                if(!empty($time)){
                    if($type == 4){
                        if(!empty($period)){
                            $bills = M('bill')->where(array('id'=>$id))->find();
                            $fq = explode('/', $bills['period']);
                            if($pid == 0){
                                $map['id'] = $id;
                                $map['period'] = array('eq','1/1');
                                $ldata = M('bill')->where($map)->select(); //查询是否是初始订单
                                unset($map);
                                if(empty($ldata)){ //一次性全款订单信息
                                    $res['errcode'] = 0;
                                    $res['errmsg'] = "不能修改首笔订单信息！";  
                                }else{ //非一次性全款订单信息
                                    $ini['time'] = $time;
                                    $ini['num'] = $num;
                                    $ini['amount'] = $amount;
                                    $ini['deposit'] = $deposit;
                                    if($deposit == 0){
                                        $ini['period'] = '1/1';
                                    }else{
                                        $ini['period'] = $period;
                                    }
                                    $ini['fid'] = $fid;
                                    $ini['cid'] = $cid;
                                    $ini['orderid'] = $orderid;
                                    $ini['houston'] = $houston;
                                    $bill = M('bill')->where(array('id'=>$id))->save($ini);
                                    unset($ini);
                                    if(!empty($bill) || $bill !== false){
                                        $res['errcode'] = 1;
                                        $res['errmsg'] = "修改成功！";  
                                    }else{
                                        $res['errcode'] = 0;
                                        $res['errmsg'] = "修改失败！";   
                                    } 
                                }
                            }else{
                                $where['pid'] = $pid;
                                $where['id'] = array('neq',$id);
                                $sun = M('bill')->field('SUM(houston) as sun')->where($where)->select();
                                $houstona = M('bill')->where(array('id'=>$pid))->getField('houston');
                                $zong = $sun[0]['sun']+$houstona+$houston;
                                $numls = $bills['amount']-$zong;
                                $lq = explode('/',$period);
                                if($lq[0] == $fq[1]){
                                    if($numls == 0){
                                        $ini['time'] = $time;
                                        $ini['houston'] = $houston;
                                        $bill = M('bill')->where(array('id'=>$id))->save($ini);
                                        unset($ini);
                                        if(!empty($bill) || $bill !== false){
                                            $res['errcode'] = 1;
                                            $res['errmsg'] = "修改成功！";  
                                        }else{
                                            $res['errcode'] = 0;
                                            $res['errmsg'] = "修改失败！";   
                                        }  
                                    }else{
                                        $res['errcode'] = 0;
                                        $res['errmsg'] = "还款金额不正确！"; 
                                    }
                                }else{
                                    if($numls > 0){
                                        $ini['time'] = $time;
                                        $ini['houston'] = $houston;
                                        $bill = M('bill')->where(array('id'=>$id))->save($ini);
                                        unset($ini);
                                        if(!empty($bill) || $bill !== false){
                                            $res['errcode'] = 1;
                                            $res['errmsg'] = "修改成功！";  
                                        }else{
                                            $res['errcode'] = 0;
                                            $res['errmsg'] = "修改失败！";   
                                        } 
                                    }else{
                                        $res['errcode'] = 0;
                                        $res['errmsg'] = "还款金额不正确！"; 
                                    }
                                }
                            }
                        }else{
                            $res['errcode'] = 0;
                            $res['errmsg'] = "分期数不能为空！"; 
                        }
                    }else{
                        $ini['time'] = $time;
                        $ini['type'] = $type;
                        $ini['num'] = $num;
                        $ini['amount'] = $amount;
                        $ini['deposit'] = $deposit;
                        $ini['period'] = '-/-';
                        $ini['houston'] = $houston;
                        $ini['fid'] = $fid;
                        $ini['cid'] = $cid;
                        $ini['orderid'] = $orderid;
                        $bill = M('bill')->where(array('id'=>$id))->save($ini);
                        unset($ini);
                        if(!empty($bill) || $bill !== false){
                            $res['errcode'] = 1;
                            $res['errmsg'] = "修改成功！";  
                        }else{
                            $res['errcode'] = 0;
                            $res['errmsg'] = "修改失败！";   
                        }  
                    }
                }else{
                    $res['errcode'] = 0;
                    $res['errmsg'] = "请选择日期！";  
                }
            }else{
                $res['errcode'] = 0;
                $res['errmsg'] = "总金额请输入有效数字,小数点后保留两位！"; 
            }
        }else{
            $res['errcode'] = 0;
            $res['errmsg'] = "数量需填整数！"; 
        }
    	echo json_encode($res);
    }

    public function periods(){
        $id = I('post.id'); //订单id
        $getid = I('post.getid'); //公司id
        $map['pid'] = $id;
        $map['uid'] = $getid;
        $data = M('bill')->field('period')->where($map)->select();
        foreach ($data as $key => $value) {
            $period = explode('/', $value['period']);
            $data[$key]['num'] = $period[0];
        }
        if(!empty($data)){
            $res['errcode'] = 1;
            $res['errmsg'] = $data; 
        }else{
            $res['errcode'] = 0;
            $res['errmsg'] = $data;  
        }
        echo json_encode($res);
    }

    public function prompt(){
        $type=I('post.data');
        $qycon=M('enterprise')->where(array("id"=>getcompanyId()))->find();
        //$qyid=M('admin')->where(array(id=>array("in",implode(",",$enterprise))))->getField("pid");
        //$qycon=M('admin')->where(array("id"=>$qyid))->find();
       
        switch ($type){
            case 1:
                $con="小票金额{$qycon['leafletstc']}元";
                break;
            case 2:
                $con="会员卡金额{$qycon['leafletscc']}元";
                break;
            case 3:
                $con="朋友圈金额{$qycon['leafletsfc']}元";
                break;
            case 4:
                $con="弟子学员金额{$qycon['leafletsdc']}元";
                break;
            case 5:
                $con="九大规划门票金额{$qycon['leafletsnc']}元";
                break;
        }
        echo json_encode($con);

    }

}