<?php
   // Various Constants
   require_once('constants.php');

   // Database connection setup
   require_once(_PWD . '/db.php');

   // Include classes
   require_once(_PWD . '/classes.php');

   // Include functions
   require_once(_PWD . '/functions.php');

   // Codebird PHP Twitter Library
   require_once(_PWD . '/codebird-php-2.4.1/src/codebird.php');

   // An attempt to get the correct daily challenge day regardless of server timezone
   date_default_timezone_set('UTC');

   // Trying to prevent remote execution, though actually it wouldn't really be that big
   // of a deal since a remote execution would do the same thing as a normal cron run
   if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
      print "\n\nERROR!\n\n";
      exit;
   }
   else {
      print "\n\nPROCESSING...\n\n";
   }

   // Actual keys stored in a file outside the git repository
   $consumer_key = _CONSUMER_KEY;
   $consumer_secret = _CONSUMER_SECRET;

   $access_token = _ACCESS_TOKEN;
   $access_token_secret = _ACCESS_TOKEN_SECRET;

   // Set up Codebird object
   \Codebird\Codebird::setConsumerKey($consumer_key, $consumer_secret);
   $cb = \Codebird\Codebird::getInstance();
   $cb->setToken($access_token, $access_token_secret);

   // TODO: Use this in Steam API URL's
   $spelunky_app_id = '239350';

   // Determine today's leaderboard
   $todays_date = date('m/d/Y');
   $leaderboard = get_todays_leaderboard();
   print "Today: $todays_date \n";
   print "Leaderboard: $leaderboard \n";

   // Loop through players table and process each player's daily scores
   $players = array();
   $result = mysql_query('select * from spelunky_players order by players_id desc');
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
               . $the_player->hashtag . " "
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

   // Determine winner and tweet if necessary
   // TODO: This needs work! Ugly
   if (count($players) == 2) {
      $player1 = $players[0];
      $player2 = $players[1];

      create_winner_image($player1, $player2);

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
