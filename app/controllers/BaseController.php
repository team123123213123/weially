<?php

class BaseController extends Phalcon\Mvc\Controller {

    public function publicPath() {
        $publicPath = "/weiallys/public/";
        return $publicPath;
    }
    
    public function publicDir() {
        $publicDir = "/weiallys/";
        return $publicDir;
    }

    protected function redirect($url) {
        return $this->response->redirect($url);
    }

}
