<?php

/**
 * Class tool
 * @Author: ryshkin@gmail.com
 */
class tool {
    // Configuration for this tool
    public static $config = array(
      'logFolder'    => 'log',
      'configFile'   => 'service/config/service.json',
      'db'           => array(
         'host'      => 'localhost',
         'database'  => '',
         'charset'   => 'utf8',
         'user'      => 'root',
         'password'  => '',
       ),
    );

    // Don't need comments here.
    public static function readConfiguration() {
     // self::xlog('t', defined('__DIR__'));
      $configString = file_get_contents(__DIR__.'/../../'.self::$config['configFile']);
      try {
        $config = json_decode($configString, true);
        self::$config = array_merge(self::$config, $config);
      } catch (Exception $e) {
        self::xlog('configError', 'Error while parse JSON format in the file '.self::$config['configFile']);
      }
    }

    // Useful logger with tags. He is writing log file to the folder "/log"
    public static function xlog($tag, $msg) {
        $msg = print_r($msg, true);
        $today = date("d.m.Y");
        $filename = __DIR__.'/../../'.self::$config['logFolder']."/{$tag}_{$today}.txt";
        if (!file_exists($filename)) {
            //chmod($filename, 0777);
        }
        $fd = fopen($filename, "a+");
        $str = "[" . date("d/m/Y h:i:s", time()) . "] " . $msg;
        fwrite($fd, $str . PHP_EOL);
        fclose($fd);
        //chmod($filename, 0644);
    }

    // Output to console with reencoding
    public static function clog($msg, $color="", $endLine = true, $encoding = 'cp866') {
        if (php_sapi_name() == "cli") {
            $msg = print_r($msg, true);
            $msg = mb_convert_encoding($msg, $encoding, 'utf-8');
            $colors = array(
                'red'      => '031',
                'green'    => '032',
                'white'    => '037',
                'yellow'   => '033',
                'blue'     => '034',
                'purple'   => '035',
                'cyan'     => '036',
            );
            if (isset($colors[$color])) {
                echo "\033[".$colors[$color]."m";
            }
            echo $msg;
            if (isset($colors[$color])) {
                echo "\033[0m";
            }
            if ($endLine) echo "\n";
        }
    }


    // Get connection to  DB MYSQLPDO
    private static  function getDbMYSQLPDO() {
        $db = null;
        try {
            $db = new \PDO('mysql:host='.self::$config['db']['host'].';dbname='.self::$config['db']['database'].';charset='.self::$config['db']['charset'], self::$config['db']['user'], self::$config['db']['password'],
                array(
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.self::$config['db']['charset']
                ));
        } catch (PDOException $e) {
            self::xlog('dbError', 'Error while connecting to DB: '.$e->getMessage());
            print "Error!: " . $e->getMessage() . "<br/>\n";
            die();
        }
        return $db;
    }

    private static $dbMYSQLPDO = null;
    // Useful executor for plain SQL
    public static function runSQL($sql, $params = array()) {
        if (self::$dbMYSQLPDO == null) {
          self::$dbMYSQLPDO = self::getDbMYSQLPDO();
        }
        if (self::$dbMYSQLPDO !== null)
            try {
                $stmt = self::$dbMYSQLPDO->prepare($sql);
                //self::xlog('ddd', $sql);
                $stmt->execute($params);
                if (strripos($sql, 'UPDATE') === FALSE && strripos($sql, 'INSERT') === FALSE && strripos($sql, 'DELETE') === FALSE) {
                    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    return array();
                }
            } catch(\PDOExecption $e) {
                self::$dbMYSQLPDO->rollback();
                self::xlog('dbError', 'SQL: '.$sql);
                self::xlog('dbError', 'Error while connecting to DB: '.$e->getMessage());
                print "SQL: " . $sql . "<br/>\n";
                print "Error!: " . $e->getMessage() . "<br/>\n";
            }

    }

    // Don't need comments here
    public static function getIp () {
        $ip = 'unknown';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        $ip = (($ip == '' || substr_count($ip, '127.0') > 0)?'unknown':$ip);
        /*
        if ($ip == 'unknown') {
            try {
              $result = json_decode(file_get_contents('http://jsonipgeobase.appspot.com'), true);
              if ($result['status'] == 'ok') {
                $ip = $result['ip'];
              }
            } catch (Exception $e) {

            }
        }//*/
        return $ip;

        // http://jsonipgeobase.appspot.com/

    }

    // Don't need comments here
    public static function getLocationByIp ($ip) {
      $result = array(
         'status' => 'no'
      );
      try {
        $result = json_decode(file_get_contents('http://freegeoip.net/json/'.(($ip !== 'unknown')?$ip:'')), true);
        $result['status'] = 'ok';
      } catch (Exception $e) {

      }
      return $result;
    }

    // Don't need comments here
    public static function getLocation () {
      $ip = self::getIp();
      $result = self::getLocationByIp($ip);
      return $result;
    }

    // Get URL with HTTP-Basic authentication
    public static function getAuthHttpUrl0($url, $login, $password) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out;
    }
    // Получение данных по URL с использованием HTTP-Basic authentication
    public static function getAuthHttpUrl($url, $login = null, $password = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($login !== null && $password !== null) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out;
    }

    public static function clearText($text) {
        $text = str_replace("\n", "", $text);
        $text = str_replace("  ", " ", $text);
        $text = str_replace("\t", "", $text);
        $text = trim($text);
        return $text;
    }

    public static function ruStrDateToEng($d) {
       $ru_month = array( 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
       $en_month = array( 'January', 'February', 'March', 'May', 'June', 'July', 'August', 'September', 'Oktober', 'November', 'December' );
       $d = mb_convert_case($d, MB_CASE_LOWER, 'utf-8');
       return str_ireplace ($ru_month, $en_month, $d);
    }

    public static function isAjax() {
        if (
            (!empty( $_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
            ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
            !(strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) ||
            !(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false)
        )
        {
            return true;
        } else {
            return false;
        }
    }
}

// Init this tool
tool::readConfiguration();


?>