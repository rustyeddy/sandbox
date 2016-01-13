--
-- Some usefule queries so I do not forget them
--

select id, domain, company_name, phone, keyword, report, score from ppcprospector order by company_name;

--
-- list all of the unique user and post meta keys
--
select distinct meta_key from wp_usermeta;
select distinct meta_key from wp_postmeta;

--
-- Determine what type of post types there are
--
select distinct post_type from wp_posts;

-- Select all of the mini_audit post types
select ID, post_title from wp_posts where post_type = 'mini_audit';

--
-- Select the meta data from wordpress posts of a specific type
--
select wp_postmeta.meta_key, wp_postmeta.meta_value, wp_posts.post_title 
 from wp_posts
 inner join wp_postmeta
 on wp_postmeta.post_id = wp_posts.ID
 where wp_posts.post_type = 'miniaudit';

