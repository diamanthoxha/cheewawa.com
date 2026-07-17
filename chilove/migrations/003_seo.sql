-- Per-article SEO meta description.
ALTER TABLE chi_posts ADD COLUMN meta_description VARCHAR(255) NULL AFTER excerpt;
