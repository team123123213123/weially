<?php

/*
 * 联盟首页所有组织 
 */

class IndexController extends BaseController {

    //主页
    public function indexAction() {

        $publicPath = $this->publicPath();
        $publicDir = $this->publicDir();

        $error_mess = ""; //提示
        $typename = ""; //搜索键值
        $teamtype = ""; //搜索类型
        //post请求搜索查询
        if ($this->request->isPost()) {
            $post = $this->request->getPost();

            $typename = htmlspecialchars(addslashes($post['typename'])); //搜索键值
            $teamtype = htmlspecialchars(addslashes($post['teamtype'])); //搜索类型  

            if (empty($typename)) {
                $error_mess = '请输入要搜索的内容！';
            }
        }

        //写入session
        $lifeTime = 2 * 3600;
        $this->session->set("myId", "23681873674764296");
        $myId = $this->session->get("myId");
        setcookie(session_name(), session_id(), time() + $lifeTime, "/");

        //我的盟友        
        $friends = allGroup::getMyFriends($myId);
        $iffriends = allGroup::ifMyFriends($myId);

        //通过我的盟友查询出其他商户是否和我拥有相同的盟友     
        $endAlly = allGroup::sameFriends($friends, $myId, $whoid = "");

        //所有的商户列表
        $endMerchants = allGroup::getAllMerchants($myId, $endAlly, $iffriends, $teamtype, $typename);
        //print_r($endMerchants);
        $this->view->setVar("data", $endMerchants);
        $this->view->setVar("error_mess", $error_mess);
        $this->view->setVar("typename", $typename);
        $this->view->setVar("teamtype", $teamtype);
        $this->view->setVar("path", $publicPath);
        $this->view->setVar("dir", $publicDir);
    }

    //获取更多盟友
    public function getMoreAllyAction() {

        $muuid = ""; //更多用户id key   
        //按钮点击传主键id进行查询
        if ($this->request->isGet()) {

            if ($this->session->has("myId")) {
                $myId = $this->session->get("myId");
            }

            $muuid = htmlspecialchars(addslashes($_GET['uuid'])); //更多用户id key

            $friends = allGroup::getMyFriends($myId);
            $endAlly = allGroup::sameFriends($friends, $myId, $whoid = $muuid);

            foreach ($endAlly as $ally) {
                foreach ($ally as $al) {
                    $data[] = $al['allyname'];
                }
            }
            echo json_encode($data);
        } else {
            echo "error";
            exit;
        }
    }

    //获取我的信息
    public function getMyInforAction() {
        $merid = htmlspecialchars(addslashes($_GET['merid']));
        if ($this->session->has("myId")) {
            $myId = $this->session->get("myId");
        }

        $infor = allGroup::Myinfor($myId);
        $refriend = allGroup::Myinfor($merid);
        
        $myinfor['id'] = $merid;
        $myinfor['infor'] = $infor;
        $myinfor['fid'] = $refriend[0]['name'];
        
        echo json_encode($myinfor);
    }

    //添加盟友
    public function addFriendAction() {
        $quest_type = "";
        $message = "";
        $merid = "";
        
        if ($this->session->has("myId")) {
            $myid = $this->session->get("myId");
        }
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            
            $quest_type = htmlspecialchars(addslashes($post['quest_type'])); //申请好友的类型
            $message = htmlspecialchars(addslashes($post['message'])); //申请语  
            $merid = htmlspecialchars(addslashes($post['merid'])); //被申请人人  
          
            allGroup::insertRequest($quest_type, $message, $merid, $myid);
            
            $infor = allGroup::Myinfor($merid);
        }
        echo json_encode($infor[0]['name']);
    }

}
