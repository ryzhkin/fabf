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


  tool::xlog('location', tool::getLocation());


?>