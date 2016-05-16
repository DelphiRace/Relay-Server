<?php
    $routerConfig = require( __DIR__."/router/config.php" );
    $REQUEST_URI = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    $REQUEST_URI = substr($REQUEST_URI, 1, strlen($REQUEST_URI));
    $RequestArray = explode("/", $REQUEST_URI);
    //有偵測到相關關鍵字
    if(array_key_exists( $RequestArray[0], $routerConfig )){
        //取得設定值
        $methodConfig = $routerConfig[$RequestArray[0]];

        // 設定允許的方法
        // allowMethods 允許請求的方法，未填寫即都可接受 => GET, PUT, POST, DELETE, OPTIONS
        // $methodClassName = $methodConfig["controller"]."Controller";
        // 這是針對全部請求的方法
        if(!empty($methodConfig["method"])){
            routerMethod($methodConfig["method"]);
        }else{
            routerMethod("GET, PUT, POST, DELETE, OPTIONS");
        }

        include(__DIR__."/../router/Controller/".$methodConfig["controller"].".php");
        //取得對應的CLASS
        $methodClassName = $methodConfig["controller"]."Controller";
        //初始化CLASS
        $methodClass = new $methodClassName();
        if(!empty($RequestArray[1])){
            // 針對單一Action請求方法設定
            if(!empty($methodConfig["ActionMethod"][$RequestArray[1]])){
                routerMethod($methodConfig["ActionMethod"][$RequestArray[1]]);
            }
            try{
                if(method_exists($methodClass, $RequestArray[1]."Action")){
                    $methodClass -> {$RequestArray[1]."Action"}();
                }else{
                    echo $RequestArray[1] . " Action is not exists";
                    exit();
                }
            }catch(Exception $error){
                echo $RequestArray[1] . " Action is not exists";
                exit();
            }
        }else{
            // 針對單一Action請求方法設定
            if(!empty($methodConfig["ActionMethod"][$methodConfig["action"]])){
                routerMethod($methodConfig["ActionMethod"][$methodConfig["action"]]);
            }
            try{
                //呼叫預設的ACTION
                if(method_exists($methodClass, $methodConfig["action"]."Action")){
                    $methodClass -> {$methodConfig["action"]."Action"}();
                }else{
                    $methodClass -> indexAction();
                }
            }catch(Exception $error){
                echo "Default or Index Action is not setting!";
                exit();
            }
        }

        // 顯示結果
        $viewContnet = $methodClass->viewContnet;
        foreach ($viewContnet as $key => $content) {
            echo $content;
        }
        exit();
    }else{
        if( $_SERVER['REQUEST_URI'] == "/"){
            // header("Location: index.html");
        }else{
            header($RequestArray[0]." 404 Not Found", true, 404);
            echo $RequestArray[0]." 404 Not Found";
            exit();
        }
    }

    function routerMethod($methodString){
        // 切陣列
        $allowMethods = explode(",", str_replace(" ","",strtoupper($methodString)));
        $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
        // print_r( trim(strtoupper($methodString)));
        if(!in_array($REQUEST_METHOD, $allowMethods) && !empty($allowMethods)){
            echo "HTTP Request Method is not Alow!";
            exit();
        }else{
            header("Access-Control-Allow-Methods: ".strtoupper($methodString));
        }
    }
?>