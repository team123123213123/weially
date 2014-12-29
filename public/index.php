<?php
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');
define('MOCK_INWX', false);
define('SHOW_ERR', false);
if(SHOW_ERR){
	ini_set("display_errors", "On");
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

try {

	$config = new \Phalcon\Config\Adapter\Ini(__DIR__.'/../app/config/config.ini');
	$paymentConfig = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/../app/config/payment.ini');
    //Register an autoloader
	
	$loader = new \Phalcon\Loader();

	$loader->registerDirs(
    array(
        __DIR__.$config->application->controllersDir,
        __DIR__.$config->application->pluginsDir,
        __DIR__.$config->application->libraryDir,
        __DIR__.$config->application->modelsDir,
    )
	)->register();
	
	$di = new \Phalcon\DI\FactoryDefault();
	$di->set('dispatcher', function() use ($di) {
		$eventsManager = $di->getShared('eventsManager');
		$dispatcher = new Phalcon\Mvc\Dispatcher();
		$dispatcher->setEventsManager($eventsManager);
		return $dispatcher;
	});

	$di->set('profiler', function(){return new \Phalcon\Db\Profiler();}, true);

	$di->set('url', function() use ($config){
		$url = new \Phalcon\Mvc\Url();
		$url->setBaseUri($config->application->baseUri);
		return $url;
	});

	$di->set('redis', function() use ($config){
		$redis = new redis();
		$redis->connect($config->redis->host, $config->redis->port);
		return $redis;
	});

	$di->set('wechat',function()use($config){return new Wechat();});
	$di->set('config',function()use($config){return $config;});
	$di->set('paymentConfig',function()use($paymentConfig){return $paymentConfig;});
	$di->set('utilstr',function(){return new UtilStr();});
	$di->set('component',function()use($config){return new Widget(__DIR__.$config->application->componentDir);});
    
	
	$di->set('session', function(){
		$session = new Phalcon\Session\Adapter\Files();
		$session->start();
		return $session;
	});
	
	$di->set ( 'db', function() use ($config, $di ) {
		$eventsManager = new \Phalcon\Events\Manager();
		$profiler = $di->getProfiler();
		$eventsManager->attach ( 'db', function($event, $connection) use ($profiler) {
			if ($event->getType() == 'beforeQuery') { $profiler->startProfile($connection->getSQLStatement()); }
			if ($event->getType() == 'afterQuery') { $profiler->stopProfile(); }
		});

		$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
			"host"     => $config->database->host,
			"dbname"   => $config->database->name,
			"charset"  => $config->database->charset,
			"username" => $config->database->username,
			"password" => $config->database->password
		));

		$connection->setEventsManager($eventsManager);
		return $connection;
	});

	
    //Setting up the view component
    $di->set ( 'view', function() use ($config) {
		$view = new \Phalcon\Mvc\View();
		$view->setViewsDir(__DIR__ . $config->application->viewsDir);
		return $view;
	});

    //Handle the request
    $application = new \Phalcon\Mvc\Application();
    $application->setDI($di);
    echo $application->handle()->getContent();

} catch(\Phalcon\Exception $e) {
     echo "PhalconException: ", $e->getMessage();
}