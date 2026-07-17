-- ChiLove — contact messages table.

CREATE TABLE IF NOT EXISTS chi_messages (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(190) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    message    TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
