<?php
use \Phalcon\Mvc\Model\Validator\Uniqueness;
/**
 * @desc	MM => MysqlModal, 封装对mysql数据表的访问基础类
 * @author 	zhonglei
 */
class MM extends Phalcon\Mvc\Model {
	/**
	 * @desc	根据配置中提供的数据库账号信息建立到mysql数据库的链接
	 * @return 	到mysql数据库的连接资源
	 */
	protected function connect () {
		$config = $this->getDI()->get ( 'config' );
		$conn   = mysqli_connect ( $config->database->host, $config->database->username, $config->database->password );

		mysqli_query ( $conn, 'SET NAMES "UTF8"' );
		mysqli_select_db ( $conn, $config->database->name );

		return $conn;
	}

	/**
	 * @desc	从mysql数据数据库中获取一个可以作为数据记录主键的整数值
	 * @return	mixed 	如果能成功获取，返回唯一的整数值，否则返回null
	 */
	public function fastuuid () {
		$sql = 'SELECT UUID_SHORT() AS `fastuuid`';
		$rtn = $this->myquery ( $sql );
		return empty($rtn[0]['fastuuid']) ? null : $rtn[0]['fastuuid'];
	}

	/**
	 * @desc	进行一次mysql的查询
	 * @param	string 		$sql	需要执行的sql语句
	 * @return	mixed 				如果查询返回bool值，返回执行完成的结果，对于特殊查询，返回数组，否则返回false
	 */
	public function myquery ( $sql ) {
		$return = mysqli_query ( $this->connect(), $sql );

		if ( is_bool($return) ) {
			return $return;
		} elseif ( is_resource($return) ) {
			$rtn = array(); while ( ($row=mysqli_fetch_array($return,MYSQLI_ASSOC)) ) $rtn[] = $row;
			return $rtn;
		} elseif ( count($return)>0 ) {
			$rtn = array(); while ( ($row=mysqli_fetch_array($return,MYSQLI_ASSOC)) ) $rtn[] = $row;
			return $rtn;
		} else {
			return false;
		}
	}

	/**
	 * @desc	返回数据表的各个字段的信息数组
	 * @return	hash  	key为字段的名称，value为字段的属性，主要包含 ：
	 * 					name:字段名称,type:字段的类型名,null:字段是否可以为空,index:字段的索引信息,extra:字段附加信息,default:字段默认值
	 */
	public function fields () {
		$sql = 'DESC `' . $this->getSource() . '`';
		$struct = $this->myquery ( $sql );
		$return = array();
		foreach ( $struct as $k => $v ) {
			$attr = array();
			$attr['name']    = $v['Field'];
			$attr['type']    = $v['Type'];
			$attr['null']    = strtolower($v['Null'])=='no' ? false : true;
			$attr['index']   = strtolower($v['Key']);
			$attr['extra']   = $v['Extra'];
			$attr['default'] = $v['Default'];

			$return[$attr['name']] = $attr;
		}

		return $return;
	}

	/**
	 * @desc	向数据表中插入一条数据记录
	 * @param	hash 	$data		插入的数据信息，key为字段的名称，value为字段值
	 * @throws 	Exception			如果保存失败，抛出错误信息字符串异常
	 */
    public static function C ( $data, $class ) {
    	$record = new $class();
    	$fileds = $record->fields();

    	foreach ( $data as $k => $v ) if ( isset($data[$k]) ) $record->{$k} = $v;
		$record->uuid = isset($data['uuid'])&&!empty($data['uuid']) ? $data['uuid'] : $record->fastuuid();

		if ( isset($fileds['create']) )  $record->create  = date('Y-m-d H:i:s');
    	if ( isset($fileds['created']) ) $record->created = date('Y-m-d H:i:s');
        if ( isset($fileds['modified']) ) $record->modified = date('Y-m-d H:i:s');

    	if ( $record->save()==false ) { $error = array();
    		foreach ($record->getMessages() as $message ) $error[] = '<p>'.$message.'</p>';
    		throw new Exception ( join ( ' ', $error ) );
    	}

    	return $record->uuid;
    }

    /**
     * @desc	更新一条数据记录
     * @param	int 	$uuid		需要更新的数据记录值的主键
     * @param	hash 	$data		提供的更新数据：key为需要更新的字段的名称，value为字段值
     * @return 	boolean				更新成功，返回true，否则，返回false
     */
    public static function U( $uuid, $data, $class ) {
    	if ( empty($uuid) ) return false;

    	$record = $class::findFirst($uuid);
    	if ( empty($record->uuid) || $record->uuid<=0 ) return false;

    	$update = array();
    	$fields = $record->fields();

    	foreach ( $data as $k => $v ) if ( isset($fields[$k]) ) $update[$k] = $v;
    	if ( isset($fields['modified']) ) $update['modified'] = date('Y-m-d H:i:s');

    	if ( empty($update) ) return false; else return (bool)$record->save($update);
    }

    /**
     * @desc	删除一条数据记录
     * @param	int 	$uuid		需要删除的记录的主键
     * @return 	boolean				删除是否成功
     */
	public static function D( $uuid, $class ) {
		if ( empty($uuid) ) return false;

		$record = $class::findFirst($uuid);
		if ( empty($record->uuid) || $record->uuid<=0 ) return false;
		return (bool)$record->delete();
	}

	/**
	 * @desc	从数据表中读取一条数据记录
	 * @param	int 	$uuid		需要读取的数据记录的主键
	 * @return	mixed 				如果读取的数据记录存在，以hash形式返回数据记录信息，否则返回null
	 */
	public static function R( $uuid, $class ) {
		if ( empty($uuid) ) return null;

		$return = $class::findFirst ( $uuid );
		return (empty($return->uuid)||$return->uuid<=0) ? null : (array)$return;
	}

	/**
	 * @desc	从数据表中进行查找
	 * @param	hash 	$cond		查找条件，key为查找的字段名称，value为查找条件值
	 * @return 	array 				符合查找条件的数据记录的主键数组
	 */
	public static function S ( $cond ) { return array(); }
	public static function log ( $msg ) {}
}

/*
class MyModel extends MM {
	function initialize(){}function beforeSave(){}function afterFetch(){}public static function produce(){return(new self());}
	function getSource(){return($this->getDI()->get('config')->database->prefix.'content_article');}
    function validation(){return($this->validationHasFailed()==true?false:true);}
*/
    /**
     * @desc 	对输入数据进行过滤
     * @param	hash 	$input		提供的输入数据，key为提供的数据的属性名，value为属性值
     * @param 	boolean $creation	提供的输入是否是为了创建新数据记录
     * @return	hash 				过滤后的数据信息
     *//*
    public static function filter ( $input, $creation=false ) {
    	if ( empty($input) ) return null;
    	$data = array();

    	if ( isset($input['uuid'])   && !empty($input['uuid'])  )  $data['uuid']   = abs(intval($input['uuid']));
    	if ( isset($input['length']) && !empty($input['length']) ) $data['length'] = abs(intval($input['length']));

    	if ( isset($input['title'])     && !empty($input['title']) )     $data['title']     = trim($input['title']);
    	if ( isset($input['author'])    && !empty($input['author']) )    $data['author']    = trim($input['author']);
    	if ( isset($input['source'])    && !empty($input['source']) )    $data['source']    = trim($input['source']);
    	if ( isset($input['comment'])   && !empty($input['comment']) )   $data['comment']   = trim($input['comment']);
    	if ( isset($input['descripte']) && !empty($input['descripte']) ) $data['descripte'] = trim($input['descripte']);

    	if ( $creation ) {
    		if ( empty($data['uuid']) ) {
    			throw new Exception('稿件创建时所关联系统内容记录不能为空');
    		}
    	} else {
    		$content = SysContent::findFirst($data['uuid']);
    		if ( empty($content->uuid) || intval($content->uuid)<0 ) throw new Exception('稿件所关联系统内容记录不存在');
    	}

    	return $data;
    }*/

    /**
     * @desc	向数据表中添加一条数据记录
     * @param	hash 	$data		提供的数据记录信息
     * @return 	int					新增加的数据记录的主键
     */
    // public static function add ( $data ) { return self::C ( self::filter ( $data, true ), __CLASS__ ); }

    /**
     * @desc	从数据表中根据主键读取一条数据记录
     * @param	int 	$uuid	需要读取的数据记录的主键
     * @return	mixed 			如果读取的数据记录存在，以hash形式返回数据记录信息，否则返回null
     */
    // public static function read ( $uuid ) { return self::R ( $uuid, __CLASS__ ); }

    /**
     * @desc	从数据表中删除一条数据记录
     * @param	int 	$uuid		需要删除的数据记录的主键
     * @return 	boolean				删除操作是否成功
     */
    // public static function del ( $uuid ) { return self::D ( $uuid, __CLASS__ ); }

    /**
     * @desc	修改指定主键的数据记录
     * @param	int 	$uuid		需要修改的数据记录的主键值
     * @param	hash 	$data		提供的更新数据，key为数据属性名，value为对应的属性值
     * @return 	boolean 			修改是否成功
     */
    // public static function mod ( $uuid, $data ) { return self::U ( $uuid, self::filter($data), __CLASS__ ); }

    /**
     * @desc	对系统数据进行检索
     * @param	hash 	$cond		检索条件，key为检索属性的名称，value为对应的检索值
     * @return 	array 				符合检索条件的数据记录的主键数组
     */
    // public static function S ( $cond ) {}
//}
