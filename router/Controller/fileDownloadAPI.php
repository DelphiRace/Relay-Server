<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

class fileDownloadAPIController
{

    //範例
    public function indexAction()
    {
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        $SysClass->initialization(null,true);
        try{
            // start loop here
            $action["status"] = false;
            if(!empty($_POST)){
                $fileUid = $_POST["uid"];
                if($fileUid > 0){
                    // 取得設定方法
                    $APIUrl = $SysClass->GetAPIUrl('apiURL');
                    // RS下載檔案位置
                    $url = $APIUrl . "waFileServer/api/file/GetFile?uid=".$fileUid;

                    // 原本的檔名
                    $originalFileName = $fileName;
                    $file = $this->collect_file($url);

                    print($file);
                    exit();
                }
            }
            // $pageContent = $SysClass->Data2Json($action);
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
        //關閉資料庫連線
        // $SysClass->DBClose();
        //釋放
        $SysClass = null;
        // $this->viewContnet['pageContent'] = $pageContent;
    }

    private function collect_file($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        // curl_setopt($ch, CURLOPT_REFERER, "http://www.xcontest.org");
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return($result);
    }

}
