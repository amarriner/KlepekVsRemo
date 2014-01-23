<?php
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

   // Creates daily badge for the given player
   function create_image($player) {
      $i = imagecreatefrompng("images/daily.png");

      $white = imagecolorallocate($i, 255, 255, 255);
      $black = imagecolorallocate($i,   0,   0,   0);

      $icon = imagecreatefromjpeg(_PWD . '/images/' . $player->steamid . '.jpg');
      list($width, $height) = getimagesize(_PWD . '/images/' . $player->steamid . '.jpg');
      imagefilledrectangle($i, 18, 37, 84, 103, $white);
      imagecopy($i, $icon, 19, 38, 0, 0, $width, $height);
      imagettfstroketext($i, 32.0, 0, 207, 72, $white, $black, 'fonts/Tekton-Bold', $player->string, 3);
      imagettfstroketext($i, 32.0, 0, 182, 210, $white, $black, 'fonts/Tekton-Bold', $player->score, 3);
      imagettfstroketext($i, 32.0, 0, 392, 210, $white, $black, 'fonts/Tekton-Bold', $player->level, 3);
      imagettfstroketext($i, 12.0, 0, 8, 184, $white, $black, 'fonts/Tekton-Bold', date('m/d/Y'), 1);
      imagettfstroketext($i, 10.0, 0, 8, 200, $white, $black, 'fonts/Tekton-Bold', '@KlepekVsRemo', 1);

      imagepng($i, _PWD . '/images/daily_' . $player->steamid . '.png');
      imagedestroy($i);
   }

   // Checks to see if the player's data has already been tweeted
   function check_today($player) {
      $found = 1;

      $last = trim(file_get_contents(_PWD . '/' . $player->steamid));
      if ($last != date('m/d/Y')) {
         $found = 0;
      }

      return $found;
   }

   // Retrieves the player's leaderboard data for today
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

            // Levels ares stored as hex values from 0-19, so convert them into what's shown on the leaderboards in-game
            $level = hexdec(substr($value['details'], 8, 2));
            $player->level = ceil($level / 4) . "-" . ($level % 4 == 0? 4 : ($level % 4));
         }
      }
   }

   // Find the id for today's daily challenge leaderboard
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

   // Attempt to match a twitch URL with the player's daily challenge run
   // Looks through their twitch past broadcasts and finds one on today's date that is labelled Spelunky
   // Will have problems with timezones if the runs are recorded before midnight server time
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

   // Tweet individual results
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
         'media[]' => _PWD . '/images/daily_' . $player->steamid . '.png'
      );

      $reply = $cb->statuses_updateWithMedia($params);
      file_put_contents(_PWD . '/' . $player->steamid, date('m/d/Y'));
   }

   // Tweet winner
   function post_winner($cb, $player1, $player2) {
      $found = 1;

      $todays_date = date('m/d/Y');
      $last = trim(file_get_contents(_PWD . '/winner'));
      if ($last != $todays_date) {
         $found = 0;
      }

      if (! $found) {
         $params = array(
            'status' => "On " . $todays_date . " " . 
                           $player1->string . " ($" . 
                           $player1->score . ") defeated " . 
                           $player2->string . " ($" . 
                           $player2->score . ")! " .
                           $player1->hashtag . " #Spelunky"
         );

         print "Posting winner!\n";
         $reply = $cb->statuses_update($params);
         file_put_contents(_PWD . '/winner', $todays_date);
      }
   }

   // Store player results in a database table
   // Currently not used for anything
   function save_score($player) {
      if ($player->score >= 0 || $player->level) {
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
   }
?>
