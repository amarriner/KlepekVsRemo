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

   /**
    * Flip (mirror) an image left to right.
    * @url http://stackoverflow.com/questions/15811421/imageflip-in-php-is-undefined
    * @param image  resource
    * @param x      int
    * @param y      int
    * @param width  int
    * @param height int
    * @return bool
    * @require PHP 3.0.7 (function_exists), GD1
    */
   function imageflip(&$image, $x = 0, $y = 0, $width = null, $height = null)
   {
       if ($width  < 1) $width  = imagesx($image);
       if ($height < 1) $height = imagesy($image);
       // Truecolor provides better results, if possible.
       if (function_exists('imageistruecolor') && imageistruecolor($image))
       {
           $tmp = imagecreatetruecolor(1, $height);
       }
       else
       {
           $tmp = imagecreate(1, $height);
       }

       // My own change to preserve alpha (amarriner)
       imagealphablending($tmp, false);
       imagesavealpha($tmp, true);

       $x2 = $x + $width - 1;
       for ($i = (int) floor(($width - 1) / 2); $i >= 0; $i--)
       {
           // Backup right stripe.
           imagecopy($tmp,   $image, 0,        0,  $x2 - $i, $y, 1, $height);
           // Copy left stripe to the right.
           imagecopy($image, $image, $x2 - $i, $y, $x + $i,  $y, 1, $height);
           // Copy backuped right stripe to the left.
           imagecopy($image, $tmp,   $x + $i,  $y, 0,        0,  1, $height);
       }
       imagedestroy($tmp);
       return true;
   }

   // Creates winner image for winning tweet
   function create_winner_image($player_left, $player_right) {
      $i = imagecreatefrompng(_PWD . '/images/winner.png');

      $levelsketch = imagecreatefrompng(_PWD . '/images/levelsketch.png');

      // Coordinates for background images in the levelsketch.png file
      // Backgrounds are around 330x190
      $backgrounds = array(
         array(0,     0),
         array(330,   0),
         array(660,   0),
         array(0,   190),
         array(330, 190)
      );

      // Coordinates for character images from the char_*.png files
      // Character sprites are 80x80
      $characters = array(
         array(880, 720),      // Winner
         array(720, 0)         // Loser
      );

      // Characters are stored as a hex value on the leaderboards. The process.php script translates them
      // to decimal which is used as the index for this array
      $colors = array(
         'orange',
         'red',
         'green',
         'blue',
         'white',
         'pink',
         'yellow',
         'brown',
         'purple',
         'black',
         'cyan',
         'lime',
         'dlc1',
         'dlc2',
         'dlc3',
         'dlc4',
         'dlc5',
         'dlc6',
         'dlc7',
         'dlc8'
      );

      $white = imagecolorallocate($i, 255, 255, 255);
      $black = imagecolorallocate($i,   0,   0,   0);

      // Get left side player icon
      $icon_left = imagecreatefromjpeg(_PWD . '/images/' . $player_left->steamid . '.jpg');
      list($width_left, $height_left) = getimagesize(_PWD . '/images/' . $player_left->steamid . '.jpg');
      $left_text = ($player_left->score > $player_right->score ? $player_left->hashtag : date('m-d-Y'));
      $left_level = substr($player_left->level, 0, 1) - 1;

      imagefilledrectangle($i, 131, 241, 132 + ($width_left / 2), 242 + ($height_left / 2), $white);
      imagecopyresized($i, $icon_left, 132, 242, 0, 0, ($width_left / 2), ($height_left / 2), $width_left, $height_left);

      // Place left side character 
      $character_coords = ($player_left->score > $player_right->score ? $characters[0] : $characters[1]);
      $character = imagecreatefrompng(_PWD . '/images/char_' . $colors[$player_left->character] . '.png');
      imagecopy($i, $character, 300, 300, $character_coords[0], $character_coords[1], 80, 80);
      imagedestroy($character);

      // Copy left player level to image
      imagecopy($i, $levelsketch, 95, 28, $backgrounds[$left_level][0], $backgrounds[$left_level][1], 330, 190);

      // Fill out left side of book text
      imagettfstroketext($i, 24.0, 0, 175, 265, $white, $black, 'fonts/Tekton-Bold', $player_left->string, 3);
      imagettfstroketext($i, 20.0, 0, 145, 308, $white, $black, 'fonts/Tekton-Bold', 'Level ' .$player_left->level, 3);
      imagettfstroketext($i, 20.0, 0, 145, 348, $white, $black, 'fonts/Tekton-Bold', $player_left->score, 3);
      imagettftext($i, 16.0, 0, 210, 433, $black, 'fonts/Tekton-Bold', $left_text);

      // Get right side player icon
      $icon_right = imagecreatefromjpeg(_PWD . '/images/' . $player_right->steamid . '.jpg');
      list($width_right, $height_right) = getimagesize(_PWD . '/images/' . $player_right->steamid . '.jpg');
      $right_text = ($player_right->score > $player_left->score ? $player_right->hashtag : date('m-d-Y'));
      $right_level = substr($player_right->level, 0, 1) - 1;

      imagefilledrectangle($i, 541, 241, 542 + ($width_right / 2), 242 + ($height_right / 2), $white);
      imagecopyresized($i, $icon_right, 542, 242, 0, 0, ($width_right / 2), ($height_right / 2), $width_right, $height_right);

      // Place right side character. Have to flip it horizontally. My installed version of PHP doesn't have the 
      // native imageflip function so I included one from Stack Overflow here. I modified it slightly to preserve
      // transparency.
      $character_coords = ($player_right->score > $player_left->score ? $characters[0] : $characters[1]);
      $temp = imagecreatetruecolor(80, 80);
      $character = imagecreatefrompng(_PWD . '/images/char_' . $colors[$player_right->character] . '.png');
      imagealphablending($temp, false);
      imagesavealpha($temp, true);
      imagecopy($temp, $character, 0, 0, $character_coords[0], $character_coords[1], 80, 80);
      imageflip($temp, IMG_FLIP_HORIZONTAL);
      imagecopy($i, $temp, 535, 300, 0, 0, 80, 80);
      imagedestroy($temp);
      imagedestroy($character);
         
      // Copy right player level to image
      imagecopy($i, $levelsketch, 485, 28, $backgrounds[$right_level][0], $backgrounds[$right_level][1], 330, 190);

      // Fill out right side book text
      imagettfstroketext($i, 24.0, 0, 583, 265, $white, $black, 'fonts/Tekton-Bold', $player_right->string, 3);
      imagettfstroketext($i, 20.0, 0, 678, 308, $white, $black, 'fonts/Tekton-Bold', 'Level ' . $player_right->level, 3);
      imagettfstroketext($i, 20.0, 0, 678, 348, $white, $black, 'fonts/Tekton-Bold', $player_right->score, 3);
      imagettftext($i, 16.0, 0, 610, 433, $black, 'fonts/Tekton-Bold', $right_text);

      imagettftext($i, 10.0, 0, 745, 470, $black, 'fonts/Tekton-Bold', '@KlepekVsRemo');

      // Preserve transparency
      imagealphablending($i, false);
      imagesavealpha($i, true);

      // Save with highest compression and lowest filesize
      // Would like to decrease filesize further, but haven't been able to as yet
      imagepng($i, _PWD . '/images/daily_winner.png', 9, PNG_ALL_FILTERS);

      imagedestroy($levelsketch);
      imagedestroy($icon_left);
      imagedestroy($icon_right);
      imagedestroy($i);      
   }

   // Creates daily badge for the given player
   function create_image($player) {
      $i = imagecreatefrompng(_PWD . '/images/daily.png');

      // Characters are stored as a hex value on the leaderboards. The process.php script translates them
      // to decimal which is used as the index for this array
      $colors = array(
         'orange',
         'red',
         'green',
         'blue',
         'white',
         'pink',
         'yellow',
         'brown',
         'purple',
         'black',
         'cyan',
         'lime',
         'dlc1',
         'dlc2',
         'dlc3',
         'dlc4',
         'dlc5',
         'dlc6',
         'dlc7',
         'dlc8'
      );

      $white = imagecolorallocate($i, 255, 255, 255);
      $black = imagecolorallocate($i,   0,   0,   0);

      $icon = imagecreatefromjpeg(_PWD . '/images/' . $player->steamid . '.jpg');
      list($width, $height) = getimagesize(_PWD . '/images/' . $player->steamid . '.jpg');
      imagefilledrectangle($i, 18, 37, 83, 102, $white);
      imagecopy($i, $icon, 19, 38, 0, 0, $width, $height);
      imagettfstroketext($i, 32.0, 0, 207, 72, $white, $black, 'fonts/Tekton-Bold', $player->string, 3);
      imagettfstroketext($i, 32.0, 0, 182, 210, $white, $black, 'fonts/Tekton-Bold', $player->score, 3);
      imagettfstroketext($i, 32.0, 0, 392, 210, $white, $black, 'fonts/Tekton-Bold', $player->level, 3);
      imagettftext($i, 14.0, 0, 220, 17, $white, 'fonts/Tekton-Bold', date('m/d/Y'));
      imagettftext($i, 10.0, 0, 2, 252, $white, 'fonts/Tekton-Bold', '@KlepekVsRemo');

      $character = imagecreatefrompng(_PWD . '/images/char_' . $colors[$player->character] . '.png');
      imagecopy($i, $character, 13, 153, 0, 880, 80, 80);

      imagepng($i, _PWD . '/images/daily_' . $player->steamid . '.png');
      imagedestroy($icon);
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

            // Characters are stored as hex values, converting to decimal
            $player->character = hexdec(substr($value['details'], 0, 2));

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
                           $player1->hashtag . " #Spelunky \n",
            'media[]' => _PWD . '/images/daily_winner.png'
         );

         print "Posting winner!\n";
         $reply = $cb->statuses_updateWithMedia($params);
         file_put_contents(_PWD . '/winner', $todays_date);
      }
   }

   // Store player results in a database table
   // Currently not used for anything
   function save_score($player) {
      if ($player->score >= 0 && $player->level != '1-1') {
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
                  "scores_character, " .
                  "scores_twitch) values (" . 
                  "null, " .
                  $player->steamid . ", " .
                  "date('" . date('Y-m-d') . "'), " . 
                  $player->score . ", " .
                  "'" . $player->level . "', " .
                  $player->character . ", " . 
                  "'" . trim($player->twitch) . "')";

            mysql_query($insert);
         }
      }
   }
?>
