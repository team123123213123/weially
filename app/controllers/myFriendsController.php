<?php

/*
 * 合作伙伴 
 */

class myFriendsController extends BaseController {

    //主页
    public function indexAction() {

        $publicPath = $this->publicPath();
        $publicDir = $this->publicDir();
        $error_mess = ""; //提示
        $getally = "";
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            
            $getally = htmlspecialchars(addslashes($post['my'])); //搜索盟友
            
            if (empty($getally)) {
                $error_mess = '请输入要搜索的内容！';
            }
        }
        
        $myId = $this->session->get("myId");
        $friendList = partner::getMyFriendsList($myId, $getally);
        print_r($friendList);
        $this->view->setVar("path", $publicPath);
        $this->view->setVar("dir", $publicDir);
        $this->view->setVar("data", $friendList);
        $this->view->setVar("getally", $getally);
        $this->view->setVar("error_mess", $error_mess);
    }

    //更改盟友状态（暂停、恢复）
    public function upFriendStatusAction(){
        $myId = $this->session->get("myId");
     echo   $upstatus = htmlspecialchars(addslashes($_GET['upstatus']));//要更改的状态
     echo   $upid = htmlspecialchars(addslashes($_GET['upid']));//要更改的盟友id        
//        $d->execute("update xone_link_merchant set allystatus='$upstatus' where allyuuid='$upid' and merchantuuid='$myId'");
    }
    
}
