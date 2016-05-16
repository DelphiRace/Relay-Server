<?php
	@session_start();
    $SystemCtrlPath = __DIR__ . '\\SystemCtrl\\SystemCtrl.php';
    if(!file_exists($System_APServicePath)){
        $SystemCtrlPath = __DIR__ . '/SystemCtrl/SystemCtrl.php';
    }
	include($SystemCtrlPath);
?>