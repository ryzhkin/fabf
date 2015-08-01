<?php
  include_once "lib/tool.php";
  include_once "lib/serviceDo.php";

  if (tool::isAjax()) {
    $params = json_decode(file_get_contents("php://input"), true);
    //tool::xlog('ajax', $params);
    $result = array(
      'ok' => true,
    );
    if (isset($params['ajaxAction'])) {
      switch ($params['ajaxAction']) {
         case 'getTexts'    : {
            $date     = ((isset($params['date']))?$params['date']:'*');
            $page     = ((isset($params['page']))?$params['page']:1);
            $pageSize = ((isset($params['pageSize']))?$params['pageSize']:20);
            $result['page']     = $page;
            $result['pageSize'] = $pageSize;
            $texts = file_get_contents(__DIR__.'/../data/texts_bad.json');
            $texts = json_decode($texts, true);

            // Проверяем на входе период дат или нет
            $periodDate = explode('@', $date);
            if (count($periodDate) > 1) {
              $startDate  = $periodDate[0];
              $endDate    = $periodDate[1];
              if (strtotime($startDate) > strtotime($endDate)) {
                $t = $startDate;
                $startDate = $endDate;
                $endDate = $t;
              }
              $result['period']['startDate'] = $startDate;
              $result['period']['endDate']   = $endDate;
            }


            // Получаем список дат
            $dates = array();
            foreach ($texts as $t) {
              $dates[] = date("Y-m-d", strtotime($t['date_time']) );
            }
            $dates = array_values(array_unique($dates));
            //tool::xlog('dates', $dates);

            // Определяем минимальную дату и максимальную
            $minDate = $dates[0];
            $maxDate = $dates[count($dates) - 1];
            foreach ($dates as $d) {
              if (strtotime($minDate) > strtotime($d)) {
                $minDate = $d;
              }
              if (strtotime($maxDate) < strtotime($d)) {
                $maxDate = $d;
              }
            }

            // Для периода получаем только те даты которые входят в этот период
            if (!empty($result['period'])) {
              $inPeriodDates = array();
              foreach ($dates as $d) {
                if ( (strtotime($d) >= strtotime($result['period']['startDate'])) && (strtotime($d) <= strtotime($result['period']['endDate'])) ) {
                  $inPeriodDates[] = $d;
                }
              }
              $dates = $inPeriodDates;
            }


            // Применяем фильтр по дате (дата, период дат)
            $filter_texts = array();
            if ($date !== '*') {
              foreach ($texts as $t) {
                if (isset($t['date_time'])) {
                  if (!empty($result['period'])) {
                    if ( (strtotime($t['date_time']) >= strtotime($result['period']['startDate'].' 00:00:00')) && (strtotime($t['date_time']) <= strtotime($result['period']['endDate'].' 23:59:59')) ) {
                        $filter_texts[] = $t;
                    }
                  } else {
                    if ( $date == date("Y-m-d", strtotime($t['date_time'])) ) {
                       $filter_texts[] = $t;
                    }
                  }
                }
              }
            } else {
              $filter_texts = $texts;
            }
            $result['count'] =  count($filter_texts);

              // Получаем страницу данных
            $out_texts = array();
            for ($i = ($page-1)*$pageSize; ($i < $page*$pageSize) && ($i < count($filter_texts)); $i++) {
              $out_texts[] = $filter_texts[$i];
            }
            $result['texts'] = $out_texts;
            $result['dates'] = $dates;
            $result['date']  = $date;
            $result['minDate'] = $minDate;
            $result['maxDate'] = $maxDate;
            break;
          }
         case 'getTextStat' : {
            $text  = ((isset($params['text']))?$params['text']:'');
            $result['stat'] = ServiceDo::getStatisticForTexts([$text]);
            break;
         }
         case 'getPeriodTextStat' : {
            $date     = ((isset($params['date']))?$params['date']:'*');
            // Проверяем на входе период дат или нет
            $periodDate = explode('@', $date);
            if (count($periodDate) > 1) {
                 $startDate  = $periodDate[0];
                 $endDate    = $periodDate[1];
                 if (strtotime($startDate) > strtotime($endDate)) {
                     $t = $startDate;
                     $startDate = $endDate;
                     $endDate = $t;
                 }
                 $result['period']['startDate'] = $startDate;
                 $result['period']['endDate']   = $endDate;
            }

            $texts = file_get_contents(__DIR__.'/../data/texts_bad.json');
            $texts = json_decode($texts, true);

            // Применяем фильтр по дате (дата, период дат)
            $filter_texts = array();
            if ($date !== '*') {
                 foreach ($texts as $t) {
                     if (isset($t['date_time'])) {
                         if (!empty($result['period'])) {
                             if ( (strtotime($t['date_time']) >= strtotime($result['period']['startDate'].' 00:00:00')) && (strtotime($t['date_time']) <= strtotime($result['period']['endDate'].' 23:59:59')) ) {
                                 $filter_texts[] = $t;
                             }
                         } else {
                             if ( $date == date("Y-m-d", strtotime($t['date_time'])) ) {
                                 $filter_texts[] = $t;
                             }
                         }
                     }
                 }
            } else {
                $filter_texts = $texts;
            }
            $result['count'] =  count($filter_texts);
            $result['stat'] = ServiceDo::getStatisticForTexts($filter_texts, array(
              'minStatCount' => 7
            ));
            break;
         }
      }


    }
    header('Content-Type: application/json');
    echo json_encode($result);
  }
?>