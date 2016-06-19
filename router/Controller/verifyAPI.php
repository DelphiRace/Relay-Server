<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

// 依照登入後 UUID進行權限確認
class verifyAPIController
{

    public function indexAction()
    {
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        $SysClass->initialization("cm_auth",true);
        
        //-----------驗證開始------------
        try{
            //設定資訊陣列
            $uidInfo = array();
            //資訊狀態
            $uidInfo["status"] = false;
            
            //檢測是否有傳入帳號與密碼
            if(!empty($_POST) and !empty($_POST["uuid"])){
                $uuid = $_POST["uuid"];

                // 先驗證是否為一般使用者權限
                $data = $this->verifyUser($SysClass, $uuid);
                //如果不是空的，就進行回送資料設定
                if(!empty($data)){
                    $uidInfo = $this->requestData($uuid);
                    $uidInfo["menuPosition"] = $this->setLoginUser($data, false);
                    $uidInfo["sysList"] = $this->userSysList($data, false);

                    $uidInfo["isAdmin"] = false;
                }else{// 如果是空的，再驗證是不是管理員
                    $data = $this->verifyAdmin($SysClass, $uuid);
                    // 是管理員的話，進行回送資料設定
                    if(!empty($data)){
                        $uidInfo = $this->requestData($uuid);
                        $uidInfo["menuPosition"] = $this->setLoginUser($data, true);
                        $uidInfo["sysList"] = $this->userSysList($data, ture);
                        $uidInfo["isAdmin"] = true;

                    }else{ // 不是管理員、也不是使用者的話，進行錯誤認證
                        $uidInfo = $this->requestErrorData();
                    }
                }
                // print_r($_SESSION);
                
                //3.寫入LOG
                //$SysClass->saveLog('loginAction','system','creatToken',$uidInfo["status"]);
            }else{//1-1 帳號密碼為空，回傳狀態
                $uidInfo["error"] = 'Accound or Password is Empty';
                $uidInfo["code"] = '1';
            }
            $pageContent = $SysClass->Data2Json($uidInfo);
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
        //-----------BI結束------------ 
        
        //關閉資料庫連線
        $SysClass->DBClose();
        //釋放
        $SysClass = null;
        $this->viewContnet['pageContent'] = $pageContent;
    }


    // 驗證使用者
    private function verifyUser($SysClass, $uuid){
        //登入驗證步驟
        //1.檢驗帳號與密碼(若錯誤回傳錯誤)
        $strSQL = "select * from ac_user where uuid = '".$uuid."'";
        $data = $SysClass->QueryData($strSQL);
        
        return $data;
    }

    // 驗證管理員
    private function verifyAdmin($SysClass, $uuid){
        //登入驗證步驟
        //1.檢驗uuid
        $strSQL = "select * from ac_admin where uuid = '".$uuid."'";
        $data = $SysClass->QueryData($strSQL);

        return $data;
    }

    // 設置回傳資訊
    private function requestData($uuid){
        //設定資訊陣列
        $uidInfo = array();
        //資訊狀態
        $uidInfo["status"] = false;

        if(!empty($uuid)){
            $uidInfo["uuid"] = $uuid;
            $uidInfo["status"] = true;
        }
        return $uidInfo;
    }

    // 設置錯誤訊息
    private function requestErrorData(){
        //設定資訊陣列
        $uidInfo = array();
        //資訊狀態
        $uidInfo["status"] = false;
        $uidInfo["error"] = 'The uuid is not exist!';
        $uidInfo["code"] = '4';

        return $uidInfo;
    }

    // 取得權限相關
    private function setLoginUser($uuid, $isSuAdmin = false){
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        $SysClass->initialization(null,true);
        try{
            // session_start();
            // 選單權限
            $menuPosition = "";
            // 是管理員
            if($isSuAdmin){
                $strSQL = "select bps_menu_id from sys_menu ";
                $strSQL .= "where hidden = 0 and bps_menu_id is not null ";                
                $strSQL .= "order by sequence,uid asc ";
                $data = $SysClass->QueryData($strSQL);

                $position = array();
                if(!empty($data)){
                    
                    foreach ($data as $content) {
                        array_push($position, $content["bps_menu_id"]);
                    }
                }
                $menuPosition = implode(",", $position);
            }else{
            // 不是管理員

                // 1.先呼叫bps取得權限
                // 以下部分未完，因未知bps後面對應的user uid
                // $APIUrl = $SysClass->GetAPIUrl('apiURL');
                // $sendData = array();
                // $bps_menu_position = $SysClass->UrlDataGet($APIUrl,$sendData);
                $bps_menu_position_id = 0;

                // 2. 根據bps給予的權限進行篩選
                $strSQL = "select bps_menu_id from sys_menu ";
                $strSQL .= "where hidden = 0 and (bps_menu_id is not null or (uid = 1 or uid = 3)) ";
                // BPS使用者權限
                if($bps_menu_position_id){
                    $strSQL .= "and (bps_menu_id in (".$bps_menu_position_id.") or is_base = 1) ";
                }else{
                    $strSQL .= "and is_base = 1 ";
                }
                $strSQL .= "and bps_menu_id is not null";
                $strSQL .= "order by sequence,uid asc ";
            }
            return $menuPosition;
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
    }

    // 取得系統
    private function userSysList($data, $isSuAdmin = false){
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        $SysClass->initialization("cm_auth",true);
        try{
            // session_start();
            // 使用者系統代碼
            $sysCode = array();
            // 是管理員
            if($isSuAdmin){
                array_push($sysCode,$data[0]["sys_code_uid"]);
            }else{
            // 不是管理員

                // 1.先呼叫bps取得權限
                // 以下部分未完，因未知bps後面對應的user uid
                $strSQL = "select * from sys_user_match ";
                $strSQL .= "where user_uuid = '".$data[0]["uuid"]."'";
                $sysData = $SysClass->QueryData($strSQL);
                foreach ($sysData as $content) {
                    array_push($sysCode,$content["sys_code_uid"]);
                }
            }
            return $sysCode;
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
    }
}
