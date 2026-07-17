-- ChiLove schema (WordPress-like, simplified).

CREATE TABLE IF NOT EXISTS chi_users (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(190) NOT NULL,
    email        VARCHAR(190) NOT NULL UNIQUE,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS chi_terms (              -- categories / tags
    id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(190) NOT NULL,
    slug     VARCHAR(190) NOT NULL UNIQUE,
    taxonomy VARCHAR(50)  NOT NULL DEFAULT 'category'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS chi_posts (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id      BIGINT UNSIGNED NULL,
    title          VARCHAR(255) NOT NULL,
    slug           VARCHAR(255) NOT NULL UNIQUE,
    excerpt        TEXT NULL,
    content        MEDIUMTEXT NULL,
    featured_image VARCHAR(255) NULL,
    read_time      SMALLINT UNSIGNED NULL,
    status         VARCHAR(20) NOT NULL DEFAULT 'publish',
    published_at   DATETIME NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_author FOREIGN KEY (author_id)
        REFERENCES chi_users (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS chi_post_terms (
    post_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, term_id),
    CONSTRAINT fk_pt_post FOREIGN KEY (post_id)
        REFERENCES chi_posts (id) ON DELETE CASCADE,
    CONSTRAINT fk_pt_term FOREIGN KEY (term_id)
        REFERENCES chi_terms (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS chi_subscribers (        -- "Join the Pack"
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
