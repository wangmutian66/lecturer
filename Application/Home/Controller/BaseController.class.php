<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function _initialize(){
        
    	if(is_login()){	//判断是否登录
    	    
            $controller = strtolower(CONTROLLER_NAME);
            $type = session('type'); //Relationship
            
            if($controller == 'index'||$controller='Course' ||$controller='Leunfold'||$controller == 'relationship' ||$controller == 'affairs' || $controller == 'personal' || $controller == 'student'||$controller=='admin'||$controller =='enterprise'||$controller =='setsail'||$controller =='bill'){
                $expiretime = session('expiretime',time() + 3600);
                if(!empty($expiretime)) {
                    if($expiretime < time()) {
                        unset($expiretime);
                        $this->redirect('Home/Login/login');
                        exit(0);
                    } else {
                        session('expiretime',time() + 3600); // 刷新时间戳
                    }
                }   
                
            }else{
                
                if($type == $controller){
                    $expiretime = session('expiretime',time() + 3600);
                    if(!empty($expiretime)) {
                        if($expiretime < time()) {
                            unset($expiretime);
                            $this->redirect('Home/Login/login');
                            exit(0);
                        } else {
                            session('expiretime',time() + 3600); // 刷新时间戳
                        }
                    }   
                }else{
                    $this->redirect('Home/Login/login');
                    exit(0);
                }
            }

//            $getmessage=getUserMessge();
//            var_dump($getmessage);
//            exit();
            $isread=isreadmessage();
            if($isread!=0){
                session("message","1");
            }else{
                session("message","2");
            }

    	}else{
    		$this->redirect('Home/Login/login');
    		die();
    	}
    }
}