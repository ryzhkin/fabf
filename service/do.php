<?php
  include_once "lib/tool.php";
  include_once "lib/tmhOAuth/tmhOAuth.php";

  //tool::xlog('currentConfig', tool::$config);
  /*
   $cites = tool::runSQL("select * from cities");
   foreach ($cites as $city) {
     tool::xlog('cites', $city['name']);
   }
  */

   print_r ("Start service ....\n");
/*
   $twitter = new tmhOAuth(array(
       'consumer_key'               => 'ikyC9rp0VUPmWxxAAtezTGRKg',
       'consumer_secret'            => 'IyTw6JtSbq1blaEQs0hzUTJluTiR2K933MbI2AxdRmKI8ZfesN',
       'token'                      => '281247456-yYDyUrFpGVQDnaBJpD6XvJuQAvmqYFjxP61hssff',
       'secret'                     => 'aOnUrKNRU2RlyCSi302B4J04nYj8MHO7s9L8KLSK8ceqQ',
   ));
   $twitter->request('GET', $twitter->url('1.1/search/tweets'), array(
     'q'     => 'красивая',
     'count' => 1,
   ));
   if ($twitter->response['code'] == 200) {
     //tool::xlog('twitter', $twitter->response['response']);
     try {
       $result = json_decode($twitter->response['response'], true);
       tool::xlog('twitter', $result);
       foreach ($result['statuses'] as $item) {
         // Collecting photos if they exist
         $photos = array();
         if (isset($item['media'])) {
           foreach ($item['media'] as $media) {
              if ($media['type'] == 'photo') {
                array_push($photos, $media['media_url']);
              }
           }
         }

         tool::xlog('twitter_result', array(
           'created_at'     => $item['created_at'],
           'text'           => $item['text'],
           'source'         => $item['source'],
           'retweet_count'  => $item['retweet_count'],
           'favorite_count' => $item['favorite_count'],
           'photos'         => $photos,
         ));
       }


     } catch (Exception $e) {

     }
   }
//*/


  //tool::xlog('location', tool::getLocation());



class ServiceDo {
    //public static $test1 = "rrrr";
    public static function prepareText($str, $minWordLen = 0) {
        $str = str_replace(array("\r\n", "\r", "\n", ".", ",", ":", "'", '"', "(", ")", "[", "]", "{", "}", "?", "!"), '', $str);
        $str = mb_convert_case($str, MB_CASE_UPPER, "UTF-8");
        $words = explode(" ", str_replace("-"," ", $str));
        $en = array();
        $ru = array();
        foreach ($words as $word) {
            if (!empty($word) && $word!='') {
              if(preg_match("/[a-zA-z]/i", $word, $matches)) {
                if ($minWordLen >= 0 && strlen($word) >= $minWordLen) {
                  $en[] = $word;
                }
              } else {
                if ($minWordLen >= 0 && mb_strlen($word, 'utf-8') >= $minWordLen) {
                  $ru[] = $word;
                }
              }
            }
        }
        return array(
          'en' => $en,
          'ru' => $ru,
        );
    }

    public static function getFrequencies($text){
        // Удалим все кроме букв
        //$text = preg_replace("/[^\p{L}]/iu", "", strtolower($text));

        // Найдем параметры для расчета частоты
       // $total = strlen($text);
       // $data = count_chars($text, 0);

       /* // Ну и сам расчет
        array_walk($data, function (&$item, $key, $total){
            if ($total !== 0)
            $item = round($item/$total, 3);
        }, $total);*/
        //return $data;
        //return array_values($data);
    }

    public static function morphyText() {
      require_once('lib/phpmorphy/src/common.php');
      try {
        $morphyRU = new phpMorphy(__DIR__.'/lib/phpmorphy/dicts/ru2', 'ru_RU', array(
          'storage' => PHPMORPHY_STORAGE_FILE,
        ));

        //echo $morphyRU->getEncoding()."\n";
        //echo $morphyRU->getLocale()."\n";
        $src = "Hi my dear friend. How are you? Что же умеет эта библиотека? Для начала хотел обратить внимание что можно получить корень слова, морфологические формы слова, определение частей речи, грамматические формы. Я думаю многие разработчики понимают пользу таких преобразований, например, есть возможность улучшить поиск в своей системе, если получать корень слова и искать уже по нему. В данном случае моя задача состояла сбор анкоров в СЕО системе, учитывая морфологию слов.\n";
        $preparedText = self::prepareText($src, 4);
        $lemmas       = $morphyRU->getBaseForm($preparedText['ru']);

        //tool::clog($src);
        tool::clog($preparedText);
        tool::clog($lemmas);



        //tool::clog($morphy->getGramInfoMergeForms('БИБЛИОТЕКА'));
        //tool::clog($morphy->getGramInfoMergeForms('НЕ'));
        //tool::clog($morphy->getGramInfoMergeForms(array('БИБЛИОТЕКА', 'НЕ')));


        //tool::clog(self::getFrequencies($src));



        // http://php.net/manual/en/function.fann-create-standard.php
        //$ann = fann_create_standard(3, 256, 128, 3);

      } catch(phpMorphy_Exception $e) {
        die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
      }
    }

}




// Start point
//print_r($argv);
if (count($argv) > 1) {
  if (method_exists('ServiceDo', $argv[1])) {
    $params = array();
    for ($i = 2; $i < count($argv); $i++) {
       $params[] = $argv[$i];
    }
    call_user_func_array('ServiceDo::'.$argv[1], $params);
  } else {
    echo "Unknown method - '".$argv[1]."'\n";
  }
} else {
  $methods = get_class_methods(ServiceDo);
  echo "Methods:\n";
  foreach ($methods as $method) {
    echo $method;
    $r = new ReflectionMethod('ServiceDo', $method);
    $params = $r->getParameters();
    if (count($params) > 0) {
        echo " (";
        $c = 1;
        foreach ($params as $param) {
           echo (($param->isOptional())?"[":"").$param->getName().(($param->isOptional())?"]":"").(($c < count($params))?", ":"");
           $c++;
        }
        echo ") ";
    }
    if (property_exists(ServiceDo, $method)) {

      //echo $method." - ".call_user_func('ServiceDo::d_'.$method)."\n";
      echo " - ";
      $vars = get_class_vars('ServiceDo');
      echo $vars[$method];
    }
    echo "\n";


  }


}

?>