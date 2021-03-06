# KlepekVsRemo
*A twitter bot that tweets the results of Chris Remo and Patrick Klepek's Spelunky grudge matches to [@KlepekVsRemo](https://twitter.com/KlepekVsRemo)*

Utilizes the [CodeBird Twitter Library](https://github.com/jublonet/codebird-php). The text stroke function for PHP 
images was lifted from [here](http://www.johnciacia.com/2010/01/04/using-php-and-gd-to-add-border-to-text/). Since the 
version of PHP I'm using doesn't include the native imageflip function, I grabbed one from [Stack 
Exchange](http://stackoverflow.com/questions/15811421/imageflip-in-php-is-undefined).

All images were pulled from the PC version of [Spelunky HD](http://spelunkyworld.com) using [this extraction 
tool](http://mossmouth.com/forums/index.php?topic=3637.0).

This bot runs on a cron every 10 minutes. It investigates the Steam leaderboards for Spelunky Daily Challenges on the date 
it runs. If it finds Chris Remo or Patrick Klepek's score they will be retrieved and stored in a database. It will then 
tweet their results as well. If both players have completed the Daily Challenge for that day, it will also determine who 
had the highest score and tweet the winner. It also builds a "winner image" with details of both runs, level backgrounds 
for the level they ended on, and the hashtag of the winner.

I'm not sure why I picked PHP other than I knew how to use it more than anything else that would perform the tasks I 
wanted to perform. I was mainly interested in learning about Steam API data and interfacing with Twitter and wasn't really 
ready to add a new langage on top of that. Should probably be done in something more elegant, but there it is. I don't 
have a lot of the version history up on GitHub, but from here on out I should. Also, no branching! Playing with fire, I 
know, but my flimsy justification is that I'm the only one working on this. The whole thing still needs a lot of cleanup 
in general.

**Things it does:**
 * Gets score and level data for a Daily Challenge run
 * Attempts to find the corresponding Twitch.tv video URL for inclusion in the tweet
 * Builds a daily image "badge" that includes various run information
 * Builds a "winner image" that includes information for both runs
 * Tweets individual run data (including the badge)
 * Tweets the winner

**TODO:**
 * Figure out why running from the shell on a cron causes errors because of the backslashes despite working correctly running it manually. It's running using wget at the moment which is kind of lame
 * Possibly pull steam avatars every time? Probably not worth doing, but putting it here in case
 * Fix checking for tweet via flat file, investigate twitter feed instead
 * ~~Move functions to a different file~~
 * ~~Move class to a different file~~
 * Clean up "determine winner" logic
 * Fix Twitch function so that timezone difference won't be a problem
 * ~~Clean up all hard-coded references to pwd~~
 * Comment more thoroughly
 * Probably more?

#### Sample Winner Image

![alt-text](https://pbs.twimg.com/media/BewzvNuCYAAFqKs.png:large "Sample Winner Image")

#### Sample Badge

![alt-text](https://pbs.twimg.com/media/BewzeNZCQAA-Ibc.png:large "Sample Badge")
