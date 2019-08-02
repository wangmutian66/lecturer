<?php
/**
 * [用于显示金额]
 * @param  [type] $num 数量 [int] $proportion 比例 [int] $onemoney 单个金额 [int]
 * @return [type]       [string]
 */
function money($num,$proportion,$onemoney){
	$single = ($onemoney/100)*$proportion;
	$single = $single*$num;
	$moneys = round($single,2);
	return  $moneys;
}
/**
 * [用于下载讲师数据表格]
 * @param  [type] $num 数量 [int] $proportion 比例 [int] $onemoney 单个金额 [int]
 * @return [type]       [string]
 */
function jexcel($map){
		$data = M('user')
		->field('id,uid,name,mobile')
        ->where($map)
        ->order('id desc')
        ->select();	//查询所有企业下的讲师
        if($data){
			foreach ($data as $key => $value) {
				$ids[] = $value['id'];
			}
		
			$info = M('enterprise')
			->where(array('id'=>$map['uid']))
			->find();	//查询企业信息

			$pdj = round(($info['leafletstc']/100)*$info['tc'],2);
			$kdj = round(($info['leafletscc']/100)*$info['cc'],2);
			$qdj = round(($info['leafletsfc']/100)*$info['fc'],2);
			$ddj = round(($info['leafletsdc']/100)*$info['dc'],2);

			$db = C('DB_PREFIX');
			if(!empty($ids)){
				$ini['qid'] = $map['uid'];
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
				$ins['qid'] = $map['uid'];
				$order = M('order')
				->where($ins)
				->select();

				foreach ($data as $key => $value) {
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
						}
					}
				}

				foreach($tt as $key => $val){	//每个人已发金额
				    $sums[$key] = $val + $ct1[$key] + $ft1[$key] + $dt1[$key] + $pt[$key];
				}

				foreach($tt as $key => $val){	//每个人未发金额
				    $sums2[$key] = $val + $ct2[$key] + $ft2[$key] + $dt2[$key] + $pt2[$key];
				}	

				foreach($tt as $key => $val){	//每个人应发发金额
				    $sums1[$key] = $val + $sums2[$key] + $sums[$key];
				}

				foreach ($data as $key => $value) {
					foreach ($sums as $key1 => $value1) {	//每个人已发金额
						if($key1 == $value['id']){
							$data[$key]['yf'] = $value1;
						}
					}
					foreach ($sums1 as $key2 => $value2) {	//每个人应发金额
						if($key2 == $value['id']){
							$data[$key]['zje'] = $value2;
						}
					}
					foreach ($sums2 as $key3 => $value3) {	//每个人未发金额
						if($key3 == $value['id']){
							$data[$key]['wf'] = $value3;
						}
					}				
				}		
			}

			if(!empty($nums)){
				foreach ($data as $key => $value) {
					foreach ($nums as $key1 => $value1) {
						if($value['id'] == $key1){
							$data[$key]['nums'] = count($value1);
						}
					}
				}
			}
			header('Content-type: text/html; charset=utf-8');
	        header("Content-type:application/vnd.ms-excel;charset=utf-8");
	        $filename="讲师数据";
	        $filename=iconv("utf-8", "gb2312", $filename);
	        header("Content-Disposition:filename=$filename.xls");
	        $str="<table border='1'>";
	        $str .= '<tr><td>序号</td><td>姓名</td><td>联系电话</td><td>学员人数</td><td>应发</td><td>已发</td><td>未发</td></tr>';
	        foreach ($data as $key => $value) {
	            $str.="<tr>";
	            $str .= '<td>'.$key.'</td><td>'.$value['name'].'</td><td>'.$value['mobile'].'</td>';
	            if(!empty($value['nums'])){
	                $str .='<td>'.$value['nums'].'名</td>';
	            }else{
	                $str .='<td>0名</td>';
	            }
	            $str .= '<td>'.$value['zje'].'</td><td>'.$value['yf'].'</td><td>'.$value['wf'].'</td>';
	            $str.="</tr>";
	        }
	        $str.="</table>";
		return $str;
	}else{
		return false;
	}
}
/**
 * [用于判断是否登录]
 * @param  
 * @return [type] [bool]
 */
function is_login() {
	$id = session('id');
	if (empty($id)) {
		return 0;
	} else {
		return 1;
	}
}