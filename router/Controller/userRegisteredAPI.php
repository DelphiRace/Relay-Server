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

}
