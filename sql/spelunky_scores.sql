CREATE TABLE spelunky_scores (
   scores_id         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   scores_players_id BIGINT NOT NULL,
   scores_date       DATE NOT NULL,
   scores_score      INT NOT NULL,
   scores_level      CHAR(4) NOT NULL,
   scores_character  INT NOT NULL,
   scores_twitch     VARCHAR(100),
   FOREIGN KEY (scores_players_id) REFERENCES spelunky_players (players_id)
);

CREATE INDEX scores_index ON spelunky_scores (scores_players_id, scores_date);
