<?php
   require_once('/home1/amarrine/www/steam/db.php');
   require_once('/home1/amarrine/www/steam/codebird-php-2.4.1/src/codebird.php');
   require_once('/home1/amarrine/www/steam/twitter.php');

   date_default_timezone_set('UTC');

   if ($_REQUEST['wizard'] != 'iDl3') {
      print "\nERROR\n\n";
      exit;
   }
   else {
      print "\nPROCESSING\n\n";
   }
   
   class player {
      var $hashtag;
      var $level;
      var $score;
      var $steamid;
      var $string;
      var $twitter;
      var $twitch;
   }

   /**
    * Writes the given text with a border into the image using TrueType fonts.
    * @author John Ciacia 
    * @url   http://www.johnciacia.com/2010/01/04/using-php-and-gd-to-add-border-to-text/
    * @param image An image resource
    * @param size The font size
    * @param angle The angle in degrees to rotate the text
    * @param x Upper left corner of the text
    * @param y Lower left corner of the text
    * @param textcolor This is the color of the main text
    * @param strokecolor This is the color of the text border
    * @param fontfile The path to the TrueType font you wish to use
    * @param text The text string in UTF-8 encoding
    * @param px Number of pixels the text border will be
    * @see http://us.php.net/manual/en/function.imagettftext.php
    */
   function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
    
       for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
           for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
               $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
       
      return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
   }

   function create_image($player) {
      $i = imagecreatefrompng("images/daily.png");

      $white = imagecolorallocate($i, 255, 255, 255);
      $black = imagecolorallocate($i,   0,   0,   0);

      $icon = imagecreatefromjpeg('/home1/amarrine/www/steam/images/' . $player->steamid . '.jpg');
      list($width, $height) = getimagesize('/home1/amarrine/www/steam/images/' . $player->steamid . '.jpg');
      imagecopy($i, $icon, 19, 38, 0, 0, $width, $height);
      imagettfstroketext($i, 32.0, 0, 207, 72, $white, $black, 'fonts/Tekton-Bold', $player->string, 3);
      imagettfstroketext($i, 32.0, 0, 182, 210, $white, $black, 'fonts/Tekton-Bold', $player->score, 3);
      imagettfstroketext($i, 32.0, 0, 392, 210, $white, $black, 'fonts/Tekton-Bold', $player->level, 3);
      imagettfstroketext($i, 12.0, 0, 8, 184, $white, $black, 'fonts/Tekton-Bold', date('m/d/Y'), 1);
      imagettfstroketext($i, 10.0, 0, 8, 200, $white, $black, 'fonts/Tekton-Bold', '@KlepekVsRemo', 1);

      imagepng($i, '/home1/amarrine/www/steam/images/daily_' . $player->steamid . '.png');
      imagedestroy($i);
   }

   function check_today($player) {
      $found = 1;

      $last = trim(file_get_contents('/home1/amarrine/www/steam/' . $player->steamid));
      if ($last != date('m/d/Y')) {
         $found = 0;
      }

      return $found;
   }

   function get_leaderboard_data($player, $leaderboard) {
      $steamid = $player->steamid;
      $score = -1;

      $player_spelunky_leaderboard = 'http://steamcommunity.com/stats/239350/leaderboards/' . $leaderboard . '/?xml=1&steamid=' . $steamid;

      $xml = file_get_contents($player_spelunky_leaderboard);
      $ob = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
      $json = json_encode($ob);
      $array = json_decode($json, true);

      foreach($array['entries']['entry'] as $key => $value) {
         if ($value['steamid'] == $steamid) {
            $player->score = $value['score'];

            $level = hexdec(substr($value['details'], 8, 2));
            $player->level = ceil($level / 4) . "-" . ($level % 4 == 0? 4 : ($level % 4));
         }
      }
   }

   function get_todays_leaderboard() {
      $leaderboard = '';

      $today = date('m/d/Y') . ' DAILY';
      $url = 'http://steamcommunity.com/stats/239350/leaderboards/?xml=1';

      $xml = file_get_contents($url);
      $ob = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
      $json = json_encode($ob);
      $array = json_decode($json, true);

      foreach($array['leaderboard'] as $key => $value) {
         if ($value['name'] == $today) {
            $leaderboard = $value['lbid'];
         }
      }

      return $leaderboard;
   }

   function get_twitch_url($api) {
      $url = null;

      $data = file_get_contents($api);
      $json = json_decode($data, TRUE);

      foreach($json['videos'] as $key => $val) {
         $rec_date_arr = getdate(strtotime($val['recorded_at']));
         $rec_date = str_pad($rec_date_arr['mon'], 2, "0", STR_PAD_LEFT) . "/"
                      . str_pad($rec_date_arr['mday'], 2, "0", STR_PAD_LEFT) . "/"
                      . $rec_date_arr['year'];

         if ($val['game'] == 'Spelunky' && $rec_date == date('m/d/Y')) {
            $url = $val['url'] . "\n";
         }
      }

      return $url;
   }

   function post_tweet($cb, $player) {
      $todays_date = date('m/d/Y');
      $params = array(
         'status'  => $player->string . " scored $" . 
                         $player->score . " on " . 
                         $todays_date . " " .
                         $player->hashtag . " \n" . 
                         "Run ended at " . 
                         $player->level . " \n" . 
                         $player->twitch,
         'media[]' => '/home1/amarrine/www/steam/daily_' . $player->steamid . '.png'
      );

   //   $reply = $cb->statuses_updateWithMedia($params);
      file_put_contents('/home1/amarrine/www/steam/' . $player->steamid, date('m/d/Y'));
   }

   function post_winner($cb, $player1, $player2) {
      $found = 1;

      $todays_date = date('m/d/Y');
      $last = trim(file_get_contents('/home1/amarrine/www/steam/winner'));
      if ($last != $todays_date) {
         $found = 0;
      }

      if (! $found) {
         $params = array(
            'status' => "On " . $todays_date . " " . 
                           $player1->string . " (" . 
                           $player1->score . ") defeated " . 
                           $player2->string . " (" . 
                           $player2->score . ")! " .
                           $player1->hashtag . " #Spelunky"
         );

         print "Posting winner!\n";
   //      $reply = $cb->statuses_update($params);
         file_put_contents('/home1/amarrine/www/steam/winner', $todays_date);
      }
   }

   function save_score($player) {

      $result = mysql_query("select * from spelunky_scores " .
                            " where scores_players_id = " . $player->steamid . 
                            "   and scores_date = date('" . date('Y-m-d') . "')");

      if (! ($row = mysql_fetch_array($result))) {
         $insert = "insert into spelunky_scores (" . 
               "scores_id, " . 
               "scores_players_id, " .
               "scores_date, " . 
               "scores_score, " . 
               "scores_level, " .
               "scores_twitch) values (" . 
               "null, " .
               $player->steamid . ", " .
               "date('" . date('Y-m-d') . "'), " . 
               $player->score . ", " .
               "'" . $player->level . "', " .
               "'" . trim($player->twitch) . "')";

         mysql_query($insert);
      }
   }

   $consumer_key = $_CONSUMER_KEY;
   $consumer_secret = $_CONSUMER_SECRET;

   $access_token = $_ACCESS_TOKEN;
   $access_token_secret = $_ACCESS_TOKEN_SECRET;

   \Codebird\Codebird::setConsumerKey($consumer_key, $consumer_secret);
   $cb = \Codebird\Codebird::getInstance();
   $cb->setToken($access_token, $access_token_secret);

   $spelunky_app_id = '239350';

   $todays_date = date('m/d/Y');
   $leaderboard = get_todays_leaderboard();
   print "Today: $todays_date \n";
   print "Leaderboard: $leaderboard \n";

   $players = array();
   $result = mysql_query('select * from spelunky_players');
   while ($row = mysql_fetch_array($result)) {
      $the_player = new player();
      $the_player->steamid = $row['players_id'];
      $the_player->hashtag = $row['players_hashtag'];
      $the_player->string  = $row['players_name'];
      $the_player->twitter = $row['players_twitter'];
      $the_player->twitch = get_twitch_url($row['players_twitch_api']);
      get_leaderboard_data($the_player, $leaderboard);
      print $the_player->string . ": (" 
               . $the_player->steamid . ") " 
               . $the_player->score . " " 
               . $the_player->level . "\n";
      print $the_player->string . " Twitch: " . $the_player->twitch . "\n";

      create_image($the_player);
      save_score($the_player);

      $tweet_today = check_today($the_player);
      if (! $tweet_today && $the_player->score > 0) {
         print "Posting " . $the_player->string . " ...\n";
         post_tweet($cb, $the_player);   
      }

      array_push($players, $the_player);
   }


   if (count($players) == 2) {
      $player1 = $players[0];
      $player2 = $players[1];

      if ($player1->score > 0 && $player2->score > 0) {
         if ($player1->score != $player2->score) {
            if ($player1->score > $player2->score) {
               post_winner($cb, $player1, $player2);
            }
            else {
               post_winner($cb, $player2, $player1);
            }
         }
      }
   }
?>
