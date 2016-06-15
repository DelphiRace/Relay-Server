<?php
// router config
return array(
    "example" => array(
        'controller' => 'example',
        'action' => 'index',
        // 針對全部呼叫方法
        // 'method' => 'GET'
        // 針對單一Action呼叫方法
        // 'ActionMethod' => array(
        //     'index' => 'POST'
        // )
    ),
    "bpsAPI" => array(
        'controller' => 'bpsAPI',
        'action' => 'index',
        // 針對全部呼叫方法
        // 'method' => 'GET'
        // 針對單一Action呼叫方法
        // 'ActionMethod' => array(
        //     'index' => 'POST'
        // )
    ),
    "mobileAPI" => array(
        'controller' => 'mobileAPI',
        'action' => 'menu',
        // 針對全部呼叫方法
        // 'method' => 'GET'
        // 針對單一Action呼叫方法
        // 'ActionMethod' => array(
        //     'index' => 'POST'
        // )
    ),
    "menuAPI" => array(
        'controller' => 'menuAPI',
        'action' => 'menu',
        // 針對全部呼叫方法
        // 'method' => 'GET,POST'
        // 針對單一Action呼叫方法
        // 'ActionMethod' => array(
        //     'index' => 'POST'
        // )
    ),
    "uploaderAPI" => array(
        'controller' => 'uploaderAPI',
        'action' => 'uploader',
        // 針對全部呼叫方法
        'method' => 'POST,OPTIONS'
        // 針對單一Action呼叫方法
        // 'ActionMethod' => array(
        //     'index' => 'POST'
        // )
    ),
    "loginAPI" => array(
        'controller' => 'loginAPI',
        'action' => 'index',
        // 針對全部呼叫方法
        'method' => 'POST,OPTIONS'
        // 針對單一Action呼叫方法
        // 'ActionMethod' => array(
        //     'index' => 'POST'
        // )
    ),
    "adminRegisteredAPI" => array(
        'controller' => 'adminRegisteredAPI',
        'action' => 'index',
        // 針對全部呼叫方法
        'method' => 'POST,OPTIONS'
        // 針對單一Action呼叫方法
        // 'ActionMethod' => array(
        //     'index' => 'POST'
        // )
    ),
);

?>