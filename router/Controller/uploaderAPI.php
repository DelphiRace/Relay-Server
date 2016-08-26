<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

class uploaderAPIController
{

    //範例
    public function uploaderAction()
    {
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        $SysClass->initialization();
        try{
            if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
                return;
            }
            // 取得設定方法
            $APIUrl = $SysClass->GetAPIUrl('apiURL');

            // 設定取得檔案的暫存名稱
            // $file_name_with_full_path = realpath($_FILES["RS_file"]["tmp_name"]);
            if(!empty($_FILES) and !empty($_POST["api"])){
                $files = [];
                foreach ($_FILES as $key => $content) {
                    if(!is_array($content["tmp_name"])){
                        $fileFullPath = realpath($content["tmp_name"]);
                        $files["file[".count($files)."]"] = curl_file_create($fileFullPath, $content["type"], $content["name"]);
                    }else{
                        // 二維資料處理
                        foreach ($content["tmp_name"] as $tmpKey => $tmpVal) {
                            foreach ($tmpVal as $fileArrKey => $filePath) {
                                $fileFullPath = realpath($filePath);
                                $files["file[".$tmpKey."][".$fileArrKey."]"] = curl_file_create($fileFullPath, $content["type"][$tmpKey][$fileArrKey], $content["name"][$tmpKey][$fileArrKey]);
                            }
                        }
                    }
                }
                if(empty($_POST["data"])){
                    $dataArr = [];
                }else{
                    $dataArr = $_POST["data"];
                }
                $postArr = array_merge($files, $dataArr);

                $post = $postArr;

                // print_r($_FILES);
                // print_r($post);
                // exit();

                if(!empty($_POST["api"])){
                    // 呼叫API
                    $uploadUrl = $APIUrl . $_POST["api"];
                    // print_r($APIUrl);
                    // 開始上傳
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$uploadUrl);
                    curl_setopt($ch, CURLOPT_POST,1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

                    $header=curl_getinfo($ch);
                    $rs = curl_exec ($ch);
                    curl_close ($ch);
                    // 結束
                    // 顯示結果
                    $ServerInfo = [];
                    $ServerInfo["http_code"] = $header["http_code"];
                    $ServerInfo["http_header"] = $header;
                    $ServerInfo["result"] = $rs;
                    $ServerInfo["POST_Data"] = $dataArr;
                    $ServerInfo["File_Data"] = $_FILES;

                    // $pageContent = $SysClass->Data2Json($ServerInfo);
                    $pageContent = $rs;
                }else{
                    $action = [];
                    $action['status'] = false;
                    $action['errMsg'] = "api is empty!";
                    $pageContent = $SysClass->Data2Json($action);
                }
            }else{
                $action = [];
                $action['status'] = false;
                $action['errMsg'] = "file is empty!";
                $pageContent = $SysClass->Data2Json($action);
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

}
