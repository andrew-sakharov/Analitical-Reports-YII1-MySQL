<?php


//              $for_id < 0 вывод информации по списку клиентов
//              $for_id > 0 идентификатор конкретного клиента

if ($my_error == 'error2') : ?>
<br>******************************************************************************
<br>******************************************************************************
<br>У Клиента нет сделок
<br>******************************************************************************
<br>******************************************************************************

<?php 
else :
    
    
$kol_period = count($deal_date_list);
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'Revenue',
    'method' => 'POST',
    'enableAjaxValidation' => false,
));

if (!isset ($_POST['platform'])) $platform_s = 0;
else $platform_s = $_POST['platform'];

if (!isset ($_POST['manager'])) $manager_s = 0;
else $manager_s = $_POST['manager'];

echo '<table  border="0" CELLSPACING=2 CELLPADDING=10>'; 
echo '<tr><td>';

$data_list_a[0] = '0';
if (isset ($_POST['data_list'])) {
    $data_list_a = $_POST['data_list'];
    $data_list_s = $_POST['data_list'];
}
else {$data_list_s = 0;}
echo CHtml::checkBoxList('data_list',  $data_list_s, $data_list);
echo '</td>';


 echo '<td valign="top">';
 echo CHtml::dropDownList('platform', $platform_s, $platform);
 
 if (!isset ($_POST['city'])) $city_s = 0;
 else $city_s = $_POST['city'];
 echo '<br>';
 if ($for_id  < 0) echo CHtml::dropDownList('city', $city_s, $city);
 
 echo '<br>';
 if ($for_id < 0) echo CHtml::dropDownList('manager', $manager_s, $manager);
 echo '</td>';
 
echo '<td valign="top" align = "left">';

$all_date = $deal_date_full;
$i = 0;

foreach ($all_date as $value) {
    $all_date[$i] = substr($value,0,4) . '.'. substr($value,4,2);
    $i++;
}

echo 'First period ';
echo CHtml::dropDownList('first_date', $first, $all_date);
echo '<br>';
echo ' Last period  ';
echo CHtml::dropDownList('last_date', $last, $all_date);
echo '<br>';

echo '</td><td></td>';
echo '<td valign="top" align = "right">';
echo CHtml::submitButton('Применить' ,array('id'=>'filters-apply',)); 

echo '<td valign="top" align = "right">';
echo CHtml::submitButton('Получить в  Excel' ,array('id'=>'excel',)); 

echo '</td><td></td>';

echo '</td><td></td><td  valign="top">';

if ($for_id < 0) : ?>
    <ul>
    <li>В базе <?php echo number_format($count_deals, 0, ',', ' ' ) ?> детальных записей по сделкам </li>
    <li>После агрегации по месяцам получено <?php echo  number_format($count_agg, 0, ',', ' ' ) ?>  записей </li>
    </ul>
<?php 
endif;
echo '</td>';
echo '</tr>';

echo '</div>';

echo '</table>';
echo '<br>';echo '<br>';
$this->endWidget();

$data_length = count($dataProvider);
$round_count = 0;
$current_total = 0;
$current_output_line = "";
$current_buyer = NULL;
$current_platform = NULL;
$awb_tax = 0;

$nom = 0;

if ($my_error == 'error1') : ?>
<br>******************************************************************************
<br>******************************************************************************
<br>В заданный диапазон не попало ни одного значения
<br>Уточните запрос.
<br>******************************************************************************
<br>******************************************************************************
<?php 
endif;

if ($my_error != 'error1') {
while ($nom < $data_length) { //Основной цикл по массиву данных по Сделкам ********************************
    if (is_null($current_buyer)) {  
                                                               // Иинмциализация
        $buyer_name = $dataProvider[$nom]['buyer_name'];        // Печать шапки Таблицы
        $current_buyer = $buyer_name;
        $current_buyer_nom = 1;

        echo '<br>' . $current_buyer_nom . '. Marker: <b>' . $user_marker_n[$buyer_name] . 
        '</b> manager: <b>' . $dataProvider[$nom]['manager'] . '</b> city: <b>' . $dataProvider[$nom]['city']  . '</b>'
            . ' [ <i>promised limit: ' . $user_platform_limit[$buyer_name]. '</i>]<br><br>';
      
        echo '<table class="deals_agg" cellspacing="1" cellpadding="1">';
        $title = '<tr><th>Platform</th><th>Data</th>';      
        for ($p = 0; $p < count($deal_date_list)-1; $p++) {
            $period = $deal_date_list[$p];
            $period_p = substr($period,0,4) . '.'. substr($period,4,2);
            $title = $title . "<th>$period_p</th>";
        }
        $title = $title . '<th>' . 'SubTotal' . '</th></tr>';
        echo $title;        
        $platform_name = $dataProvider[$nom]['platform'];
        $current_platform = $platform_name;
        $current_period = 0;   
        $str_total_value = "<tr><td>$current_platform</td><td>Value</td>";
        
        $str_bfl_profit_byers = "<tr><td></td><td>Profit</td>";
        $str_bfl_awb_byers = "<tr><td></td><td>AWB Profit</td>";
       
        if (in_array('2', $data_list_a, TRUE)) $str_bfl_profit_byers = "<tr><td>$current_platform</td><td>Profit</td>";
        $str_awb_procent =  "<tr><td></td><td>AWB %</td>"; 
        if (in_array('5', $data_list_a, TRUE)) {
            $str_bfl_awb_byers = "<tr><td>$current_platform</td><td>AWB Profit</td>";
            $str_awb_procent =          "<tr><td></td><td>AWB %</td>";
        }
        $str_avg_procent =  "<tr><td></td><td>Value %</td>";        
        if (in_array('3', $data_list_a, TRUE)) $str_avg_procent = "<tr><td>$current_platform</td><td>Value %</td>";
        $str_last_procent = "<tr><td></td><td>Last %</td>";
        if (in_array('4', $data_list_a, TRUE)) $str_last_procent = "<tr><td>$current_platform</td><td>Last %</td>";  
        $total_total_value = 0;
        
        $key = 0;
        while ($key < count ($deal_date_list)){
            
            $sum_total_value [$key] = 0;
            $sum_profit_byers [$key] = 0;
            $sum_awb_byers [$key] = 0;
            $key++;
        }
        
        $total_profit_byers= 0;
        $total_awb_byers= 0;
    }
    $period =  $dataProvider[$nom]['deal_date'];
         
    while ($current_period < $kol_period-1) {
                
        $total_value = $dataProvider[$nom]['total_value'];
        $bfl_profit_byers = $dataProvider[$nom]['bfl_profit_byers'];
        $bfl_awb_byers = $dataProvider[$nom]['awb'];
        $awb_procent = round($dataProvider[$nom]['awb_procent'],0) . '%';
        $avg_procent = $dataProvider[$nom]['avg_procent']*100 . '%';
        $awb_tax = $dataProvider[$nom]['awb_tax'];
        if ($current_period != 0) {
            if ($dataProvider[$nom-1]['total_value'] == 0) {
                $last_procent = '0%';                
            }
            else {
            $last_procent = round(($dataProvider[$nom]['total_value']/$dataProvider[$nom-1]['total_value'])*100, 0) . '%';
            }
            }
        else  {$last_procent = '0%';}
        $key=0;                             // Найти позицию Периода в массиве перидов - $key
        while ($key < count ($deal_date_list)){
            if ($period == $deal_date_list [$key])  break;
            $key++;
        }   
          while ($current_period < $key){   // Заполнить отсутсвующие периоды прочерками
            $str_total_value = $str_total_value .           "<td align = \"right\">-</td>";            
            $str_bfl_profit_byers = $str_bfl_profit_byers . "<td align = \"right\">-</td>";
            $str_bfl_awb_byers = $str_bfl_awb_byers .       "<td align = \"right\">-</td>";
            $str_awb_procent = $str_awb_procent .           "<td align = \"right\">-</td>";
            $str_avg_procent = $str_avg_procent .           "<td align = \"right\">-</td>";
            $str_last_procent = $str_last_procent .         "<td align = \"right\">-</td>";
            $current_period++;
        }    
        $total_value_pr = number_format( $total_value, 0, ',', ' ' );
        $str_total_value = $str_total_value .                           "<td align = \"right\">$total_value_pr</td>";
        $total_total_value = $total_total_value + $total_value;          
        
        $sum_total_value[$current_period] = $sum_total_value[$current_period] + $total_value;    
        
        $bfl_profit_byers_pr = number_format( $bfl_profit_byers, 0, ',', ' ' );
        $str_bfl_profit_byers = $str_bfl_profit_byers .                 "<td align = \"right\">$bfl_profit_byers_pr</td>";
        $total_profit_byers = $total_profit_byers + $bfl_profit_byers;
        
        $bfl_awb_byers_pr = number_format( $bfl_awb_byers, 0, ',', ' ' );
        $str_bfl_awb_byers = $str_bfl_awb_byers .                 "<td align = \"right\">$bfl_awb_byers_pr</td>";
        $total_awb_byers = $total_awb_byers + $bfl_awb_byers;
        
        $sum_profit_byers[$current_period] =    $sum_profit_byers[$current_period] +  $bfl_profit_byers;   
        
        $sum_awb_byers[$current_period] =       $sum_awb_byers[$current_period] +  $bfl_awb_byers;  
        
        $str_awb_procent = $str_awb_procent .                           "<td align = \"right\">$awb_procent</td>";
        $str_avg_procent = $str_avg_procent .                           "<td align = \"right\">$avg_procent</td>";
        $str_last_procent = $str_last_procent .                         "<td align = \"right\">$last_procent</td>";   
        
        
        if (!isset ($bfl_data [$current_period]['value'])) {
            $bfl_data [$current_period]['value'] = $total_value;
            $bfl_data [$current_period]['profit'] = $bfl_profit_byers;
            $bfl_data [$current_period]['awb'] = $bfl_awb_byers;
            
            
            
            $bfl_data [$current_period]['awb_tax'] = $awb_tax;
        }
        else {
            $bfl_data [$current_period]['value'] =      $bfl_data [$current_period]['value'] + $total_value;
            $bfl_data [$current_period]['profit'] =     $bfl_data [$current_period]['profit']  + $bfl_profit_byers;
            
            $bfl_data [$current_period]['awb'] =        $bfl_data [$current_period]['awb']  + $bfl_awb_byers;
            $bfl_data [$current_period]['awb_tax'] =    $bfl_data [$current_period]['awb_tax'] + $awb_tax;
        }    
        $current_period++;
        $nom++;                                           //Взять следующую строку
          if ($nom >= $data_length) break;                // Просмотрены все строки - Выйти
          if ($current_platform != $dataProvider[$nom]['platform'] or $current_buyer != $dataProvider[$nom]['buyer_name']) break; 
        $period =  $dataProvider[$nom]['deal_date'];
    }      
                                                    // При необходимости Вывести пустые ячейки в Строках
    while ($current_period != count ($deal_date_list)-1){
        $str_total_value = $str_total_value .           "<td align = \"right\">-</td>";
        $str_bfl_profit_byers = $str_bfl_profit_byers.  "<td align = \"right\">-</td>";
        $str_bfl_awb_byers = $str_bfl_awb_byers.        "<td align = \"right\">-</td>";
        $str_awb_procent = $str_awb_procent .           "<td align = \"right\">-</td>";
        $str_avg_procent = $str_avg_procent .           "<td align = \"right\">-</td>";
        $str_last_procent = $str_last_procent .         "<td align = \"right\">-</td>";
        $current_period++;
    }
    
    $total_total_value_pr = number_format( $total_total_value, 0, ',', ' ' );
    $str_total_value = $str_total_value .                                       "<td align = \"right\">$total_total_value_pr</td></tr>";
    $total_profit_byers_pr = number_format( $total_profit_byers, 0, ',', ' ' );   
    $str_bfl_profit_byers = $str_bfl_profit_byers.                              "<td align = \"right\">$total_profit_byers_pr</td></tr>";
    
    $total_awb_byers_pr = number_format( $total_awb_byers, 0, ',', ' ' );
    $str_bfl_awb_byers = $str_bfl_awb_byers.                              "<td align = \"right\">$total_awb_byers_pr</td></tr>";
    
    $str_awb_procent = $str_awb_procent .                                       "<td align = \"right\"></td></tr>";
    $str_avg_procent = $str_avg_procent .                                       "<td align = \"right\"></td></tr>";
    $str_last_procent = $str_last_procent .                                     "<td align = \"right\"></td></tr>";

    if (in_array('0', $data_list_a, TRUE)) {
        echo $str_total_value;
        echo $str_bfl_profit_byers;
        echo $str_bfl_awb_byers;
        echo $str_awb_procent;
        echo $str_avg_procent;
        echo $str_last_procent;
    }
    else  {
        if (in_array('1', $data_list_a, TRUE)) {echo $str_total_value;}
        if (in_array('2', $data_list_a, TRUE)) {echo $str_bfl_profit_byers;}
        if (in_array('5', $data_list_a, TRUE)) {echo $str_bfl_awb_byers; echo $str_awb_procent;}
        if (in_array('3', $data_list_a, TRUE)) {echo $str_avg_procent;}
        if (in_array('4', $data_list_a, TRUE)) {echo $str_last_procent;}
    }
    $current_period = 0;
                                                        //  Новая платформа ? Новый Покупатель? Конец массива?
    if ($nom >= $data_length)  break;                   //  Просмотрен весь Массив   
    $buyer_name = $dataProvider[$nom]['buyer_name'];
    $platform_name = $dataProvider[$nom]['platform'];     
    if ($current_platform != $platform_name) {          // Новая платформа       
        $current_platform = $platform_name;
        $str_total_value =          "<tr><td>$current_platform</td><td>Value</td>";
        $str_bfl_profit_byers =     "<tr><td></td><td>Profit</td>";
        $str_bfl_awb_byers =        "<tr><td></td><td>AWB Profit</td>";
        $str_awb_procent =          "<tr><td></td><td>AWB %</td>";
        $str_avg_procent =          "<tr><td></td><td>Value %</td>";
        $str_last_procent =         "<tr><td></td><td>Last %</td>";             
            if (in_array('1', $data_list_a, TRUE))                  $str_total_value = "<tr><td>$current_platform</td><td>Value</td>";

            $str_bfl_profit_byers = "<tr><td></td><td>Profit</td>";
            if (in_array('2', $data_list_a, TRUE))                  $str_bfl_profit_byers = "<tr><td>$current_platform</td><td>Profit</td>";

            $str_bfl_awb_byers = "<tr><td></td><td>AWB Profit</td>";
            if (in_array('5', $data_list_a, TRUE))  {                
                $str_bfl_awb_byers = "<tr><td>$current_platform</td><td>AWB Profit</td>";
                $str_awb_procent =          "<tr><td></td><td>AWB %</td>";
            }
                       
            $str_avg_procent =  "<tr><td></td><td>Value %</td>";
            if (in_array('3', $data_list_a, TRUE))                  $str_avg_procent =  "<tr><td>$current_platform</td><td>Value %</td>";
            $str_last_procent = "<tr><td></td><td>Last %</td>";
            if (in_array('4', $data_list_a, TRUE))                  $str_last_procent = "<tr><td>$current_platform</td><td>Last %</td>";     
        $total_profit_byers= 0;
        $total_awb_byers= 0;
        $total_total_value = 0;
    }                                                       // Новый Покупатель     
    if  ($current_buyer != $buyer_name) {   
    $total_total_value = 0;
    $total_profit_byers= 0;
    $total_awb_byers= 0;
    
    if ($platform_s == 0) {
        $subt_value =  "<tr><td><i>Total</i></td><td><i>Value</i></td>";
        $subt_profit = "<tr><td><i>Total</i></td><td><i>Profit</i></td>";
        $subt_awb =    "<tr><td><i>Total</i></td><td><i>AWB Profit</i></td>";
        
        $subt_value_p = 0;
        $sum_profit_byers_p = 0;
        $sum_awb_byers_p = 0;
      
        $t = 0;
        while ($t < count ($deal_date_list)-1) {
            if ($sum_total_value[$t] > 0) {
                $sum_total_value_pr = number_format($sum_total_value [$t], 0, ',', ' ' );
                $subt_value_p = $subt_value_p + $sum_total_value [$t];
                
                $sum_profit_byers_pr = number_format($sum_profit_byers [$t], 0, ',', ' ' );
                $sum_profit_byers_p = $sum_profit_byers_p + $sum_profit_byers [$t];
            }
            else {
                $sum_total_value_pr = '-';
                $sum_profit_byers_pr = '-';
            }
            $subt_value = $subt_value .    "<td align = \"right\">$sum_total_value_pr</td>";
            $subt_profit = $subt_profit .  "<td align = \"right\">$sum_profit_byers_pr</td>";
            $t++;
        }
        
        $t = 0;
            while ($t < count ($deal_date_list)-1) {

                if ($sum_awb_byers [$t] != 0) {
                    $sum_awb_byers_pr = number_format($sum_awb_byers [$t], 0, ',', ' ' );
                    $sum_awb_byers_p = $sum_awb_byers_p + $sum_awb_byers [$t];
                }
                else {
                    $sum_awb_byers_pr = '-';
                }                      
            $subt_awb = $subt_awb .        "<td align = \"right\">$sum_awb_byers_pr</td>";
            $t++;
        }
        
        
        $sum_total_value_pr = number_format($subt_value_p, 0, ',', ' ' );
        $sum_profit_byers_pr = number_format($sum_profit_byers_p, 0, ',', ' ' );
        $sum_awb_byers_pr = number_format($sum_awb_byers_p, 0, ',', ' ' );
        
        
        $subt_value = $subt_value .     "<td align = \"right\">$sum_total_value_pr</td></tr>";
        $subt_profit = $subt_profit .   "<td align = \"right\">$sum_profit_byers_pr</td></tr>";
        $subt_awb = $subt_awb .         "<td align = \"right\">$sum_awb_byers_pr</td></tr>";
        echo $subt_value;
        echo $subt_profit;
        echo $subt_awb;
    }
        
                                        // Завершена печать по Покупателю *************************************************       
        echo "</table>";
        echo '<br>';      
        $current_buyer = $buyer_name;
        $current_buyer_nom++;

        echo '<br>' . $current_buyer_nom . '. Marker: <b>' . $user_marker_n[$buyer_name] .
        '</b> manager: <b>' . $dataProvider[$nom]['manager'] . '</b> city: <b>' . $dataProvider[$nom]['city']  . '</b>'
            . ' [ <i>promised limit: ' . $user_platform_limit[$buyer_name]. '</i>]<br><br>';
                   
        echo '<table class="deals_agg" cellspacing="1" cellpadding="1">';
        $title = '<tr><th>Platform</th><th>Data</th>';
        for ($p = 0; $p < count($deal_date_list)-1; $p++) {
            $period = $deal_date_list[$p];
            $period_p = substr($period,0,4) . '.'. substr($period,4,2);
            $title = $title . "<th> $period_p </th>";
        }
        $title = $title . '<th>' . 'SubTotal' . '</th></tr>';
        echo $title;
        $key = 0;
        while ($key < count ($deal_date_list)){
            $sum_total_value [$key] = 0;
            $sum_profit_byers [$key] = 0;
            $sum_awb_byers [$key] = 0;
            $key++;
        }

    $str_total_value = "<tr><td>$current_platform</td><td>Value</td>";
    $str_bfl_profit_byers =                                                 "<tr><td></td><td>Profit</td>";
    
    $str_bfl_awb_byers =                                                 "<tr><td></td><td>AWB Profit</td>";
    
    if (in_array('2', $data_list_a, TRUE))
        $str_bfl_profit_byers = "<tr><td>$current_platform</td><td>Profit</td>";

    $str_awb_procent =                                                      "<tr><td></td><td>AWB %</td>";
    if (in_array('5', $data_list_a, TRUE)) {           
        $str_bfl_awb_byers = "<tr><td>$current_platform</td><td>AWB Profit</td>";  
        $str_awb_procent =   "<tr><td></td><td>AWB %</td>";
    }
        
    $str_avg_procent =                                                      "<tr><td></td><td>Value %</td>";
    if (in_array('3', $data_list_a, TRUE))
        $str_avg_procent = "<tr><td>$current_platform</td><td>Value %</td>";
    
    $str_last_procent =                                                     "<tr><td></td><td>Last %</td>";
    if (in_array('4', $data_list_a, TRUE)) {
        $str_last_procent = "<tr><td>$current_platform</td><td>Last %</td>";
        }
    $total_total_value = 0;
    $total_profit_byers= 0;
    $total_awb_byers= 0;
    }
}

$subt_value =  "<tr><td><i>Total</i></td><td><i>Value</i></td>";
$subt_profit = "<tr><td><i>Total</i></td><td><i>Profit</i></td>";
$subt_awb =    "<tr><td><i>Total</i></td><td><i>AWB Profit</i></td>";

$subt_value_p = 0;
$sum_profit_byers_p = 0;
$sum_awb_byers_p = 0;

$t = 0;
while ($t < count ($deal_date_list)-1) {
    if ($sum_total_value[$t] > 0) {
        $sum_total_value_pr = number_format($sum_total_value [$t], 0, ',', ' ' );
        $subt_value_p = $subt_value_p + $sum_total_value [$t];
        $sum_profit_byers_pr = number_format($sum_profit_byers [$t], 0, ',', ' ' );
        $sum_profit_byers_p = $sum_profit_byers_p + $sum_profit_byers [$t];
    }
    else {
        $sum_total_value_pr = '-';
        $sum_profit_byers_pr = '-';
    }
    $subt_value = $subt_value .    "<td align = \"right\">$sum_total_value_pr</td>";
    $subt_profit = $subt_profit .  "<td align = \"right\">$sum_profit_byers_pr</td>";
    $t++;
}

$t = 0;
while ($t < count ($deal_date_list)-1) {
    if ($sum_awb_byers [$t] != 0) {        
        $sum_awb_byers_pr = number_format($sum_awb_byers [$t], 0, ',', ' ' );
        $sum_awb_byers_p = $sum_awb_byers_p + $sum_awb_byers [$t];
    }
    else {
        $sum_awb_byers_pr = '-';
    }
    $subt_awb = $subt_awb .        "<td align = \"right\">$sum_awb_byers_pr</td>";
    $t++;
}

$sum_total_value_pr = number_format($subt_value_p, 0, ',', ' ' );
$sum_profit_byers_pr = number_format($sum_profit_byers_p, 0, ',', ' ' );
$sum_awb_byers_pr = number_format($sum_awb_byers_p, 0, ',', ' ' );

$subt_value = $subt_value .     "<td align = \"right\">$sum_total_value_pr</td></tr>";
$subt_profit = $subt_profit .   "<td align = \"right\">$sum_profit_byers_pr</td></tr>";
$subt_awb = $subt_awb .         "<td align = \"right\">$sum_awb_byers_pr</td></tr>";
echo $subt_value;
echo $subt_profit;
echo $subt_awb;

echo "</table>";
echo '<br>';echo '<br>';echo '<br>';



if ($for_id < 0) echo '<b>All Buers (All)</b><br><br>';
else echo '<b>Total (All Platforms)</b><br><br>';
echo '<table class="deals_agg_total" cellspacing="1" cellpadding="1">';

$gr [] = '';
$title = '<tr><th>Data</th>';
for ($p = 0; $p < count($deal_date_list)-1; $p++) {
    $period = $deal_date_list[$p];
    $period_p = substr($period,0,4) . '.'. substr($period,4,2);
    $title = $title . "<th>$period_p</th>";
    $gr[$p]['period'] = $period_p; 
}
$title = $title . '<th align="right">GrandTotal</th></tr>';
echo $title;

$str_value =        "<tr><td>Value</td>";
$str_profit =       "<tr><td>Profit</td>";
$str_awb =          "<tr><td>AWB Profit</td>";
$str_awb_tax =      "<tr><td>AWB</td>";
$str_last_procent = "<tr><td>Last %</td>";

$j = count ($deal_date_list)-1;
$total_value_s = 0;
$total_profit_s = 0;
$total_awb_s = 0;
$awb_tax = 0;
$total_awb_tax_s = 0;

$x = 0;

while ($x < $j) {  
    if (!isset($bfl_data [$x]['value'])) {
        $bfl_data [$x]['value'] = 0;
    }
    $gr[$x]['value'] = $bfl_data [$x]['value']; 
    
    $total_value_s = $total_value_s + $bfl_data [$x]['value']; 
    $total_value_pr = number_format( $bfl_data [$x]['value'], 0, ',', ' ' );
    $str_value = $str_value . "<td align = \"right\">$total_value_pr</td>";   

    if (!isset($bfl_data [$x]['profit'])) {$bfl_data [$x]['profit'] = 0;}
    $total_profit_s = $total_profit_s + $bfl_data [$x]['profit'];
    $total_profit_pr = number_format( $bfl_data [$x]['profit'], 0, ',', ' ' );
    $str_profit = $str_profit . "<td align = \"right\">$total_profit_pr </td>";
    
    if (!isset($bfl_data [$x]['awb'])) {$bfl_data [$x]['awb'] = 0;}
    $total_awb_s = $total_awb_s + $bfl_data [$x]['awb'];
    $total_awb_pr = number_format( $bfl_data [$x]['awb'], 0, ',', ' ' );
    $str_awb = $str_awb . "<td align = \"right\">$total_awb_pr </td>";
    
    if (!isset($bfl_data [$x]['awb_tax'])) {$bfl_data [$x]['awb_tax'] = 0;}
    $total_awb_tax_s = $total_awb_tax_s + $bfl_data [$x]['awb_tax'];
    $total_awb_tax_pr = number_format( $bfl_data [$x]['awb_tax'], 0, ',', ' ' );
    $str_awb_tax = $str_awb_tax . "<td align = \"right\">$total_awb_tax_pr </td>";
    
    if ($x != 0 and $bfl_data [$x-1]['value'] > 0) {$last_procent = round(($bfl_data [$x]['value']/$bfl_data [$x-1]['value'])*100, 0) . '%';}
    else  {$last_procent = 0;}
    
    $str_last_procent = $str_last_procent . "<td align = \"right\">$last_procent</td>";
    $x++;
}

$total_value_pr = number_format($total_value_s, 0, ',', ' ' );
$str_value = $str_value . "<td align = \"right\">$total_value_pr</td>";
$total_profit_pr = number_format($total_profit_s, 0, ',', ' ' );
$total_awb_pr = number_format($total_awb_s, 0, ',', ' ' );
$str_profit = $str_profit . "<td align = \"right\">$total_profit_pr </td>";
$str_awb = $str_awb . "<td align = \"right\">$total_awb_pr </td>";

echo $str_value .           '</b></tr>';
echo $str_profit .          '</b></tr>';
echo $str_awb .             '</b></tr>';
echo $str_awb_tax .         '</b></tr>';
echo $str_last_procent .    '</b></tr>';

echo "</table>";
}
endif;

echo '<br>'; echo '<br>'; 
$time_post = microtime(true);
$exec_time = $time_post - $time_pre;
echo '<i>Query execution time: ' . round($exec_time,2) . ' sec</i>';
echo '<br>';echo '<br>';echo '<br>';

?>

 <script type="text/javascript" src="https://www.google.com/jsapi"></script>
 <script type="text/javascript">
 google.load("visualization", "1", {packages:["corechart"]});
 google.setOnLoadCallback(drawChart);
 
 function drawChart() {
 var data_val = google.visualization.arrayToDataTable([ 
 ['Период', 'Объём'],
 
<?php 

if (isset($gr)) {
for ($j = 0; $j < count($gr); $j++) {
    $deal_date = $gr [$j]['period'];
    $value = $gr [$j]['value'];
    echo "['".$deal_date."',".$value." ],";
}
}
else {
    $deal_date = '';
    $value = 0;
    echo "['".$deal_date."',".$value." ],";
}
 ?>
 ]);
 
 var options_val = { title: ' Объёмы по периодам ' };
 var chart_val = new google.visualization.ColumnChart(document.getElementById("columnchart"));
 chart_val.draw(data_val, options_val);
 }
 </script>
 <div id="columnchart" style="width: 910px; height: 200px;"></div>


































