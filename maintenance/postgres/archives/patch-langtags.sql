--
-- patch-langtags.sql
-- Language tag support
-- 2007-06-02
--

CREATE TABLE /*$wgDBprefix*/langtags (
    language_id integer,
    prefix_id integer,
    preferred_id integer,

    tag_name varchar(255),
    display_name varchar(255),

    native_name varchar(255),
    english_name varchar(255),

    rfc4646 varchar(42),
    rfc4646_suppress varchar(4),
    rfc4646_added date,
    rfc4646_deprecated date,

    iso639 char(3),
    iso639_3 char(3),
    iso639_3_revision date,

    wikimedia_key varchar(15),

    is_rtl smallint,
    is_collection smallint,
    is_enabled smallint,
    is_private smallint,
    is_searchable smallint,
    tag_touched timestamptz
);

CREATE UNIQUE INDEX langtags_tag_enabled
 ON /*$wgDBprefix*/langtags (is_enabled,tag_name);
CREATE UNIQUE INDEX langtags_tag_name
 ON /*$wgDBprefix*/langtags (tag_name);
CREATE INDEX langtags_iso639_idx
 ON /*$wgDBprefix*/langtags (iso639);
CREATE INDEX langtags_rfc4646_idx 
 ON /*$wgDBprefix*/langtags (rfc4646);

CREATE TABLE /*$wgDBprefix*/langsets (
    language_id integer,
    group_name varchar(255)
);
CREATE UNIQUE INDEX langsets_language_id
  ON /*$wgDBprefix*/langsets (language_id,group_name);
CREATE INDEX group_name 
  ON /*$wgDBprefix*/langsets (group_name);

ALTER TABLE /*$wgDBprefix*/archive
 ADD COLUMN ar_language integer;
DROP INDEX archive_name_title_timestamp;
CREATE INDEX archive_name_title_timestamp 
  ON /*$wgDBprefix*/archive (ar_language, ar_namespace, ar_title, ar_timestamp)

ALTER TABLE /*$wgDBprefix*/page
  ADD COLUMN page_language integer;
DROP INDEX page_unique_name;
CREATE UNIQUE INDEX page_unique_name
  ON /*$wgDBprefix*/page (page_language, page_namespace, page_title);

ALTER TABLE /*$wgDBprefix*/pagelinks
  ADD COLUMN pl_language integer;
DROP INDEX pagelink_unique;
CREATE UNIQUE INDEX pagelink_unique
  ON /*$wgDBprefix*/pagelinks (pl_from, pl_namespace, pl_language, pl_title);

ALTER TABLE /*$wgDBprefix*/recentchanges
  ADD COLUMN rc_language integer;
DROP INDEX rc_namespace_title;
CREATE INDEX rc_namespace_title
  ON /*$wgDBprefix*/recentchanges (rc_namespace, rc_language, rc_title);

ALTER TABLE /*$wgDBprefix*/watchlist
  ADD COLUMN wl_language integer;
DROP INDEX wl_user_namespace_title;
CREATE UNIQUE INDEX wl_user_namespace_title
  ON /*$wgDBprefix*/watchlist (wl_namespace, wl_language, wl_title, wl_user);

ALTER TABLE /*$wgDBprefix*/categorylinks
  ADD COLUMN cl_language integer;
DROP INDEX cl_from;
DROP INDEX cl_sortkey; 
CREATE UNIQUE INDEX cl_from
  ON /*$wgDBprefix*/categorylinks (cl_from, cl_language, cl_to);
CREATE INDEX cl_sortkey
  ON /*$wgDBprefix*/categorylinks (cl_language, cl_to, cl_sortkey, cl_from);

ALTER TABLE /*$wgDBprefix*/templatelinks
  ADD COLUMN tl_language integer;
DROP INDEX templatelinks_unique;
CREATE UNIQUE INDEX templatelinks_unique
  ON /*$wgDBprefix*/templatelinks (tl_namespace, tl_language, tl_title, tl_from);

ALTER TABLE /*$wgDBprefix*/querycache
  ADD COLUMN qc_language integer;
