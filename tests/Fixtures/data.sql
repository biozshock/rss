DROP TABLE IF EXISTS `Feed`;
CREATE TABLE IF NOT EXISTS `Feed` (
  `id` INTEGER PRIMARY KEY,
  `source` text NOT NULL,
  `link` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `title` text DEFAULT NULL,
  `published_date` datetime DEFAULT NULL,
  `last_fetched` datetime DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL
) ;

DROP TABLE IF EXISTS `Record`;
CREATE TABLE IF NOT EXISTS `Record` (
  `id` INTEGER PRIMARY KEY,
  `feed_id` int(10) NOT NULL,
  `title` text NOT NULL,
  `content` text DEFAULT NULL,
  `picture` text DEFAULT NULL,
  `author` text DEFAULT NULL,
  `link` text DEFAULT NULL,
  `guid` text DEFAULT NULL,
  `publication_date` datetime NOT NULL,
  `tags` text DEFAULT NULL
) ;
