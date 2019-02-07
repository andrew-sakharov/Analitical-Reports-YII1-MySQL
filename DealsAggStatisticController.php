<?php

class DealsAggStatisticController extends Controller
{  
    public function actionIndex()
    {          
             
        $time_pre = microtime(true);                          
        $def_period = 7;
                                    //  $for_id  != -1 Выводить заданного клиента по его Id
                                            
        $for_id =  Yii::app()->request->getParam('for_user', -1);       
        if ($for_id != -1) {
            $def_period = 12;                 
        }
        
        $dealsLogRepository = new DealsLogRepository();
        $count_deals = $dealsLogRepository->getCountByStatus('ok');   
        
        if (Yii::app()->request->getParam('last_date') or Yii::app()->request->getParam('first_date') or Yii::app()->request->getParam('city')
            or Yii::app()->request->getParam('manager') or Yii::app()->request->getParam('platform') or Yii::app()->request->getParam('data_list')) {}
        else {     
              $dealsAggStatisticUpdate = new DealsAggStatisticUpdate();
              $res = $dealsAggStatisticUpdate();
             
             }
  
        $data_list [0] = 'Все показатели';
        $data_list [1] = 'Объём';
        $data_list [2] = 'Доход';
        $data_list [3] = 'Такса';
        $data_list [4] = 'Процент к периоду';
        $data_list [5] = 'Доход AWB';
             
        $dealsStatisticRepository = new DealsStatisticRepository();
        $count_agg = $dealsStatisticRepository->getCount();         
               
        $distinctDealsDateListQuery = new DistinctDealsDateListQuery();
        $deal_date = $distinctDealsDateListQuery($for_id);          
        $deal_date_length = count($deal_date);   
        
        if ($deal_date_length > 0) {        
            if (Yii::app()->request->getParam('last_date')) {
                $last = Yii::app()->request->getParam('last_date');}
            else {$last = $deal_date_length - 1;}                  
            if (Yii::app()->request->getParam('first_date')) {
                $first = Yii::app()->request->getParam('first_date');}
            else {
                if ($deal_date_length > ($def_period-1)) $first = $deal_date_length - $def_period;
                else $first = 0;
            }  
            
            $j = 0;
            for ($i = 0; $i < $deal_date_length; $i++) {
                $deal_date_name = strip_tags(implode( $deal_date[$i]));
                $deal_date_name  = str_replace("(", "", $deal_date_name);
                $deal_date_name  = str_replace(")", "", $deal_date_name);
                $deal_date_full[$i] = $deal_date_name;
                if ($i >= $first and $i <= $last) {
                    $deal_date_list[$j] = $deal_date_name;
                    $j++;
                }
            }
            $deal_date_list[$i] = "Total";
                                   
            $dealsAggStatQuery = new DealsAggStatQuery();
            $dataProvider_a = $dealsAggStatQuery($for_id, $deal_date_full, $first, $last);      
        }
        
        $my_error = 'no';       
        if (isset ($dataProvider_a) and count($dataProvider_a) > 0) {
                $j = 0; 
                while ($j < count($dataProvider_a)) {                   
                    $platform_name = $dataProvider_a[$j]['platform'];
                    $city = $dataProvider_a[$j]['city'];
                    $manager = $dataProvider_a[$j]['manager'];  
                    $platform_name = $dataProvider_a[$j]['platform'];
                    $promised_limit = $dataProvider_a[$j]['promised_limit'];
                    $buyer_name = $dataProvider_a[$j]['buyer_name'];
                    $user_marker_n [$buyer_name] = $dataProvider_a[$j]['user_marker_n'];                  
                    if (isset($user_platform_limit [$buyer_name])) {
                        if (strpos($user_platform_limit [$buyer_name], $platform_name) === FALSE) {
                            $user_platform_limit [$buyer_name] = $user_platform_limit [$buyer_name] . ', ' . $platform_name . ' (' . $promised_limit . ') ';
                        }
                    }
                    else if (!isset($user_platform_limit [$buyer_name])) {
                        $user_platform_limit [$buyer_name] = $platform_name . ' (' . $promised_limit . ')';
                    }                    
                    $platform_name_array[$platform_name] = 1;
                    $city_u [$city] = 1;
                    $manager_u [$manager] = 1;                         
                    $j++;
                }
                $manager = array_keys($manager_u);
                asort($manager);
                array_unshift($manager, 'Все менеджеры');                 
                $city = array_keys($city_u);
                asort($city);
                array_unshift($city, 'Все регионы');                   
                $platform_list = array_keys($platform_name_array);
                array_unshift($platform_list, 'Все платформы'); 
        }
        else {
            $my_error = 'error2'; // У Пользователя нет Сделок
            $dataProvider_a = '';
            $deal_date_list = '';
            $deal_date_full = '';
            $platform_list = '';
            $data_list = '';
            $manager = '';
            $city = '';
            $first = '';
            $last = '';
            $count_deals = '';
            $count_agg = '';
            $buyer_stat = '';
            $user_platform_limit = '';
            $user_marker_n = '';
        }
                                                                               // Выборка по платформе
        if (Yii::app()->request->getParam('platform')) {
            $i = 0; $j = 0;
            $data_length = count($dataProvider_a);
            while ($i < $data_length) {                                         //Цикл по массиву данных
                if ($dataProvider_a[$i]['platform'] == $platform_list[Yii::app()->request->getParam('platform')]) {
                    $dataProvider_an[$j] = $dataProvider_a[$i];
                    $j++;
                }
                $i++;
            }
            if (isset ($dataProvider_an[0])) {
                $dataProvider_a = '';
                $dataProvider_a = $dataProvider_an;
                $dataProvider_an = '';
            }
            else {
                if ($my_error != 'error2') $my_error = 'error1';
            }
        }       
                                                                                // Выборка по городу
        if (Yii::app()->request->getParam('city')) {            
            $city_my = $city [Yii::app()->request->getParam('city')];                     
            $i = 0; $j = 0;
            $data_length = count($dataProvider_a);
            while ($i < $data_length) { 
                if ($dataProvider_a[$i]['city'] == $city_my) {
                    $dataProvider_an[$j] = $dataProvider_a[$i];
                    $j++;
                }
                $i++;
            }
            if (isset ($dataProvider_an[0]))  {
                $dataProvider_a = '';
                $dataProvider_a = $dataProvider_an;
                $dataProvider_an = '';
            }
            else {
                if ($my_error != 'error2') $my_error = 'error1';
            }
        }        
                                                                               // Выборка по менеджеру
        if (Yii::app()->request->getParam('manager')) {
            $manager_my = $manager [Yii::app()->request->getParam('manager')];                       
            $i = 0; $j = 0;
            $data_length = count($dataProvider_a);
            
            while ($i < $data_length) { 
                if ($dataProvider_a[$i]['manager'] == $manager_my) {
                    $dataProvider_an[$j] = $dataProvider_a[$i];
                    $j++;
                }
                $i++;
            }    
            if (isset ($dataProvider_an[0])) {
                $dataProvider_a = '';
                $dataProvider_a = $dataProvider_an;
                $dataProvider_an = '';
            }
            else {
                if ($my_error != 'error2') $my_error = 'error1';
            }
        }         
                                                                            // Экспорт в Excel
        if (Yii::app()->request->getParam('yt1')) {             
            $pExcel = new PHPExcel();          
            
//            **************** Если нужна запись в существующий файл Excel- раскоментировать            
//            $objReader = new PHPExcel_Reader_Excel2007();
//            $pExcel = $objReader->load(Yii::app()->getBasePath() . '/../protected/modules/admin/views/dealsAggStatistic/data/DealsStat.xlsx');
           
            $pExcel->setActiveSheetIndex(0);
            $aSheet = $pExcel->getActiveSheet();
            
          
            $aSheet->getColumnDimension('A')->setWidth(10);
            $aSheet->getColumnDimension('B')->setWidth(20);
            $aSheet->getColumnDimension('C')->setWidth(30);
            $aSheet->getColumnDimension('D')->setWidth(30);
            $aSheet->getColumnDimension('E')->setWidth(30);
            $aSheet->getColumnDimension('F')->setWidth(20);
            $aSheet->getColumnDimension('G')->setWidth(15);
            $aSheet->getColumnDimension('H')->setWidth(15);
            $aSheet->getColumnDimension('I')->setWidth(15);               
            $aSheet->getColumnDimension('J')->setWidth(15);
            $aSheet->getColumnDimension('K')->setWidth(15);
            $aSheet->getColumnDimension('L')->setWidth(60);
                        
            $aSheet->setCellValue('A1', 'ID');
            $aSheet->setCellValue('B1', 'Platform');
            $aSheet->setCellValue('C1', 'Buyer Region');
            $aSheet->setCellValue('D1', 'Buyer Manager');
            $aSheet->setCellValue('E1', 'Deals Date');
            $aSheet->setCellValue('F1', 'Value');
            $aSheet->setCellValue('G1', 'Tax');
            $aSheet->setCellValue('H1', 'Percent'); 
            $aSheet->setCellValue('I1', 'Promised Limit');
            $aSheet->setCellValue('J1', 'AWB Profit');
            $aSheet->setCellValue('K1', 'AWB Value');
            $aSheet->setCellValue('L1', 'Marker');
           
            $i=0;
            $data_length = count($dataProvider_a);
          
            while ($i < $data_length) { //Цикл по массиву данных     
                $buyer_name = $dataProvider_a [$i]['buyer_name'];
                $aSheet->setCellValue('A'.($i+2), $dataProvider_a [$i]['buyer_name']);
                $aSheet->setCellValue('B'.($i+2), $dataProvider_a [$i]['platform']);                    
                $aSheet->setCellValue('C'.($i+2), $dataProvider_a [$i]['city']);
                $aSheet->setCellValue('D'.($i+2), $dataProvider_a [$i]['manager']);
                $aSheet->setCellValue('E'.($i+2), $dataProvider_a [$i]['deal_date']);
                $aSheet->setCellValue('F'.($i+2), $dataProvider_a [$i]['total_value']);
                $aSheet->setCellValue('G'.($i+2), $dataProvider_a [$i]['bfl_profit_byers']);
                $aSheet->setCellValue('H'.($i+2), $dataProvider_a [$i]['avg_procent']);              
                $aSheet->setCellValue('I'.($i+2), $dataProvider_a[$i]['promised_limit'] );
                $aSheet->setCellValue('J'.($i+2), $dataProvider_a[$i]['awb']);
                $aSheet->setCellValue('K'.($i+2), $dataProvider_a[$i]['awb_tax']);
                $aSheet->setCellValue('L'.($i+2), $user_marker_n [$buyer_name]);
                               
                $i++;
            }
            
            $objWriter = new PHPExcel_Writer_Excel2007($pExcel);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: filename="' . 'DealsStat.xlsx'. '.xlsx"');
            $objWriter->save('php://output');
            
            Yii::app()->end();
        }    
        
        $this->render('index',array(
            'dataProvider'=>$dataProvider_a,
            'deal_date_list' => $deal_date_list,
            'deal_date_full' => $deal_date_full,
            'platform' => $platform_list,
            'data_list' => $data_list,
            'manager' => $manager,
            'city' => $city,
            'first' => $first,
            'last' => $last,
            'count_deals' => $count_deals,
            'count_agg' => $count_agg,

            'my_error' => $my_error,
            'time_pre' => $time_pre,
            'user_platform_limit'=> $user_platform_limit,
            'user_marker_n' => $user_marker_n,
            'for_id' => $for_id,
            )
            );       
    }
}

?>