<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

class userRegisteredAPIController
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
        $SysClass->initialization();
        try{
            $action = array();
            $action["status"] = false;
            $action["msg"] = "請使用Action呼叫";
            $pageContent = $SysClass->Data2Json($action);
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
    // 註冊一般使用者帳號
    public function registeredAction()
    {
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        $SysClass->initialization("cm_auth",true);
        try{
            $action = array();
            $action["status"] = false;
            if(!empty($_POST)){
                $sys_code_uid = $_POST["sys_code_uid"];
                $user_ac = $_POST["user_ac"];
                $user_pw = $_POST["user_pw"];
                $create_uuid = $_POST["create_uuid"];
                $bps_user_uid = $_POST["bps_user_uid"];
                if($sys_code_uid and $user_ac and $user_pw and $create_uuid and $bps_user_uid){
                    // 先驗證UUID是否正確
                    $strSQL = "select * from ac_admin where uuid = '".$create_uuid."'";
                    $data = $SysClass->QueryData($strSQL);
                    $isAdmin = false;
                    $isExist = false;
                    if(!empty($data)){
                        $isAdmin = true;
                        $isExist = true;
                    }else{
                        $strSQL = "select * from ac_user where uuid = '".$create_uuid."'";
                        $data = $SysClass->QueryData($strSQL);
                        if(!empty($data)){
                            $isExist = true;
                        }
                    }

                    if($isExist){
                        // 先找看有沒有重複的帳號
                        $strSQL = "select * from ac_user where user_ac = '".$user_ac."'";
                        $data = $SysClass->QueryData($strSQL);
                        // 沒有重複才新增
                        if(empty($data)){
                            if($isAdmin){
                                $insertStr = "user_ac,user_pw,create_admin";
                            }else{
                                $insertStr = "user_ac,user_pw,create_user";
                            }
                            $strSQL = "insert into ac_user(".$insertStr.") ";
                            $strSQL .= "values('".$user_ac."',md5('".$user_pw."'),'".$create_uuid."');";

                            if($SysClass->Execute($strSQL)){
                                // 取得剛剛新增的資料
                                $strSQL = "select * from ac_user where user_ac = '".$user_ac."'";
                                $data = $SysClass->QueryData($strSQL);
                                $newUUID = $data[0]["uuid"];

                                if($isAdmin){
                                    $insertStr = "uuid,bps_user_uid,create_admin";
                                    $insertData = "'".$newUUID."','".$bps_user_uid."','".$create_uuid."'";
                                }else{
                                    $insertStr = "uuid,bps_user_uid";
                                    $insertData = "'".$newUUID."',md5('".$user_pw."')'";
                                }

                                $strSQL = "insert into sys_user_data(".$insertStr.") ";
                                $strSQL .= "values(".$insertData.");";

                                $strSQL .= "insert into sys_user_match(user_uuid,sys_code_uid,match_date) ";
                                $strSQL .= "values('".$newUUID."','".$sys_code_uid."','".date("Y-m-d H:i:s")."');";
                                // $action["msg"] = $strSQL;
                                if($SysClass->Execute($strSQL)){

                                    $info = array();
                                    $info["bps_user_uid"] = $bps_user_uid;
                                    $info["user"] = $user_ac;

                                    $action["msg"] = "註冊成功";
                                    $action["data"] = $info;
                                    $action["status"] = true;
                                }else{
                                    $action["msg"] = "帳號資料整合失敗";
                                    $action["msg"] = $strSQL;

                                }
                            }else{
                                $action["msg"] = "帳號註冊失敗";
                                // $action["msg"] = $strSQL;
                            }
                        }else{
                            // 已重複
                            $action["msg"] = "已有重複帳號，註冊失敗";
                        }
                    }else{
                        $action["msg"] = "UUID: ".$create_uuid." 無法驗證，請重新登入後再嘗試註冊帳號";
                    }
                }else{
                    $action["msg"] = "相關參數未帶入值";
                }
            }

            $pageContent = $SysClass->Data2Json($action);
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
        //關閉資料庫連線
        $SysClass->DBClose();
        //釋放
        $SysClass = null;
        $this->viewContnet['pageContent'] = $pageContent;
    }

    // 取得管理員帳號列表
    public function adminlistAction()
    {
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        $SysClass->initialization("cm_auth",true);
        try{
            $action = array();
            $action["status"] = false;
            
            $strSQL = "select user_ac,sys_code_uid from ac_admin ";
            $strSQL .= "where sys_code_uid > 0 ";
            $data = $SysClass->QueryData($strSQL);
            if(!empty($data)){
                $action["status"] = true;
                $action["data"] = $data;
            }

            $pageContent = $SysClass->Data2Json($action);
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
        //關閉資料庫連線
        $SysClass->DBClose();
        //釋放
        $SysClass = null;
        $this->viewContnet['pageContent'] = $pageContent;
    }

    public function Insert_AssUserComplexAction(){
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        $SysClass->initialization();
        try{
            // 取得ＡＰＩ位置
            $APIUrl = $SysClass->GetAPIUrl('threeAPIURL');
            $contentType = "application/x-www-form-urlencoded; charset=UTF-8";
            $action = array();
            $action["Status"] = false;

            if(!empty($_POST["userInfo"]) and !empty($_POST["census"]) and !empty($_POST["communication"]) and !empty($_POST["org"])){

                if(!empty($_POST["contentType"])){
                    $contentType = $_POST["contentType"];
                }
                $SendArray = $_POST["userInfo"];
                // 先註冊使用者資料
                $response = $SysClass->UrlDataPost( $APIUrl."AssCommon/Insert_AssCommon", $SendArray, $contentType, true); 
                $responseArr = $SysClass->Json2Data($response["result"],false);
                if($responseArr["Status"]){
                    $_POST["userInfo"]["uid"] = $responseArr["Data"];
                    $_POST["org"]["cmid"] = $responseArr["Data"];
                    $_POST["census"]["cmid"] = $responseArr["Data"];
                    $_POST["communication"]["cmid"] = $responseArr["Data"];

                    $SendArray = $_POST["census"];
                    // 再註冊地址
                    $response = $SysClass->UrlDataPost( $APIUrl."AssCommonAddress/Insert_AssCommonAddress", $SendArray, $contentType, true); 
                    
                    // 再註冊通訊地址
                    $SendArray = $_POST["data"]["communication"];
                    $response = $SysClass->UrlDataPost( $APIUrl."AssCommonAddress/Insert_AssCommonAddress", $SendArray, $contentType, true); 

                    

                    // 再註冊使用者
                    $SendArray = array();
                    $SendArray["cmid"] =  $responseArr["Data"];
                    $SendArray["orgid"] = $_POST["org"]["org"][0];
                    $SendArray["posid"] = (count($_POST["org"]["job"])) ? $_POST["org"]["job"][0]: "0";
                    $SendArray["uuid"] = $_POST["uuid"];
                    $SendArray["sid"] = $_POST["userInfo"]["sid"];
                    $SendArray["userID"] = $_POST["userInfo"]["userID"];
                    $SendArray["sys_code"] = $_POST["sys_code"];

                    $response = $SysClass->UrlDataPost( $APIUrl."AssUser/Insert_AssUser", $SendArray, $contentType, true);
                    $userResponseArr = $SysClass->Json2Data($response["result"],false);
                    if($userResponseArr["Status"]){
                        $action["Status"] = true;

                        $action["userID"] = $userResponseArr["Data"];
                        $action["cmid"] = $responseArr["Data"];
                    }else{
                        // 刪除剛剛新增的使用者資訊
                        $SendArray = array();
                        $SendArray["cmid"] = $responseArr["Data"];
                        $response = $SysClass->UrlDataDelete( $APIUrl."AssCommonAddress/Delete_AssCommonAddressCmid", $SendArray, $contentType, true); 

                        $SendArray = array();
                        $SendArray["iUid"] = $responseArr["Data"];
                        $response = $SysClass->UrlDataDelete( $APIUrl."AssCommon/Delete_AssCommon", $SendArray, $contentType, true);
                        $action["msg"] = "註冊失敗";
                    }
                }else{
                    $action["msg"] = "註冊AssCommon失敗";
                }
            }else{
                $action["msg"] = "data 參數不可為空";
            }
            $pageContent = $SysClass->Data2Json($action);

        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
        //釋放
        $SysClass = null;
        $this->viewContnet['pageContent'] = $pageContent;
    }

    public function Update_AssUserComplexAction(){
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        $SysClass->initialization();
        try{
            // 取得ＡＰＩ位置
            $APIUrl = $SysClass->GetAPIUrl('threeAPIURL');
            $contentType = "application/x-www-form-urlencoded; charset=UTF-8";
            $action = array();
            $action["Status"] = false;

            if(!empty($_POST["userInfo"]) and !empty($_POST["census"]) and !empty($_POST["communication"]) and !empty($_POST["org"])){

                if(!empty($_POST["contentType"])){
                    $contentType = $_POST["contentType"];
                }
                $SendArray = $_POST["userInfo"];
                // 先註冊使用者資料
                $response = $SysClass->UrlDataPost( $APIUrl."AssCommon/Update_AssCommon", $SendArray, $contentType, true); 
                $responseArr = $SysClass->Json2Data($response["result"],false);
                if($responseArr["Status"]){
                    $SendArray = $_POST["census"];
                    // 再註冊地址
                    $response = $SysClass->UrlDataPost( $APIUrl."AssCommonAddress/Update_AssCommonAddress", $SendArray, $contentType, true); 

                    // 再註冊通訊地址
                    $SendArray = $_POST["communication"];
                    $response = $SysClass->UrlDataPost( $APIUrl."AssCommonAddress/Update_AssCommonAddress", $SendArray, $contentType, true); 

                    // 再註冊使用者
                    $SendArray = array();
                    $SendArray["cmid"] =  $_POST["userInfo"]["uid"];
                    $SendArray["orgid"] = $_POST["org"]["org"][0];
                    $SendArray["posid"] = (count($_POST["org"]["job"])) ? $_POST["org"]["job"][0]: "0";
                    $SendArray["uuid"] = $_POST["uuid"];
                    $SendArray["sid"] = $_POST["userInfo"]["sid"];
                    $SendArray["userID"] = $_POST["userInfo"]["userID"];
                    $SendArray["sys_code"] = $_POST["sys_code"];

                    $response = $SysClass->UrlDataPost( $APIUrl."AssUser/Update_AssUser", $SendArray, $contentType, true);
                    $userResponseArr = $SysClass->Json2Data($response["result"],false);
                    // print_r($userResponseArr);
                    $action = $userResponseArr;
                }else{
                    $action["msg"] = "修改失敗";
                }
            }else{
                $action["msg"] = "data 參數不可為空";
            }
            $pageContent = $SysClass->Data2Json($action);
        }catch(Exception $error){
            //依據Controller, Action補上對應位置, $error->getMessage()為固定部份
            $SysClass->WriteLog("SupplyController", "editorAction", $error->getMessage());
        }
        //釋放
        $SysClass = null;
        $this->viewContnet['pageContent'] = $pageContent;
    }
}
