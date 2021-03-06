<?php
use SystemCtrl\ctrlSystem;

class bpsAPIController
{
    // 嘗試次數
    private $callAPITryTime = 0;
    // 總嘗試次數
    private $callAPITotalTime = 0;
    private $APIFinalResponse;
    public function indexAction()
    {
        global $_DELETE, $_PUT;
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // $SysClass->initialization(null,true);
        // 使用方法：
        // 送過來的物件為:
        // {
        //     api: "呼叫的ＡＰＩ方法",
        //     data: {} => 呼叫ＡＰＩ所需要的參數值
        // }
        $SysClass->initialization();
        try{
            // 取得設定方法
            $APIUrl = $SysClass->GetAPIUrl('apiURL');
            
            $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
            $contentType = "application/x-www-form-urlencoded; charset=UTF-8";
            $SendArray = [];

            // GET
            if($REQUEST_METHOD == "GET"){
                if(isset($_GET["threeModal"])){
                    $APIUrl = ($_GET["threeModal"])?$SysClass->GetAPIUrl('threeAPIURL'):$APIUrl;
                }

                $APIMethod = $_GET["api"];
                $APIUrl .= $_GET["api"];
                if(isset($_GET["data"])){
                    $SendArray = $_GET["data"];
                }

                $response = $SysClass->UrlDataGet( $APIUrl, $SendArray);
            }
            // POST
            else if($REQUEST_METHOD == "POST"){
                
                if(isset($_POST["threeModal"])){
                    $APIUrl = ($_POST["threeModal"])?$SysClass->GetAPIUrl('threeAPIURL'):$APIUrl;
                }

                $APIMethod = $_POST["api"];

                if(isset($_POST["data"])){
                    $SendArray = $_POST["data"];
                }

                $APIUrl .= $_POST["api"];
                if(!empty($_POST["contentType"])){
                    $contentType = $_POST["contentType"];
                }
                $response = $SysClass->UrlDataPost( $APIUrl, $SendArray, $contentType, $_POST["threeModal"],$_POST["changeJson"]); 
            }
            // DELETE
            else if($REQUEST_METHOD == "DELETE"){
                $this->getVars();
                if(isset($_DELETE["threeModal"])){
                    $APIUrl = ($_DELETE["threeModal"])?$SysClass->GetAPIUrl('threeAPIURL'):$APIUrl;
                }

                $APIMethod = $_DELETE["api"];
                if(isset($_DELETE["data"])){
                    $SendArray = $_DELETE["data"];
                }

                $APIUrl .= $_DELETE["api"];
                if(!empty($_DELETE["contentType"])){
                    $contentType = $_DELETE["contentType"];
                }
                $response = $SysClass->UrlDataDelete( $APIUrl, $SendArray, $contentType, $_DELETE["threeModal"], $_DELETE["changeJson"]);
            }
            // PUT
            else if($REQUEST_METHOD == "PUT"){
                $this->getVars();
                if(isset($_PUT["threeModal"])){
                    $APIUrl = ($_PUT["threeModal"])?$SysClass->GetAPIUrl('threeAPIURL'):$APIUrl;
                }
                $APIMethod = $_PUT["api"];
                if(isset($_PUT["data"])){
                    $SendArray = $_PUT["data"];
                }

                $APIUrl .= $_PUT["api"];
                if(!empty($_PUT["contentType"])){
                    $contentType = $_PUT["contentType"];
                }
                $response = $SysClass->UrlDataPut( $APIUrl, $SendArray, $contentType, $_PUT["threeModal"]);
            }

            // print_r($response);
            // 不等於兩百的時候重新嘗試, 且重新嘗試次數大於0
            if($response['http_code'] != 200 and $this->callAPITryTime > 0){
                $this->reCallAPI($REQUEST_METHOD, $contentType, $APIUrl, $SendArray, $APIMethod, $SysClass);
                $response = $this->APIFinalResponse;
            }
            
            try{

                if($REQUEST_METHOD != "OPTIONS"){
                    if(!empty($response)){
                        $pageContent = $response["result"];
                        // $pageContent = $SysClass->Data2Json($response);
                    }else{
                        $response = array();
                        $response['status'] = false;
                        $response['errMsg'] = 'Could not Send Request, Http Method: '.$REQUEST_METHOD.', API: ' . $APIMethod.', Data: '.$SysClass->Data2Json($SendArray);
                        $action['http_code'] = 404;
                        $pageContent = $SysClass->Data2Json($response);
                    }
                }

            }catch(Exception $error){
                $pageContent = 'Try fail';
            }
            
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
        //關閉資料庫連線
        // $SysClass->DBClose();
        //釋放
        $SysClass = null;
        $this->viewContnet['pageContent'] = $pageContent;
    }

    private function reCallAPI($Request_Method, $contentType, $APIUrl, $SendArray, $APIMethod, $SysClass){
        global $_DELETE, $_PUT;
        usleep(100000);
        if($Request_Method == "GET"){
            $response = $SysClass->UrlDataGet( $APIUrl, $SendArray);
        }
        // POST
        else if($Request_Method == "POST"){
            $response = $SysClass->UrlDataPost( $APIUrl, $SendArray, $contentType); 
        }
        // DELETE
        else if($Request_Method == "DELETE"){
            $response = $SysClass->UrlDataDelete( $APIUrl, $SendArray, $contentType);
        }
        // PUT
        else if($Request_Method == "PUT"){
            $response = $SysClass->UrlDataPut( $APIUrl, $SendArray, $contentType);
        }

        if($response['http_code'] == 200 and $this->callAPITotalTime <= $this->callAPITryTime){
            $this->callAPITotalTime = 0;
            return $response;
        }else if($this->callAPITotalTime < $this->callAPITryTime){
            $this->callAPITotalTime++;
            // usleep(100000);
            // echo "T";
            $this->reCallAPI($Request_Method, $contentType, $APIUrl, $SendArray, $APIMethod, $SysClass);

        }else{
            $action = [];
            $action['status'] = false;
            $action['errMsg'] = 'Could not Send Request, Http Method: '.$Request_Method.', API: ' . $APIMethod.', Data: '.$SysClass->Data2Json($SendArray);
            $action['http_code'] = $response['http_code'];
            $action['Request_Method'] = $Request_Method;
            
            // $response = [];
            $response["result"] = $SysClass->Data2Json($action);
            $this->APIFinalResponse = $response;
            
        }
        return;
        
    } 

    //創建DELETE和PUT的變數
    private function getVars() {
        if (strlen(trim($vars = file_get_contents('php://input'))) === 0){
            $vars = false;
        }else{
            $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
            parse_str($vars, $GLOBALS["_".$REQUEST_METHOD]);
        }
    }
    //創建DELETE和PUT的變數 - 結束
}
