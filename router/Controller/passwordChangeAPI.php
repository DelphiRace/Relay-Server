<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

// 依照登入後 UUID進行權限確認
class passwordChangeAPIController
{
    public function indexAction()
    {
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        try{
            $action = array();
            $action["status"] = false;
            $action["msg"] = "請使用其他方法";
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

    // 驗證使用者的帳號(新註冊時使用，透過身份證)
    public function passChangeAction(){
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        $SysClass->initialization("cm_auth",true);
        try{
            $action = array();
            $action["status"] = false;
            if(!empty($_POST)){
                $uuid = $_POST["uuid"];
                $oldPass = $_POST["oldPass"];
                $newPass = $_POST["newPass"];
                if($uuid and $oldPass and $newPass){
                    // 先檢驗UUID是否是管理者或使用者其中一位
                    $strSQL = "select * from ac_admin where uuid = '".$uuid."'";
                    $adminData = $SysClass->QueryData($strSQL);
                    if(!empty($adminData)){
                        if( $adminData[0]["user_pw"] == md5($oldPass) ){
                            $strSQL = "update ac_admin set user_pw=md5('".$newPass."') where uuid='".$uuid."'";
                            if($SysClass->Execute($strSQL)){
                                $action["msg"] = "密碼變更成功";
                                $action["status"] = true;
                            }else{
                                $action["msg"] = "密碼變更失敗";
                            }
                        }else{
                            $action["msg"] = "舊密碼錯誤";
                        }
                    }else{
                        $strSQL = "select * from ac_user where uuid = '".$uuid."'";
                        $userData = $SysClass->QueryData($strSQL);
                        if(!empty($userData)){
                            if( $userData[0]["user_pw"] == md5($oldPass) ){
                                $strSQL = "update ac_user set user_pw=md5('".$newPass."') where uuid='".$uuid."'";
                                if($SysClass->Execute($strSQL)){
                                    $action["msg"] = "密碼變更成功";
                                    $action["status"] = true;
                                }else{
                                    $action["msg"] = "密碼變更失敗";
                                }
                            }else{
                                $action["msg"] = "舊密碼錯誤";
                            }
                        }else{
                            $action["msg"] = "查無此 uuid";
                        }
                    }

                }else{
                    $action["msg"] = "uuid & oldPass & newPass  不可為空";
                }
            }else{
                $action["msg"] = "不支援此方法";
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
