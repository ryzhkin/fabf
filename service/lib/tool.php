<?php

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
      $configString = file_get_contents(self::$config['configFile']);
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
        $filename = self::$config['logFolder']."/{$tag}_{$today}.txt";
        if (!file_exists($filename)) {
            //chmod($filename, 0777);
        }
        $fd = fopen($filename, "a+");
        $str = "[" . date("d/m/Y h:i:s", time()) . "] " . $msg;
        fwrite($fd, $str . PHP_EOL);
        fclose($fd);
        //chmod($filename, 0644);
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

}

// Init this tool
tool::readConfiguration();


?>