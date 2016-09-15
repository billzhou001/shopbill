<?php
namespace Admin\Controller;
class IndexController extends BaseController {
    public function index(){
        
        $this->display();
    }
    public function top(){
        $this->display();
    }
    public function menu(){
        $priModel = D('Privilege');
        $btn = $priModel->getBtn();

        $this->assign('btn',$btn);
        $this->display();
    }
    public function main(){
        $this->display();
    }
    public function test(){
        $this->display();
    }
}