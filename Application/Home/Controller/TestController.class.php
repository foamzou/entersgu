<?php
namespace Home\Controller;
use Think\Controller;
class TestController extends Controller {
    public function index(){
        $_user = new \Home\Model\UserModel();
        $_lib = new \Home\Model\LibraryModel();
        p($_lib->search(0,'d',0,1));
    }
}