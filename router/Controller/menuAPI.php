<?php
/*
    Example Method for api
*/
use SystemCtrl\ctrlSystem;

class menuAPIController
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
            // 之後會先與BPS確認權限，之後才進行下面
            $strSQL = "select t1.nid, t2.url, t3.parent, t4.class_name, t1.memo from sys_menu t1 ";
            $strSQL .= "left join sys_menu_url t2 on t1.uid = t2.m_uid ";
            $strSQL .= "left join sys_menu_parents t3 on t1.uid = t3.m_uid ";
            $strSQL .= "left join sys_menu_class t4 on t1.uid = t4.m_uid ";
            $strSQL .= "where t1.hidden = 0 ";
            // BPS確認後，再把下面打開
            // $strSQL .= "and t1.bps_menu_id in (".$bps_menu_position_id.") ";
            
            $strSQL .= "order by t1.sequence,t1.uid asc ";

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


    //user權限選單
    public function userMenuAction()
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
            $action = [];
            $action["status"] = false;
            // if($_POST["menuPosition"]){
                $menuPositionArr = explode(",",$_POST["menuPosition"]);
                foreach($menuPositionArr as $key => $content){
                    $menuPositionArr[$key] = "'".$content."'";
                }
                $bps_menu_position_id = implode(",", $menuPositionArr);
                // 之後會先與BPS確認權限，之後才進行下面
                $strSQL = "select t1.uid, t1.nid, t2.url, t3.parent, t4.class_name, t1.memo from sys_menu t1 ";
                $strSQL .= "left join sys_menu_url t2 on t1.uid = t2.m_uid ";
                $strSQL .= "left join sys_menu_parents t3 on t1.uid = t3.m_uid ";
                $strSQL .= "left join sys_menu_class t4 on t1.uid = t4.m_uid ";
                $strSQL .= "where t1.hidden = 0 ";
                // 有權限
                if($_POST["menuPosition"]){
                    $strSQL .= "and (t1.bps_menu_id in (".$bps_menu_position_id.") or (t1.uid = 1 or t1.uid = 3) or is_base = 1)";
                }else{
                // 沒有權限
                    $strSQL .= "and ((t1.uid = 1 or t1.uid = 3) or is_base = 1)";
                }
                $strSQL .= "order by t1.sequence,t1.uid asc ";

                $data = $SysClass->QueryData($strSQL);

                if(!empty($data)){

                    $action['data'] = $data;
                    $action['status'] = true;
                }else{
                    $action['errMsg'] = 'No data';
                    $action['strSQL'] = $strSQL;
                }
            // }else{
            //     $action['errMsg'] = 'No Position';
            //     // $action['strSQL'] = $strSQL;
            // }
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

    //user權限選單
    public function userMenuPositionListAction()
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
            // 之後會先與BPS確認權限，之後才進行下面
            $strSQL = "select t1.uid, t1.nid, t2.url, t3.parent, t4.class_name, t1.memo, t1.bps_menu_id from sys_menu t1 ";
            $strSQL .= "left join sys_menu_url t2 on t1.uid = t2.m_uid ";
            $strSQL .= "left join sys_menu_parents t3 on t1.uid = t3.m_uid ";
            $strSQL .= "left join sys_menu_class t4 on t1.uid = t4.m_uid ";
            $strSQL .= "where t1.uid not in(1,3) ";
            $strSQL .= "order by t3.parent,t1.uid asc ";

            $data = $SysClass->QueryData($strSQL);

            $action = [];
            $action["status"] = false;

            if(!empty($data)){
                $transData = $data;
                if(isset($_GET["bpsID"])){
                    if($_GET["bpsID"]){

                        // 取得設定方法
                        $APIUrl = $SysClass->GetAPIUrl('apiURL');
                        $menuInfo = $this->getBpsAllMenuInfo($APIUrl, $SysClass);
                        
                        if($menuInfo["Status"]){
                            $tmpBpsMenuArr = [];
                            foreach ($menuInfo["Data"] as $key => $content) {
                                $tmpBpsMenuArr[$content["opid"]] = $content["uid"];
                            }
                            
                            foreach ($transData as $key => $content) {
                                if($content["bps_menu_id"]){
                                    $transData[$key]["bpsID"] = $tmpBpsMenuArr[$content["bps_menu_id"]];
                                }
                            }
                        }
                    }
                }
                $action['data'] = $transData;
                $action['status'] = true;
            }else{
                $action['errMsg'] = 'No data';
                $action['strSQL'] = $strSQL;
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

    // 選單新增
    public function menuInsertAction()
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
            // 取得設定方法
            $APIUrl = $SysClass->GetAPIUrl('apiURL');
            
            $action = [];
            $action["status"] = false;
            $bpsMidIsNull = false;

            if(!empty($_POST)){

                if($_POST["parent"] != 0){

                    $strSQL = "select * from sys_menu where uid = '".$_POST["parent"]."'";
                    $data = $SysClass->QueryData($strSQL);

                    if($data[0]["bps_menu_id"]){
                        $meunInfo = $this->getMenuInfo($APIUrl, $data[0]["bps_menu_id"], $SysClass);

                        $faid = $meunInfo["Data"][0]["uid"];
                    }else{
                        $bpsMidIsNull = true;
                    }
                }else{

                    $faid = 0;

                }
                
                if(!$bpsMidIsNull){
                    $insertAPI = $APIUrl."v201604/sys/api/ctrlSysMenu/Insert_SysMenuInfo";
                    $SendArray = [ "name"=>$_POST["memo"], "faid"=>$faid ];
                    
                    if($data[0]["parent"] != 0){
                        $SendArray['seq_sys'] = $_POST["sequence"];
                        $SendArray['seq'] = 0;
                    }else{
                        $SendArray['seq_sys'] = 0;
                        $SendArray['seq'] = $_POST["sequence"];
                    }

                    $response = $SysClass->UrlDataPost( $insertAPI, $SendArray);
                    $result = $SysClass->Json2Data($response["result"],false);
                    
                    if($result["Status"]){
                        // 這邊如果始終不給予opid，那就自行使用GET反查剛剛給的uid資訊
                        $newMeunInfo = $this->getMenuInfo($APIUrl, $result["Data"], $SysClass, true);
                        
                        if($newMeunInfo["Status"]){

                            $SysClass->Transcation();

                            $strSQL = "insert into sys_menu(nid,bps_menu_id,sequence,memo) values('".$_POST['nid']."','".$newMeunInfo["Data"][0]["opid"]."','".$_POST["sequence"]."','".$_POST["memo"]."');";

                            if($SysClass->Execute($strSQL)){

                                $newID = $SysClass->NewInsertID();

                                $strSQL = "insert into sys_menu_parents(m_uid,parent) values(".$newID.",'".$_POST["parent"]."');";
                                $SysClass->Execute($strSQL);

                                $strSQL = "insert into sys_menu_url(m_uid,url) values(".$newID.",'".$_POST["url"]."');";
                                $SysClass->Execute($strSQL);

                                // $strSQL .= "insert into sys_menu_parents(m_uid,class_name) values('".$newID."','".$_POST["class_name"]."');";
                                // $SysClass->Execute($strSQL);

                                $SysClass->Commit();

                                // 抓資料出來
                                $strSQL = "select t1.uid, t1.nid, t2.url, t3.parent, t4.class_name, t1.memo, t1.bps_menu_id from sys_menu t1 ";
                                $strSQL .= "left join sys_menu_url t2 on t1.uid = t2.m_uid ";
                                $strSQL .= "left join sys_menu_parents t3 on t1.uid = t3.m_uid ";
                                $strSQL .= "left join sys_menu_class t4 on t1.uid = t4.m_uid ";
                                $strSQL .= "where t1.uid = '".$newID."'";
                                $data = $SysClass->QueryData($strSQL);

                                $action['data'] = $data[0];

                                $action['status'] = true;
                            }else{
                                $SysClass->Rollback();
                                $action['errMsg'] = 'RS insert Error';
                            }
                        }else{
                            $action['errMsg'] = 'Get New Menu Data Error, '.$newMeunInfo;
                        }
                    }else{
                        $action['errMsg'] = $response;
                    }
                }else{
                    $action['errMsg'] = "parent's bps_menu_id is Null, please insert from parnet";
                }
                
            }else{
                $action['errMsg'] = 'uid error';
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

    // 選單修改
    public function menuModifyAction()
    {
        $SysClass = new ctrlSystem;
        // 預設不連資料庫
        // $SysClass->initialization();
        // 連線指定資料庫
        // $SysClass->initialization("設定檔[名稱]",true); -> 即可連資料庫
        // 連線預設資料庫
        // $SysClass->initialization(null,true);
        // 需要用到的有 uid, memo, sequence, nid, parent, url
        $SysClass->initialization(null,true);
        try{
            // 取得設定方法
            $APIUrl = $SysClass->GetAPIUrl('apiURL');

            $action = [];
            $action["status"] = false;

            if(!empty($_POST["uid"])){
                $uid = $_POST["uid"];
                $strSQL = "select t1.*,t3.parent from sys_menu t1 ";
                $strSQL .= "left join sys_menu_parents t3 on t1.uid = t3.m_uid ";
                $strSQL .= "where t1.uid ='".$uid."' ";
                $data = $SysClass->QueryData($strSQL);
                $bpsMidIsNull = false;
                // 取得父層資料
                if($_POST["parent"] != 0){

                    $strSQL = "select * from sys_menu where uid = '".$_POST["parent"]."'";
                    $parentData = $SysClass->QueryData($strSQL);
                    if($parentData[0]["bps_menu_id"]){

                        $meunInfo = $this->getMenuInfo($APIUrl, $parentData[0]["bps_menu_id"], $SysClass);

                        $faid = $meunInfo["Data"][0]["uid"];
                    }else{
                        $bpsMidIsNull = true;
                    }

                }else{

                    $faid = 0;

                }

                if(!$bpsMidIsNull){
                    // 對RS、BPS進行修改
                    if(!empty($data[0]["bps_menu_id"])){

                        $meunInfo = $this->getMenuInfo($APIUrl, $data[0]["bps_menu_id"], $SysClass);

                        if($meunInfo["Status"]){

                            $modifyAPIUrl .= $APIUrl."v201604/sys/api/ctrlSysMenu/Update_SysMenuInfo";
                            $SendArray = [ "uid"=>$meunInfo["Data"][0]["uid"],"name"=>$_POST["memo"], "faid"=>$faid ];
                            
                            if($data[0]["parent"] != 0){
                                $SendArray['seq_sys'] = $_POST["sequence"];
                                $SendArray['seq'] = 0;
                            }else{
                                $SendArray['seq_sys'] = 0;
                                $SendArray['seq'] = $_POST["sequence"];
                            }
                            
                            $response = $SysClass->UrlDataPost( $modifyAPIUrl, $SendArray);
                            $result = $SysClass->Json2Data($response["result"],false);

                            if($result["Status"]){

                                $strSQL = "update sys_menu set memo='".$_POST["memo"]."', sequence='".$_POST["sequence"]."' ";
                                if($_POST["nid"] != $data[0]["nid"]){
                                    $strSQL .= ",nid='".$_POST["nid"]."' ";
                                }
                                $strSQL .= "where uid='".$_POST["uid"]."';";
                                
                                $strSQL .= "update sys_menu_parents set parent='".$_POST["parent"]."' ";
                                $strSQL .= "where m_uid='".$_POST["uid"]."';";

                                $strSQL .= "update sys_menu_url set url='".$_POST["url"]."' ";
                                $strSQL .= "where m_uid='".$_POST["uid"]."';";

                                $SysClass->Execute($strSQL);

                                $action['data'] = 'modify success';
                                $action['status'] = true;
                            }else{
                                $action['errMsg'] = $response;
                            }
                        }else{
                            $action['errMsg'] = 'bps_menu_id is error, '.$meunInfo;
                        }
                    }else{
                    // 對BPS進行新增，在修改RS
                        $insertAPI = $APIUrl."v201604/sys/api/ctrlSysMenu/Insert_SysMenuInfo";
                        $SendArray = [ "name"=>$_POST["memo"], "faid"=>$faid ];
                        
                        if($data[0]["parent"] != 0){
                            $SendArray['seq_sys'] = $_POST["sequence"];
                            $SendArray['seq'] = 0;
                        }else{
                            $SendArray['seq_sys'] = 0;
                            $SendArray['seq'] = $_POST["sequence"];
                        }
                        
                        $response = $SysClass->UrlDataPost( $insertAPI, $SendArray);
                        $result = $SysClass->Json2Data($response["result"],false);
                        if($result["Status"]){

                             // 這邊如果始終不給予opid，那就自行使用GET反查剛剛給的uid資訊
                            $newMeunInfo = $this->getMenuInfo($APIUrl, $result["Data"], $SysClass, true);
                            
                            if($newMeunInfo["Status"]){

                                $strSQL = "update sys_menu set memo='".$_POST["memo"]."',nid='".$_POST["nid"]."',sequence='".$_POST["sequence"]."',bps_menu_id='".$newMeunInfo["Data"][0]["opid"]."' ";
                                $strSQL .= "where uid='".$_POST["uid"]."';";
                                
                                $strSQL .= "update sys_menu_parents set parent='".$_POST["parent"]."' ";
                                $strSQL .= "where m_uid='".$_POST["uid"]."';";

                                $strSQL .= "update sys_menu_url set url='".$_POST["url"]."' ";
                                $strSQL .= "where m_uid='".$_POST["uid"]."';";

                                $SysClass->Execute($strSQL);

                                $action['data'] = 'modify success';
                                $action['status'] = true;
                            }else{
                                $action['errMsg'] = 'Menu Info error,'.$newMeunInfoResult;

                            }
                        }else{
                            $action['errMsg'] = $response;
                        }
                    }
                }else{
                    $action['errMsg'] = "parent's bps_menu_id is Null, please insert from parnet";
                }
            }else{
                $action['errMsg'] = 'uid error';
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

    private function getMenuInfo($APIUrl, $bps_menu_id, $SysClass, $iUid = false){
        $getMenuInfoUrl = $APIUrl. "v201604/sys/api/ctrlSysMenu/GetData_SysMenuInfo";

        $SendArray = [];
        if(!$iUid){
            $SendArray["sOpId"] = $bps_menu_id;
        }else{
            $SendArray["iUid"] = $bps_menu_id;
        }
        $meunInfoResult = $SysClass->UrlDataGet( $getMenuInfoUrl, $SendArray);
        $meunInfo = $SysClass->Json2Data($meunInfoResult["result"],false);
        // print_r($APIUrl);

        return $meunInfo;
    }

    private function getBpsAllMenuInfo($APIUrl, $SysClass){
        $getMenuInfoUrl = $APIUrl. "v201604/sys/api/ctrlSysMenu/GetData_SysMenuInfo";

        $SendArray = [];
        
        $meunInfoResult = $SysClass->UrlDataGet( $getMenuInfoUrl, $SendArray);
        $meunInfo = $SysClass->Json2Data($meunInfoResult["result"],false);
        // print_r($APIUrl);

        return $meunInfo;
    }
}
