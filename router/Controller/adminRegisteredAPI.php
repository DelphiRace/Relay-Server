<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

class adminRegisteredAPIController
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
    // 註冊管理員帳號
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
                $customersID = $_POST["customersID"];
                if($sys_code_uid and $user_ac and $user_pw and $customersID){
                    $strSQL = "insert into ac_admin(user_ac,user_pw,sys_code_uid) ";
                    $strSQL .= "values('".$user_ac."',md5('".$user_pw."'),'".$sys_code_uid."');";

                    if($SysClass->Execute($strSQL)){
                        // $newID = $SysClass->NewInsertID();
                        $info = array();
                        $info["uid"] = $customersID;
                        $info["admin"] = $user_ac;

                        $action["msg"] = "註冊成功";
                        $action["data"] = $info;
                        $action["status"] = true;
                        // 開啟系統限制
                        $this->systemLimit($sys_code_uid, $SysClass);
                    }else{
                        $action["msg"] = "管理員帳號註冊失敗";
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

    // match管理員帳號
    public function matchAction()
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
                $admin_uuid = $_POST["admin_uuid"];
                $bps_user_uid = $_POST["bps_user_uid"];

                if($admin_uuid and $bps_user_uid){
                    $strSQL = "select * from sys_admin_data where uuid = '".$admin_uuid."'";
                    $data = $SysClass->QueryData($strSQL);
                    if(empty($data)){

                        $strSQL = "insert into sys_admin_data(uuid,bps_user_uid) ";
                        $strSQL .= "values('".$admin_uuid."','".$bps_user_uid."');";

                        if($SysClass->Execute($strSQL)){
                            $action["msg"] = "管理員帳號資訊更新成功";
                            $action["status"] = true;
                        }else{
                            $action["msg"] = "管理員帳號資訊更新失敗";
                            // $action["msg"] = $strSQL;

                        }
                    }else{
                        $action["msg"] = "管理員帳號資訊已有";

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
            
            $strSQL = "select t1.user_ac, t1.sys_code_uid, t2.limit_end_date as limitTime from ac_admin t1 ";
            $strSQL .= "left join sys_limit t2 on t1.sys_code_uid = t2.sys_code_uid ";
            $strSQL .= "where t1.sys_code_uid > 0 ";
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

    // 啟用系統限制
    private function systemLimit($sysCodeID, $SysClass){
        $strSQL = "select * from sys_limit where sys_code_uid = ".$sysCodeID;
        $data = $SysClass->QueryData($strSQL);

        if(empty($data)){
            $nowTime = strtotime(date("Y-m-d"));
            $strSQL = "insert into sys_limit(sys_code_uid,limit_start_date,limit_end_date)";
            $strSQL .= "values(".$sysCodeID.",".$nowTime.",".$nowTime.")";
            $SysClass->Execute($strSQL);
        }
    }

}
