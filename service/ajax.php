<?php
  include_once "lib/tool.php";
  if (tool::isAjax()) {
    $params = json_decode(file_get_contents("php://input"), true);
    //tool::xlog('ajax', $params);
    $result = array(
      'ok' => true,
    );
    if (isset($params['ajaxAction'])) {
      switch ($params['ajaxAction']) {
          case 'getTexts': {
            $page     = ((isset($params['page']))?$params['page']:1);
            $pageSize = ((isset($params['pageSize']))?$params['pageSize']:20);
            $result['page']     = $page;
            $result['pageSize'] = $pageSize;
            $texts = file_get_contents(__DIR__.'/../data/texts_bad.json');
            $texts = json_decode($texts, true);
            $result['count'] =  count($texts);
            $out_texts = array();
            for ($i = ($page-1)*$pageSize; $i < $page*$pageSize; $i++) {
              $out_texts[] = $texts[$i];
            }
            $result['texts'] =  $out_texts;
            break;
          }
      }


    }
    header('Content-Type: application/json');
    echo json_encode($result);
  }
?>