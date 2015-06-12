<?php

class ServiceDo {
    public static $prepareText = "Splitting and preparing text to the words. Returned array of words.";
    public static function prepareText($str, $minWordLen = 5) {
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
        $links = array();
        switch ($type) {
            case '112': {
                require_once('phpQuery/phpQuery/phpQuery.php');
                $url = 'http://112.ua/archive';
                $query = '';
                if (isset($options['category'])) {
                    foreach ($options['category'] as $c) {
                        $query .= 'category[]='.$c.'&';
                    }
                    $query = rtrim($query, '&');
                }
                if (isset($options['page'])) {
                    $query .= (($query !== '')?'&':'').'page='.$options['page'];
                }
                if ($query !== '') {
                    $url .= '?'.$query;
                }
                $content = tool::getAuthHttpUrl($url);
                $doc = phpQuery::newDocument($content);
                $items = $doc->find('ul.news-list li');
                foreach ($items as $item) {
                    $url = pq($item)->find('p a')->attr('href');
                    if (strpos($url, '/video/') === FALSE) {
                        $links[] = array(
                            'date_time_str' => pq($item)->find('.time')->text(),
                            'date_time'     => date('Y-m-d H:i:s', strtotime(tool::ruStrDateToEng(pq($item)->find('.time')->text())) ),
                            'url'           => 'http://112.ua'.$url,
                        );
                    }
                }
                break;
            }
            case 'file': {
                $links = json_decode(file_get_contents(__DIR__.'/../../'.$options), true);
                break;
            }
        }
        return $links;
    }

    private static function saveDataToFile($links, $fileName= 'content.json') {
        $links = json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = __DIR__.'/../../'.$fileName;
        if (!file_exists($filename)) {
            //chmod($filename, 0777);
        }
        $fd = fopen($filename, "w");
        fwrite($fd, $links.PHP_EOL);
        fclose($fd);
    }

    private static function getContent($url) {
        require_once('phpQuery/phpQuery/phpQuery.php');
        $content = '';
        if (strpos($url, '112.ua') !== FALSE ) {
            $content = tool::getAuthHttpUrl($url);
            $doc = phpQuery::newDocument($content);
            $a = $doc->find('.article-text');
            pq($a)->find('.article-img')->remove();
            pq($a)->find('.article-img__info')->remove();
            pq($a)->find('.rsCaption')->remove();
            pq($a)->find('.article_attached')->remove();
            pq($a)->find('.flowplayer')->remove();
            pq($a)->find('p:contains("Ранее сообщалось")')->remove();
            $content = tool::clearText($a->text());
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

    private static function sortStatistic(&$words, $inverse = false) {
        if ($inverse) {
            uasort($words, function ($a, $b) {
                if ($a['count'] == $b['count']) {
                    return 0;
                }
                return ($a['count'] > $b['count']) ? -1 : 1;
            });
        } else {
            uasort($words, function ($a, $b) {
                if ($a['count'] == $b['count']) {
                    return 0;
                }
                return ($a['count'] < $b['count']) ? -1 : 1;
            });
        }
        return $words;
    }

    private static function countWords($text = '') {
        $text = ($text != '')?$text:"Hi my dear friend. How are you? Что же умеет эта библиотека? Для начала хотел обратить внимание что можно получить корень слова, морфологические формы слова, определение частей речи, грамматические формы. Я думаю многие разработчики понимают пользу таких преобразований, например, есть возможность улучшить поиск в своей системе, если получать корень слова и искать уже по нему. В данном случае моя задача состояла сбор анкоров в СЕО системе, учитывая морфологию слов.\n";
        $preparedText = self::prepareText($text);
        $ru = array();
        $en = array();
        require_once('phpmorphy/src/common.php');
        try {
            $morphyRU = new phpMorphy(__DIR__.'/phpmorphy/dicts/ru2', 'ru_RU', array(
                'storage' => PHPMORPHY_STORAGE_FILE,
            ));
            $morphyEN = new phpMorphy(__DIR__.'/phpmorphy/dicts/en1', 'en_EN', array(
                'storage' => PHPMORPHY_STORAGE_FILE,
            ));
            $lemmasRU       = $morphyRU->getBaseForm($preparedText['ru'], phpMorphy::IGNORE_PREDICT);
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
            $lemmasEN  = $morphyEN->getBaseForm($preparedText['en'], phpMorphy::IGNORE_PREDICT);
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
    public static function getStatisticForTexts ($texts = array(), $options = array()) {
        $opt = array(
            'minStatCount'  => 2,          // Отсечение по минимальному кол-ву повторов слов
            'maxCountWords' => 100000000,  // Кол-во слов которые попадают в конечный рейтинг
        );
        $opt = array_merge($opt, $options);
        if (count($texts) == 0) {
            $texts = array(
                "Назначение Михеила Саакашвили председателем Одесской ОГА привлекло внимание за рубежом. Об этом заявил президент Украины Петр Порошенко в ходе встречи с председателем Одесской ОГА Михеилом Саакашвили, сообщили в пресс-службе главы государства.",
                "Почти 60 тонн сыра, который перевозился без необходимых документов, не пустили в Россию из Казахстана, сообщается на сайте Россельхознадзора.",
                "Почти В Японии в результате падения 700-килограммового воздушного змея пострадали четыре человека, сообщает The Guardian.",
                "Почти Атомная электростанция в Бушере заработала в 2011 г. при содействии РФ. Она была спроектирована так, что выдерживает землетрясение магнитудой 8.",
            );
        }

        // Если на входе комплексный массив данных
        if (isset($texts[0]['text'])) {
            $texts2 = array();
            foreach ($texts as $text) {
                $texts2[] = $text['text'];
            }
            $texts = $texts2;
        }



        $ru = array();
        $en = array();
        foreach ($texts as $text) {
            $s = self::countWords($text);
            $ru = self::addStatistic($ru, $s['ru']);
            $en = self::addStatistic($en, $s['en']);
        }


        $ru_out = array();
        foreach ($ru as $w => $v) {
            if ($w !== '' && $v['count'] >= $opt['minStatCount']) {
                $ru_out[$w] = $v;
            }
        }

        $en_out = array();
        foreach ($en as $w => $v) {
            if ($w !== '' && $v['count'] >= $opt['minStatCount']) {
                $en_out[$w] = $v;
            }
        }

        $en_out = self::sortStatistic($en_out, true);
        $ru_out = self::sortStatistic($ru_out, true);

        // Получение информации о частях речи
        // http://phpmorphy.sourceforge.net/dokuwiki/manual-graminfo
        require_once('phpmorphy/src/common.php');
        try {
            $morphyRU = new phpMorphy(__DIR__.'/phpmorphy/dicts/ru2', 'ru_RU', array(
                'storage' => PHPMORPHY_STORAGE_FILE,
            ));
            foreach ($ru_out as $w => $v) {
                $ru_out[$w]['gram'] = $morphyRU->getPartOfSpeech($w);
            }
        } catch(phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
        }

        // Фильтр на части речи
        $en = $en_out;
        $ru = array();

        foreach ($ru_out as $w => $v) {
            if (count($ru) <= $opt['maxCountWords'])
                if (isset($ru_out[$w]['gram'])) {
                    foreach ($ru_out[$w]['gram'] as $gram) {
                        if (in_array($gram, array('С', /*'ИНФИНИТИВ',*/ 'ФРАЗ', 'Г'/*, 'Н'*/))) {
                            $ru[$w] = $ru_out[$w];
                            break;
                        }
                        //tool::clog($gram);
                    }
                } else {
                    $ru[$w] = $ru_out[$w];
                }
        }

        $statistic = array(
            'en' => $en,
            'ru' => $ru,
        );
        // tool::clog($statistic);
        return $statistic;
    }


    private static function isUniqueText ($list, $item, $samePercent = 80) {
        foreach ($list as $item0) {
           similar_text($item['text'], $item0['text'], $p);
           if ($p >= $samePercent) {
             return false;
           }
        }
        return true;
    }
    private static function getUniqueTexts ($list, $samePercent = 80) {
      $uniqueTexts = array();
      foreach ($list as $item) {
        if (self::isUniqueText($uniqueTexts, $item, $samePercent)) {
            $uniqueTexts[] = $item;
        }
      }
      return $uniqueTexts;
    }


    private static function getTexts() {
        tool::clog('Get ', 'yellow', false);
        tool::clog('BAD', 'red', false);
        tool::clog(' texts:'."\n", 'yellow');

        tool::clog('1) Get from 112.ua: ', 'yellow');
        $maxPage = 1;
        $links = array();
        tool::clog('[1 - '.$maxPage.']', 'yellow');
        for ($p = 1; $p <= $maxPage; $p++) {
            $ll = self::getLinks('112', array(
                'category' => [7],
                'page'     => $p,
            ));
            foreach ($ll as $l) {
               $links[] = $l;
            }
            tool::clog($p.', ', 'green', false);
        }
        self::saveDataToFile($links, 'data/links_bad.json');

        $percent = 0;
        $c = 0;
        $start = time();
        foreach ($links as &$link) {
            //tool::clog($link);
            $text = self::getContent($link['url']);
            $link['text'] = $text;

            $c++;
            $dpercent = floor(($c/count($links))*100);
            if ($dpercent > $percent) {
                $percent = $dpercent;
                sleep(1);
                echo "\x1b[K";
                tool::clog("\r  Progress - ".$percent.'% ', 'white', false);
                if ($percent < 100) {
                    //tool::clog('...', '', false);
                } else {
                    tool::clog("\r  Completed - ", '', false);
                    tool::clog($percent.'%', 'green', false);
                    tool::clog(" Time: ".(time()-$start)." s", 'cyan', false);
                    tool::clog('');
                }
            }
        }
        $links = self::getUniqueTexts($links);
        self::saveDataToFile($links, 'data/texts_bad.json');
        //*/

        // http:\\112.ua/avarii-chp/v-kieve-na-troeshhine-prorvalo-truboprovod-iz-pod-zemli-bil-fontan-vysotoy-s-devyatietazhnyy-dom-236877.html

    }

    public static $run = "Запуск серверного кода системы";
    public static function run () {
        self::getTexts();

       /* $links = self::getLinks('file', 'data/texts_bad.json');
        //tool::clog($links);
        $statistic = self::getStatisticForTexts($links, array(
            //'minStatCount'  => 2,   // Отсечение по минимальному кол-ву повторов слов
            //'maxCountWords' => 255,   // Кол-во слов которые попадают в конечный рейтинг
        ));
        self::saveDataToFile($statistic, 'data/statistic_bad.json');
        //tool::clog($statistic);*/
        //*/




        // http://php.net/manual/en/function.fann-create-standard.php
        // $ann = fann_create_standard(3, 256, 128, 3);

    }



}


?>