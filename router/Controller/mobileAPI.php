<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

class mobileAPIController
{

    //範例
    public function menuAction()
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
            $strSQL = "select * from sys_menu_mobile where hidden = 0 order by sequence,uid asc";
            $data = $SysClass->QueryData($strSQL);
            $action = [];
            $action["status"] = false;

            if(!empty($data)){
                $action['data'] = $data;
                $action['status'] = true;
            }else{
                $action['errMsg'] = 'No data';
            }
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

}
