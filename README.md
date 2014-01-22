# KlepekVsRemo
*A twitter bot that tweets the results of Chris Remo and Patrick Klepek's Spelunky grudge matches to [@KlepekVsRemo](https://twitter.com/KlepekVsRemo)*

Utilizes the [CodeBird Twitter Library](https://github.com/jublonet/codebird-php), and the text stroke function for PHP 
images was lifted from [here](http://www.johnciacia.com/2010/01/04/using-php-and-gd-to-add-border-to-text/).

This bot runs on a cron every 10 minutes. It investigates the Steam leaderboards for Spelunky Daily Challenges on the date 
it runs. If it finds Chris Remo or Patrick Klepek's score they will be retrieved and stored in a database. It will then 
tweet their results as well. If both players have completed the Daily Challenge for that day, it will also determine who 
had the highest score and tweet the winner. 

I'm not sure why I picked PHP other than I knew how to use it more than anything else that would perform the tasks I 
wanted to perform. Should probably be done in something more elegant, but there it is. I don't have a lot of the version 
history up on GitHub, but from here on out I should. Still needs a lot of cleanup. Speaking of which, the tracking of 
tweets via flat files filled with the date is dumb and should be removed at some point. Cheap hack.

**Things it does:**
 * Gets score and level data for a Daily Challenge run
 * Attempts to find the corresponding Twitch.tv video URL for inclusion in the tweet
 * Builds a daily image "badge" that includes various run information
 * Tweets individual run data (including the badge)
 * Tweets the winner

**TODO:**
 * Fix checking for tweet via flat file, investigate twitter feed instead
 * Move functions to a different file
 * Move class to a different file
 * Clean up "determine winner" logic
 * Fix Twitch function so that timezone difference won't be a problem
 * Comment more thoroughly
 * Probably more?

#### Sample Badge

![alt-text](https://raw2.github.com/amarriner/KlepekVsRemo/da6e8f44e277b6b53f21cdd38fa5a0dc5334e070/images/daily_76561197969204208.png "Sample Badge")
