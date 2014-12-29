<?php

use \Phalcon\Mvc\Model\Validator\Uniqueness;

class partner extends Publics
{

    function initialize()
    {
        
    }

    function beforeSave()
    {
        
    }

    function afterFetch()
    {
        
    }

    static function produce()
    {
        return new self();
    }

    function getSource()
    {
        return $this->getDI()->get('config')->database->prefix . 'link_event';
    }

    function validation()
    {
        return $this->validationHasFailed() == true ? false : true;
    }

    //我的伙伴列表
    public static function getMyFriendsList($myId, $getally)
    {
//        $friends = allGroup::getMyFriends($myId);
        if ($getally) {
            $wheremy = " and a.name like '%$getally%'";
        } else {
            $wheremy = "";
        }

        $list = mysqli_query(Publics::connect(), "select * from xone_sys_merchant a left join xone_link_merchant b on a.uuid=b.allyuuid  or a.uuid=b.merchantuuid"
                . " where  (b.merchantuuid = '$myId' or b.allyuuid='$myId') and a.uuid!='$myId' and b.status='1' $wheremy order by createdtime desc");
        while (($row = mysqli_fetch_array($list, MYSQLI_ASSOC))) {
            $myFriends[] = $row;
        }
        if (!empty($myFriends)) {
            $myFriends = $myFriends;
        } else {
            $myFriends = '对不起，没有您所搜索的内容！';
        }
        return $myFriends;
    }

}
