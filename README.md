# KlepekVsRemo
### A twitter bot that tweets the results of Chris Remo and Patrick Klepek's Spelunky grudge matches
### https://twitter.com/KlepekVsRemo

This bot runs on a cron every 10 minutes. It investigates the Steam leaderboards for Spelunky Daily Challenges on the date 
it runs. If it finds Chris Remo or Patrick Klepek's score they will be retrieved and stored in a database. It will then 
tweet their results as well. If both players have completed the Daily Challenge for that day, it will also determine who 
had the highest score and tweet the winner. 

I'm not sure why I picked PHP other than I knew how to use it more than anything else that would perform the tasks I 
wanted to perform. Should probably be done in something more elegant, but there it is. I don't have a lot of the version 
history up on GitHub, but from here on out I should. Still needs a lot of cleanup.

Things it does:
 * Gets score and level data for a Daily Challenge run
 * Attempts to find the corresponding Twitch.tv video URL for inclusion in the tweet
 * Builds a daily image "badge" that includes various run information
 * Tweets individual run data (including the badge)
 * Tweets the winner
