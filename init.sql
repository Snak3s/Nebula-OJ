CREATE TABLE IF NOT EXISTS `admin` (
  `aid` int(8) NOT NULL AUTO_INCREMENT,
  `account` char(50) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `announcement` (
  `sid` int(8) NOT NULL AUTO_INCREMENT,
  `time` char(50) NOT NULL,
  `account` char(50) CHARACTER SET utf8 DEFAULT NULL,
  `title` text CHARACTER SET utf8,
  `body` text CHARACTER SET utf8,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `comment` (
  `cid` int(8) NOT NULL AUTO_INCREMENT,
  `time` char(50) NOT NULL,
  `account` char(50) CHARACTER SET utf8 DEFAULT NULL,
  `body` text CHARACTER SET utf8,
  `postid` int(8) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `problems` (
  `pid` int(8) NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8 NOT NULL,
  `statement` text CHARACTER SET utf8,
  `input` text CHARACTER SET utf8,
  `output` text CHARACTER SET utf8,
  `sample` text CHARACTER SET utf8,
  `constraints` text CHARACTER SET utf8,
  `timelimit` int(11) DEFAULT NULL,
  `memorylimit` int(11) DEFAULT NULL,
  `accepted` int(11) DEFAULT NULL,
  `submit` int(11) DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `submission` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `account` char(50) NOT NULL,
  `program` text CHARACTER SET utf8,
  `result` text CHARACTER SET utf8,
  `time` text,
  `lang` text,
  PRIMARY KEY (`sid`),
  KEY `sid` (`sid`),
  KEY `sid_2` (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(8) NOT NULL AUTO_INCREMENT,
  `account` char(50) CHARACTER SET utf8 DEFAULT NULL,
  `password` char(32) NOT NULL,
  `sex` int(8) DEFAULT NULL,
  `motto` text CHARACTER SET utf8,
  `accepted` int(8) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
