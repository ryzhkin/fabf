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
    public static $prepareText = "Splitting and preparing text to the words. Returned array of words.";
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

    // Получаем массив ссылок с ресурса $type и с настройками $options
    private static function getLinks($type = '112', $options = array( ) ) {

    }

    private static function getContent($url) {
      require_once('lib/phpQuery/phpQuery/phpQuery.php');
      $content = '';
      if (strpos($url, '112.ua') !== FALSE ) {
        $content = tool::getAuthHttpUrl($url);
        $doc = phpQuery::newDocument($content);
        $a = $doc->find('.article-text');
        pq($a)->find('.article-img')->remove();
        pq($a)->find('.article_attached')->remove();
        pq($a)->find('p:contains("Ранее сообщалось")')->remove();
        mb_regex_encoding("UTF-8");
        $content = trim(mb_ereg_replace('/\s+/S', " ", $a->text()));


      }
      return $content;
    }

    public static function morphyText() {
      require_once('lib/phpmorphy/src/common.php');
      try {
        $morphyRU = new phpMorphy(__DIR__.'/lib/phpmorphy/dicts/ru2', 'ru_RU', array(
          'storage' => PHPMORPHY_STORAGE_FILE,
        ));
        $morphyEN = new phpMorphy(__DIR__.'/lib/phpmorphy/dicts/en1', 'en_EN', array(
          'storage' => PHPMORPHY_STORAGE_FILE,
        ));

        //echo $morphyRU->getEncoding()."\n";
        //echo $morphyRU->getLocale()."\n";
        $src = "Hi my dear friend. How are you? Что же умеет эта библиотека? Для начала хотел обратить внимание что можно получить корень слова, морфологические формы слова, определение частей речи, грамматические формы. Я думаю многие разработчики понимают пользу таких преобразований, например, есть возможность улучшить поиск в своей системе, если получать корень слова и искать уже по нему. В данном случае моя задача состояла сбор анкоров в СЕО системе, учитывая морфологию слов.\n";
        $preparedText = self::prepareText($src, 4);
        $lemmasRU       = $morphyRU->getBaseForm($preparedText['ru']);
        $lemmasEN       = $morphyEN->getBaseForm($preparedText['en']);
        //tool::clog($src);
        tool::clog($preparedText);
        tool::clog($lemmasRU);
        tool::clog($lemmasEN);



        // tool::clog($morphy->getGramInfoMergeForms('БИБЛИОТЕКА'));
        // tool::clog($morphy->getGramInfoMergeForms('НЕ'));
        // tool::clog($morphy->getGramInfoMergeForms(array('БИБЛИОТЕКА', 'НЕ')));
        // tool::clog(self::getFrequencies($src));

        // http://php.net/manual/en/function.fann-create-standard.php
        //$ann = fann_create_standard(3, 256, 128, 3);

      } catch(phpMorphy_Exception $e) {
        die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
      }
    }

    private static function sortStatistic(&$words) {
       uasort($words, function ($a, $b) {
          if ($a['count'] == $b['count']) {
              return 0;
          }
          return ($a['count'] < $b['count']) ? -1 : 1;
      });
      return $words;
    }
    private static function countWords($text = '') {
       $text = ($text != '')?$text:"Hi my dear friend. How are you? Что же умеет эта библиотека? Для начала хотел обратить внимание что можно получить корень слова, морфологические формы слова, определение частей речи, грамматические формы. Я думаю многие разработчики понимают пользу таких преобразований, например, есть возможность улучшить поиск в своей системе, если получать корень слова и искать уже по нему. В данном случае моя задача состояла сбор анкоров в СЕО системе, учитывая морфологию слов.\n";
       $preparedText = self::prepareText($text);
       $ru = array();
       $en = array();
       require_once('lib/phpmorphy/src/common.php');
       try {
            $morphyRU = new phpMorphy(__DIR__.'/lib/phpmorphy/dicts/ru2', 'ru_RU', array(
                'storage' => PHPMORPHY_STORAGE_FILE,
            ));
            $morphyEN = new phpMorphy(__DIR__.'/lib/phpmorphy/dicts/en1', 'en_EN', array(
                'storage' => PHPMORPHY_STORAGE_FILE,
            ));
            $lemmasRU       = $morphyRU->getBaseForm($preparedText['ru']);
            foreach ($preparedText['ru'] as $word) {
               if (isset($lemmasRU[$word]) && count($lemmasRU[$word]) > 0) {
                   if (isset($ru[$lemmasRU[$word][0]])) {
                       $ru[$lemmasRU[$word][0]]['count']++;
                   } else {
                       $ru[$lemmasRU[$word][0]] = array(
                           'count' => 1,
                       );
                   }
               }
           }
           $lemmasEN  = $morphyEN->getBaseForm($preparedText['en']);
           foreach ($preparedText['en'] as $word) {
               if (isset($lemmasEN[$word]) && count($lemmasEN[$word]) > 0) {
                   if (isset($en[$lemmasEN[$word][0]])) {
                       $en[$lemmasEN[$word][0]]['count']++;
                   } else {
                       $en[$lemmasEN[$word][0]] = array(
                           'count' => 1,
                       );
                   }
               }
           }
       } catch(phpMorphy_Exception $e) {
           die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
       }
       $statistic = array(
         'en' => self::sortStatistic($en),
         'ru' => self::sortStatistic($ru),
       );
       //tool::clog($statistic);
       return $statistic;
    }
    private static function addStatistic($s1, $s2) {
      foreach ($s2 as $w2 => $v2) {
        if (isset($s1[$w2]) && isset($s1[$w2]['count']) && isset($v2['count'])) {
          $s1[$w2]['count'] +=  $v2['count'];
        } else {
          $s1[$w2]['count'] =  $v2['count'];
        }
      }
      return $s1;
    }
    public static function getStatisticForTexts ($texts = array()) {
        if (count($texts) == 0)
        $texts = array(
          "Назначение Михеила Саакашвили председателем Одесской ОГА привлекло внимание за рубежом. Об этом заявил президент Украины Петр Порошенко в ходе встречи с председателем Одесской ОГА Михеилом Саакашвили, сообщили в пресс-службе главы государства.",
          "Почти 60 тонн сыра, который перевозился без необходимых документов, не пустили в Россию из Казахстана, сообщается на сайте Россельхознадзора.",
          "Почти В Японии в результате падения 700-килограммового воздушного змея пострадали четыре человека, сообщает The Guardian.",
          "Почти Атомная электростанция в Бушере заработала в 2011 г. при содействии РФ. Она была спроектирована так, что выдерживает землетрясение магнитудой 8.",
        );
        $ru = array();
        $en = array();
        foreach ($texts as $text) {
          $s = self::countWords($text);
          $ru = self::addStatistic($ru, $s['ru']);
          $en = self::addStatistic($en, $s['en']);
        }
        $minStatCount = 2;

        $ru_out = array();
        foreach ($ru as $w => $v) {
          if ($w !== '' && $v['count'] >= $minStatCount) {
            $ru_out[$w] = $v;
          }
        }

        $en_out = array();
        foreach ($en as $w => $v) {
            if ($w !== '' && $v['count'] >= $minStatCount) {
                $en_out[$w] = $v;
            }
        }

        $statistic = array(
          'en' => self::sortStatistic($en_out),
          'ru' => self::sortStatistic($ru_out),
        );
        tool::clog($statistic);
        return $statistic;
    }

    public static $run = "Запуск серверного кода системы";
    public static function run () {
        $content = self::getContent('http://112.ua/ato/v-luganskoy-obl-na-mine-podorvalsya-traktor-voditel-ranen-mvd-234944.html');
        tool::clog($content);

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