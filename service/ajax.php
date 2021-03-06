<?php
  include_once "lib/tool.php";
  include_once "lib/serviceDo.php";

  function getTextsFromDB ($date, $page = -1, $pageSize = 20) {
      $result = array();
      $result['page']     = $page;
      $result['pageSize'] = $pageSize;
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
      $sql = "
              SELECT
                DATE_FORMAT(texts.text_date_time, '%Y-%m-%d') as d
              FROM texts
              GROUP BY d
              ORDER BY d DESC
            ";
      $rows = tool::runSQL($sql);
      //tool::xlog('sql', $rows);
      foreach ($rows as $r) {
          $dates[] = $r['d'];
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

      // Определяем минимальную дату и максимальную
      $minDate = $dates[count($dates) - 1];
      $maxDate = $dates[0];

      $result['dates']   = $dates;
      $result['date']    = $date;
      $result['minDate'] = $minDate;
      $result['maxDate'] = $maxDate;

      // Получаем страницу данных
      $out_texts = array();
      $period = "";
      if ($date !== '*') {
          if (!empty($result['period'])) {
              $period = "WHERE texts.text_date_time >= '".($result['period']['startDate'].' 00:00:00')."' AND texts.text_date_time <= '".($result['period']['endDate'].' 23:59:59')."'";
          } else {
              $period = "WHERE DATE_FORMAT(texts.text_date_time, '%Y-%m-%d') = '{$date}'";
          }
      }
      $limit = "";
      if ($page > 0 && $pageSize > 0) {
        $limit = "LIMIT ".($pageSize*($page-1)).", ".$pageSize;
      }
      $sql = "
              SELECT SQL_CALC_FOUND_ROWS
               *,
               texts.text_date_time as date_time
              FROM texts
              {$period}
              ORDER BY texts.text_date_time DESC
              {$limit}
            ";
      $rows = tool::runSQL($sql);
      foreach ($rows as $r) {
         $out_texts[] = $r;
      }
      $result['texts'] = $out_texts;
      //tool::xlog('texts', $out_texts);
      $count = tool::runSQL("SELECT FOUND_ROWS() as c;");
      $count = $count[0]['c'];
      $result['count'] = $count;

      return $result;
  }

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
/*
            $result['page']     = $page;
            $result['pageSize'] = $pageSize;

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
            $sql = "
              SELECT
                DATE_FORMAT(texts.text_date_time, '%Y-%m-%d') as d
              FROM texts
              GROUP BY d
              ORDER BY d DESC
            ";
            $rows = tool::runSQL($sql);
            //tool::xlog('sql', $rows);
            foreach ($rows as $r) {
              $dates[] = $r['d'];
            }


            // Определяем минимальную дату и максимальную
            $minDate = $dates[count($dates) - 1];
            $maxDate = $dates[0];

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


            // Получаем страницу данных
            $out_texts = array();
            $period = "";
            if ($date !== '*') {
                 if (!empty($result['period'])) {
                     $period = "WHERE texts.text_date_time >= '".($result['period']['startDate'].' 00:00:00')."' AND texts.text_date_time <= '".($result['period']['endDate'].' 23:59:59')."'";
                 } else {
                     $period = "WHERE DATE_FORMAT(texts.text_date_time, '%Y-%m-%d') = '{$date}'";
                 }
            }

            $sql = "
              SELECT SQL_CALC_FOUND_ROWS
               *,
               texts.text_date_time as date_time
              FROM texts
              {$period}
              ORDER BY texts.text_date_time DESC
              LIMIT ".($pageSize*($page-1)).", {$pageSize}
            ";
            $rows = tool::runSQL($sql);
            foreach ($rows as $r) {
              $out_texts[] = $r;
            }
            //tool::xlog('texts', $out_texts);
            $count = tool::runSQL("SELECT FOUND_ROWS() as c;");
            $count = $count[0]['c'];
            $result['count'] = $count;
            //*/

          /*
            $texts = file_get_contents(__DIR__.'/../data/texts_bad.json');
            $texts = json_decode($texts, true);


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
            //*/


            /*$result['texts'] = $out_texts;
            $result['dates'] = $dates;
            $result['date']  = $date;
            $result['minDate'] = $minDate;
            $result['maxDate'] = $maxDate;*/


            $result = getTextsFromDB($date, $page, $pageSize);
            break;
          }
         case 'getTextStat' : {
            $text  = ((isset($params['text']))?$params['text']:'');
            $result['stat'] = ServiceDo::getStatisticForTexts([$text]);
            foreach ($result['stat']['ru'] as $word => &$info) {
              $search = tool::runSQL("SELECT * FROM words WHERE word = :word", array(
                ':word' => $word
              ));
              $info['db'] = $search[0];
              /*if (count($search) > 0) {
                $info['db'] = $search[0];
              } else {
                $info['db'] = 'false';
              }*/
              $info['word'] = $word;
            }
            break;
         }
         case 'getPeriodTextStat' : {
            $date     = ((isset($params['date']))?$params['date']:'*');
            /*
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
            //*/

            $result = getTextsFromDB($date);
            $result['stat'] = ServiceDo::getStatisticForTexts($result['texts'], array(
              'minStatCount'   => 7,
              'maxCountWords'  => 100,
            ));
            break;
         }
         case 'loadTexts': {
            ServiceDo::getTextsToDB(30);
            break;
         }
      }


    }
    header('Content-Type: application/json');
    echo json_encode($result);
  }
?>