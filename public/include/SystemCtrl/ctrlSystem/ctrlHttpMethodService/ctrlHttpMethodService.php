<?php		
	namespace ctrlHttpMethodService;
	
	
	class ctrlHttpMethod {
	//創建DELETE和PUT的變數
		public function httpMethodVars() {
			// print_r($_SERVER['REQUEST_METHOD']);
			// print_r(file_get_contents('php://input'));
	        if (strlen(trim($vars = file_get_contents('php://input'))) === 0){
	            $vars = false;
	            return $vars;
	        }else{
	            $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
	            // print_r($REQUEST_METHOD);
	            parse_str($vars, $GLOBALS["_".$REQUEST_METHOD]);
	            return $GLOBALS["_".$REQUEST_METHOD];
	        }
   		}
	//創建DELETE和PUT的變數 結束
	}
?>