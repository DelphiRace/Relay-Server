<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

// 依照登入後 UUID進行權限確認
class userVerifyAPIController
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
    public function verifyUserAccountBySIDAction(){
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
                $sid = $_POST["sid"];
                $uuid = $_POST["uuid"];
                if($sid and $uuid){
                    // 先檢驗UUID是否是管理者或使用者其中一位
                    $strSQL = "select * from ac_admin where uuid = '".$uuid."'";
                    $adminData = $SysClass->QueryData($strSQL);
                    if(!empty($adminData)){
                        $action = $this->checkSID($SysClass, $sid);
                    }else{
                        $strSQL = "select * from ac_user where uuid = '".$uuid."'";
                        $userData = $SysClass->QueryData($strSQL);
                        if(!empty($userData)){
                            $action = $this->checkSID($SysClass, $sid);
                        }else{
                            $action["msg"] = "查無此 uuid";
                        }
                    }

                }else{
                    $action["msg"] = "sid&uuid 不可為空";
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

    private function checkSID($SysClass, $sid){
        $action = array();
        $action["status"] = false;

        $strSQL = "select * from ac_user where user_ac = '".$sid."'";
        $newAc = $SysClass->QueryData($strSQL);

        if(empty($newAc)){
            $action["status"] = true;
            $action["msg"] = "可以使用";
        }else{
            $action["msg"] = "不可使用";
        }
        return $action;
    }
}
