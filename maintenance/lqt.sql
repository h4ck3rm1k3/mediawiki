CREATE TABLE /*$wgDBprefix*/thread (
  thread_id int(8) unsigned NOT NULL auto_increment,
  thread_root int(8) unsigned UNIQUE NOT NULL,
  thread_root_rev int(8) unsigned NOT NULL default 0,
  thread_article int(8) unsigned NOT NULL default 0,
  thread_path text NOT NULL,
  thread_summary_page int(8) unsigned NULL,
  thread_timestamp char(14) binary NOT NULL default '',
  thread_revision int(8) unsigned NOT NULL default 1,

  -- The following are used only for non-existant article where
  -- thread_article = 0. They should be ignored if thread_article != 0.
  thread_article_namespace int NULL,
  thread_article_title varchar(255) binary NULL,

  PRIMARY KEY thread_id (thread_id),
  UNIQUE INDEX thread_id (thread_id),
  INDEX thread_article (thread_article),
  INDEX thread_article_title (thread_article_namespace, thread_article_title),
  INDEX( thread_path(255) ),
  INDEX thread_timestamp (thread_timestamp)
) TYPE=InnoDB;

CREATE TABLE /*$wgDBprefix*/historical_thread (
  -- Note that many hthreads can share an id, which is the same as the id
  -- of the live thread. It is only the id/revision combo which must be unique.
  hthread_id int(8) unsigned NOT NULL,
  hthread_revision int(8) unsigned NOT NULL,
  hthread_contents TEXT NOT NULL,
  PRIMARY KEY hthread_id_revision (hthread_id, hthread_revision)
) TYPE=InnoDB;

-- Because hthreads are only stored one per root, this lists
-- the subthreads that can be found within each one.
CREATE TABLE /*$wgDBprefix*/hthread_contents (
  htcontents_child int(8) unsigned NOT NULL,
  htcontents_hthread int(8) unsigned NOT NULL,
  htcontents_rev_start int(8) unsigned NOT NULL,
  htcontents_rev_end int(8) unsigned NULL
) TYPE=InnoDB;

/*
	old_superthread and old_article are mutually exclusive.
	New position is recorded either in the text movement or in the
	thread's current information.
*/
CREATE TABLE /*$wgDBprefix*/lqt_movement (
  movement_id int(8) unsigned NOT NULL auto_increment,
  
  movement_thread int(8) unsigned NOT NULL,

  movement_old_superthread int(8) unsigned NULL,
  movement_old_article int(8) unsigned NULL,

  movement_timestamp char(14) binary NOT NULL default '',

  PRIMARY KEY movement_id (movement_id)
  /* TODO we will need an index to look up my article and timestamp. */

) TYPE=InnoDB;

