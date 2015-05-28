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
    public static function prepareText($str) {
        $str = str_replace(array("\r\n", "\r", "\n", ".", ",", ":", "'", '"', "(", ")", "[", "]", "{", "}", "?", "!"), '', $str);
        $str = mb_convert_case($str, MB_CASE_UPPER, "UTF-8");
        $words = explode(" ", str_replace("-"," ", $str));
        $en = array();
        $ru = array();
        foreach ($words as $word) {
            if (!empty($word) && $word!='') {
              if(eregi("[a-zA-z]", $word)) {
                $en[] = $word;
              } else {
                $ru[] = $word;
              }
            }
        }
        return array(
          'en' => $en,
          'ru' => $ru,
        );
    }
    public static function morphyText() {
      require_once('lib/phpmorphy/src/common.php');
      try {
        $morphy = new phpMorphy(__DIR__.'/lib/phpmorphy/dicts/ru1', 'ru_RU', array(
          'storage' => PHPMORPHY_STORAGE_FILE,
        ));
        echo $morphy->getEncoding()."\n";
        echo $morphy->getLocale()."\n";
        $src = "Что же умеет эта библиотека? Для начала хотел обратить внимание что можно получить корень слова, морфологические формы слова, определение частей речи, грамматические формы. Я думаю многие разработчики понимают пользу таких преобразований, например, есть возможность улучшить поиск в своей системе, если получать корень слова и искать уже по нему. В данном случае моя задача состояла сбор анкоров в СЕО системе, учитывая морфологию слов.\n";
        echo mb_convert_encoding($src, 'cp866', 'utf-8');
        echo "\n";
        // print_r(self::prepareText($src));
        echo(mb_convert_encoding(json_encode(self::prepareText($src), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), 'cp866', 'utf-8'));
        echo "\n";

        //print_r($morphy->getBaseForm(self::prepareText($src)['ru']));
        echo(mb_convert_encoding(json_encode($morphy->getBaseForm(self::prepareText($src)['ru']), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), 'cp866', 'utf-8'));
        echo "\n";




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