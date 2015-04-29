<?php

class ZsExcelComponent extends Object {

	/**
	 * excel第一排字母名字
	 *
	 * @var array
	 */
	public $menuKey = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

	/**
	 * 导出excel
	 *
	 * @param array $excelData => array('menu', 'data'), menu为第一排菜单，data为excel下载数据
	 * menu为一列名 例如 $menu = array('username'=>'用户名')   'username'对应data数据项的username
	 * data为多维数据项 例如 $data = array( 0=>array('username'=>'admin'), 1=>array('username' =>'test')
	 * @param string $fileName
	 */
	public function save($excelData, $fileName){
			
			 set_time_limit(0);
	       	 ini_set('memory_limit', '5120M');
	       	 ob_end_clean();//使用PHPExcel导出Excel时，需要清空缓冲区，否则会导致乱码！
	       	 App::import('vendor', 'PHPExcel', array('file' =>'PHPExcel.php'));
	       	 App::import('vendor', 'PHPExcel/PHPExcel/Writer', array('file' =>'Excel2007.php'));
	
	        if ($excelData['menu']  && $excelData['data']) {
	            //创建一个excel
	            $objPHPExcel = new PHPExcel();
	            //第一排为菜单
	            $i = 0;
	            foreach($excelData['menu'] as $key=>$menuName) {
	            	$menuKey = $this->menuKey[$i] . '1';
	            	$objPHPExcel->getActiveSheet()->setCellValue($menuKey, $menuName);
	            	$i++;
	            }
	  
	            
	            //从第二排开始处理
	            $i = 2;
	            foreach($excelData['data'] as $val){
	            	$j = 0;
	            	foreach($excelData['menu'] as $key=>$menuName) {
	            		$menuKey = $this->menuKey[$j] . $i;
	            		$menuVal = isset( $val[$key] ) ? $val[$key] : '';
	                	$objPHPExcel->getActiveSheet()->setCellValue($menuKey, $menuVal);
	                	$j++;
	            	}
	                $i++;
	            }
	
	            //保存excel—2007格式
	            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	
	            //直接输出到浏览器
	            if(strpos($_SERVER["HTTP_USER_AGENT"],"Firefox"))  {
	            	//$fileName = iconv('utf-8', 'gb2312', $fileName);
	            } else {
	            	ob_end_clean(); 
	           		$fileName = urlencode($fileName);
	            }
	           	header("Content- Type: application/vnd.ms-excel; charset=gb2312");
	            header("Pragma: public");
	            header("Expires: 0");
	            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
	            header("Content-Type:application/force-download");
	            header("Content-Type:application/vnd.ms-execl");
	            header("Content-Type:application/octet-stream");
	            header("Content-Type:application/download");;
	            header("Content-Disposition:attachment;filename=".$fileName);
	            header("Content-Transfer-Encoding:binary");
	            $resout = $objWriter->save('php://output');
	            
	            exit;
	        } else {
	        	throw new Exception('excelData must array =>array(menu, data)');
	        }
	}

}
