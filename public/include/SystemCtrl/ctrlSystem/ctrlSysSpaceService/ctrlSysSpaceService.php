<?php		
	namespace ctrlSysSpaceService;
	
	
	class ctrlSysSpace {
		#取得API設定檔
		public function GetSysSpace($sysCode){
			$SysClass = new \ctrlToolsService\ctrlTools;
			if($sysCode != null){
				$strIniFile = dirname(__DIR__) . "\\..\\..\\..\\..\\config\\".$sysCode.".ini";
				if(!file_exists($strIniFile)){
					$strIniFile = dirname(__DIR__) . "\\..\\..\\..\\..\\config\\sysDefaultSpace.ini";
				}
			}else{
				$strIniFile = dirname(__DIR__) . "\\..\\..\\..\\..\\config\\sysDefaultSpace.ini";
			}
            //開啟ＡＰＩ設定檔
            $spaceConfig = $SysClass->GetINIInfo($strIniFile,null,"space",'',true);

            return $spaceConfig;
		}
		// 換算與回傳限制
		#取得API設定檔
		public function GetConvert($originalSpace, $originalUnit, $convertSpace, $convertUnit){
			if($originalUnit != "Byte"){
				$original = $originalSpace * $this->unitConvert("Byte");
			}
			if($convertUnit != "Byte"){
				$convert = $convertSpace * $this->unitConvert("Byte");
			}
			if($original > $convert){
				return false;
			}else{
				return true;
			}

            return $spaceConfig;
		}

		public function unitConvert($convertUnit){
			// gb > byte
			// 以最低轉換單位為MB
			// 轉換次數
			$changeTime = 5;
			switch ($convertUnit) {
				case 'Byte':
					$changeTime = $changeTime - 2;
					break;
				case 'KB':
					# code...
					$changeTime = $changeTime - 3;
					break;
				case 'MB':
					# code...
					$changeTime = $changeTime - 4;
					break;
			}
			$conver = 1024;
			for($i = 1; $i <= $changeTime; $i++){
				$conver = $conver*1024;
			}
			return $conver;
		}
	}
?>