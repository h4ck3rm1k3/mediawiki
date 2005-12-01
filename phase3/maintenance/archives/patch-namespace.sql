-- New namespace system

DROP TABLE IF EXISTS /*$wgDBprefix*/namespace;
CREATE TABLE /*$wgDBprefix*/namespace (
  `ns_id` int(8) NOT NULL default '0',
  `ns_system` varchar(80) default '0',
  `ns_subpages` tinyint(1) NOT NULL default '0',
  `ns_search_default` tinyint(1) NOT NULL default '0',
  `ns_target` varchar(200) default NULL,
  `ns_parent` int(8) default NULL,
  `ns_hidden` tinyint(1) default NULL,
  PRIMARY KEY  (`ns_id`)
);
