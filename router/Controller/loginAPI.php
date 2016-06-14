<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

class loginAPIController
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
            if(!empty($_POST) and !empty($_POST["userAc"]) and !empty($_POST["userPw"])){
                $userAc = strtolower($_POST["userAc"]);
                $userPw = $_POST["userPw"];

                // 先驗證是否為一般使用者
                $data = $this->verifyUser($SysClass, $userAc, $userPw);
                //如果不是空的，就進行回送資料設定
                if(!empty($data)){
                    $uidInfo = $this->requestData($data, $userAc);
                    $uidInfo["menuPosition"] = $this->setLoginUser($data, false);
                }else{// 如果是空的，再驗證是不是管理員
                    $data = $this->verifyAdmin($SysClass, $userAc, $userPw);
                    // 是管理員的話，進行回送資料設定
                    if(!empty($data)){
                        $uidInfo = $this->requestData($data, $userAc);
                        $uidInfo["menuPosition"] = $this->setLoginUser($data, true);
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

    // 登出
    public function logoutAction()
    {
        $account = array();
        $account["status"] = true;
        @session_start();
        @session_destroy();

        $this->viewContnet['pageContent'] = json_encode($account);
    }

     public function testAction()
    {
        // @session_start();
        print_r($_SESSION);
        $this->viewContnet['pageContent'] = "";
    }
    // 驗證使用者
    private function verifyUser($SysClass, $userAc, $userPw){
        //登入驗證步驟
        //1.檢驗帳號與密碼(若錯誤回傳錯誤)
        $strSQL = "select * from ac_user where user_ac = '".$userAc."' and user_pw = md5('".$userPw."')";
        $data = $SysClass->QueryData($strSQL);
        
        return $data;
    }

    // 驗證管理員
    private function verifyAdmin($SysClass, $userAc, $userPw){
        //登入驗證步驟
        //1.檢驗帳號與密碼(若錯誤回傳錯誤)
        $strSQL = "select * from ac_admin where user_ac = '".$userAc."' and user_pw = md5('".$userPw."')";
        $data = $SysClass->QueryData($strSQL);

        return $data;
    }

    // 設置回傳資訊
    private function requestData($acData, $userAc){
        //設定資訊陣列
        $uidInfo = array();
        //資訊狀態
        $uidInfo["status"] = false;

        if(!empty($acData)){
            $uidInfo["uuid"] = $acData[0]["uuid"];
            $uidInfo["userAc"] = $userAc;
            // $uidInfo["name"] = $acData[0]["userName"];
            $uidInfo["name"] = $userAc;
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
        $uidInfo["error"] = 'The Accound is not Sing up!';
        $uidInfo["code"] = '2';

        return $uidInfo;
    }

    // 設置登入session
    private function setLoginUser($loginData, $isSuAdmin = false){
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
                $APIUrl = $SysClass->GetAPIUrl('apiURL');
                $sendData = array();
                $bps_menu_position = $SysClass->UrlDataGet($APIUrl,$sendData);

                // 2. 根據bps給予的權限進行篩選
                // $strSQL = "select bps_menu_id from sys_menu ";
                // $strSQL .= "where hidden = 0 and (bps_menu_id is not null or (uid = 1 or uid = 3)) ";
                // // BPS使用者權限
                // $strSQL .= "and bps_menu_id in (".$bps_menu_position_id.") ";
                
                // $strSQL .= "order by sequence,uid asc ";
            }
            return $menuPosition;
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
    }
}
