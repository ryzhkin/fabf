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
         case 'getTexts'   : {
            $date     = ((isset($params['date']))?$params['date']:'*');
            $page     = ((isset($params['page']))?$params['page']:1);
            $pageSize = ((isset($params['pageSize']))?$params['pageSize']:20);
            $result['page']     = $page;
            $result['pageSize'] = $pageSize;
            $texts = file_get_contents(__DIR__.'/../data/texts_bad.json');
            $texts = json_decode($texts, true);


            // Получаем список дат
            $dates = array();
            foreach ($texts as $t) {
              $dates[] = date("Y-m-d", strtotime($t['date_time']) );
            }
            $dates = array_values(array_unique($dates));
            //tool::xlog('dates', $dates);

            $filter_texts = array();
            if ($date !== '*') {
              foreach ($texts as $t) {
                if (isset($t['date_time']) && ($date == date("Y-m-d", strtotime($t['date_time'])) ) ) {
                  $filter_texts[] = $t;
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
            $result['texts'] =  $out_texts;
            $result['dates'] =  $dates;
            $result['date']  =  $date;
            break;
          }
         case 'getTextStat': {
            $text  = ((isset($params['text']))?$params['text']:'');
            $result['stat'] = ServiceDo::getStatisticForTexts([$text]);
         }
      }


    }
    header('Content-Type: application/json');
    echo json_encode($result);
  }
?>