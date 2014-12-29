<?php

use \Phalcon\Mvc\Model\Validator\Uniqueness;

class allGroup extends Publics {

    function initialize() {
        
    }

    function beforeSave() {
        
    }

    function afterFetch() {
        
    }

    static function produce() {
        return new self();
    }

    function getSource() {
        return $this->getDI()->get('config')->database->prefix . 'link_event';
    }

    function validation() {
        return $this->validationHasFailed() == true ? false : true;
    }

    public static function ifMyFriends($myId) {
        $myFriend = mysqli_query(Publics::connect(), "select merchantuuid,allyuuid from xone_link_merchant where (merchantuuid='$myId' or allyuuid='$myId') and status='1'");

        while (($row = mysqli_fetch_array($myFriend, MYSQLI_ASSOC))) {
            $myFriends[] = $row;
        }

        foreach ($myFriends as $friend) {
            if ($friend['merchantuuid'] == $myId) {
                $mylist[$friend['allyuuid']] = $myId;
            }
            if ($friend['allyuuid'] == $myId) {
                $mylist[$friend['merchantuuid']] = $myId;
            }
        }
        return $mylist;
    }

    public static function getMyFriends($myId) {
        $myFriend = mysqli_query(Publics::connect(), "select merchantuuid,allyuuid from xone_link_merchant where (merchantuuid='$myId' or allyuuid='$myId') and status='1'");

        while (($row = mysqli_fetch_array($myFriend, MYSQLI_ASSOC))) {
            $myFriends[] = $row;
        }

        foreach ($myFriends as $friend) {
            if ($friend['merchantuuid'] == $myId) {
                $ends = $friend['allyuuid'];
            }
            if ($friend['allyuuid'] == $myId) {
                $ends = $friend['merchantuuid'];
            }
            $friendArray[] = "'" . $ends . "'";
        }
        $friends = implode(',', $friendArray);
        return $friends;
    }

    public static function sameFriends($friends, $myId, $whoid) {
        if (!empty($whoid)) {
            $whoids = " and a.merchantuuid='$whoid'";
            $whoidm = " and a.allyuuid='$whoid'";
        } else {
            $whoids = "";
            $whoidm = "";
        }
        $mers = mysqli_query(Publics::connect(), "select a.merchantuuid,a.allyuuid,b.name,b.label,b.address,b.comment from xone_sys_merchant b left join xone_link_merchant a on a.merchantuuid=b.uuid"
                . " where a.merchantuuid in($friends) and a.merchantuuid!='$myId' $whoidm and a.allystatus='1'");
        while (($merrow = mysqli_fetch_array($mers, MYSQLI_ASSOC))) {
            $mer[] = $merrow;
        }

        $allys = mysqli_query(Publics::connect(), "select a.merchantuuid,a.allyuuid,b.name,b.label,b.address,b.comment from xone_sys_merchant b left join xone_link_merchant a on a.allyuuid=b.uuid"
                . " where a.allyuuid in($friends) and a.merchantuuid!='$myId' $whoids and a.allystatus='1'");
        while (($row = mysqli_fetch_array($allys, MYSQLI_ASSOC))) {
            $ally[] = $row;
        }
        if (!empty($ally)) {
            foreach ($ally as $keyally => $ay) {
                $endAlly[$ay['merchantuuid']][$keyally]['allyname'] = $ay['name'];
                $endAlly[$ay['merchantuuid']][$keyally]['merchantuuid'] = $ay['merchantuuid'];
                $endAlly[$ay['merchantuuid']][$keyally]['allyuuid'] = $ay['allyuuid'];
            }
        }
        if (!empty($mer)) {
            foreach ($mer as $keymer => $me) {
                $endAlly[$me['allyuuid']][$keymer . '999']['allyname'] = $me['name'];
                $endAlly[$me['allyuuid']][$keymer . '999']['merchantuuid'] = $me['merchantuuid'];
                $endAlly[$me['allyuuid']][$keymer . '999']['allyuuid'] = $me['allyuuid'];
            }
        }
        return $endAlly;
    }

    public static function getAllMerchants($myId, $endAlly, $ismyf, $teamtype, $typename) {

        if (!empty($teamtype) && !empty($typename)) {
            if ($teamtype == '1') {//按名称查询
                $search_where = " and name like '%$typename%'";
            } elseif ($teamtype == '2') {//按类别查询
                $search_where = " and label like '%$typename%'";
            }
        } else {
            $search_where = "";
        }

        $merchant = mysqli_query(Publics::connect(), "select uuid, name, label, address, comment, label, wbcount, wxcount from xone_sys_merchant where uuid !='$myId' $search_where order by created desc");
        while (($row = mysqli_fetch_array($merchant, MYSQLI_ASSOC))) {
            $merchants[] = $row;
        }

        if (!empty($merchants)) {
            foreach ($merchants as $keymer => $mer) {

                if (isset($endAlly[$mer['uuid']])) {
                    $endMerchants[$mer['name']]['uuid'] = $mer['uuid'];
                    $endMerchants[$mer['name']]['address'] = $mer['address'];
                    $endMerchants[$mer['name']]['label'] = $mer['label'];
                    $endMerchants[$mer['name']]['friend'] = $endAlly[$mer['uuid']];
                    $endMerchants[$mer['name']]['label'] = $mer['label'];
                    $endMerchants[$mer['name']]['comment'] = $mer['comment'];
                    $endMerchants[$mer['name']]['wbcount'] = $mer['wbcount'];
                    $endMerchants[$mer['name']]['wxcount'] = $mer['wxcount'];
                    if (isset($ismyf[$mer['uuid']])) {
                        $endMerchants[$mer['name']]['ismyf'] = 1;
                    } else {
                        $endMerchants[$mer['name']]['ismyf'] = 0;
                    }
                } else {
                    $endMerchants[$mer['name']] = $mer;
                    if (isset($ismyf[$mer['uuid']])) {
                        $endMerchants[$mer['name']]['ismyf'] = 1;
                    } else {
                        $endMerchants[$mer['name']]['ismyf'] = 0;
                    }
                }
            }
        } else {
            $endMerchants = '对不起，没有您所搜索的内容！';
        }
        return $endMerchants;
    }

    //我的信息
    public static function Myinfor($myid) {
        $merchant = mysqli_query(Publics::connect(), "select uuid, name, label, address, comment, label, wbcount, wxcount from xone_sys_merchant where uuid ='$myid'");
        while (($row = mysqli_fetch_array($merchant, MYSQLI_ASSOC))) {
            $merchants[] = $row;
        }
        return $merchants;
    }
    
    //请求加好友状态入库
    public static function insertRequest($quest_type, $message, $merid, $myid){
        $createdtime = date("Y-m-d H:i:s");//请求时间
        
        $ifhive = mysqli_query(Publics::connect(), "select status  from xone_link_merchant where merchantuuid ='$myid' and allyuuid='$merid'");
        if(!empty($ifhive)){
           $delete = mysqli_query(Publics::connect(),"delete from xone_link_merchant  where merchantuuid ='$myid' and allyuuid='$merid'");
           exec($merchant);
        }
        $merchant = mysqli_query(Publics::connect(), "insert into xone_link_merchant (merchantuuid,allyuuid,allytype,allystatus,status,createdtime) values ('$myid','$merid','$quest_type','1','2','$createdtime')");
        exec($merchant);
    }
    

}
