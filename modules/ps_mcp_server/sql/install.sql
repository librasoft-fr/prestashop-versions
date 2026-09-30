CREATE TABLE IF NOT EXISTS `PREFIX_mcp_server_features`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_id`        INT(10) UNSIGNED NOT NULL,
    `type`             ENUM('tool', 'prompt', 'resource', 'resourceTemplate') NOT NULL,
    `name`             VARCHAR(255)     NOT NULL,
    `description`      TEXT             NULL,
    `category`         VARCHAR(255)     NOT NULL DEFAULT 'general',
    `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`       DATETIME         NOT NULL,
    `updated_at`       DATETIME         NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `feature_name_type` (`name`, `type`)
) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_mcp_server_modules`
(
    `module_id`        INT(10) UNSIGNED NOT NULL,
    `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`       DATETIME         NOT NULL,
    PRIMARY KEY (`module_id`)
) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_mcp_server_allowed_users`
(
    `email`            VARCHAR(255)     NOT NULL,
    `role`             ENUM('editor', 'viewer') NOT NULL DEFAULT 'viewer',
    `token_hash`       VARCHAR(255)     DEFAULT NULL,
    `created_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`email`)
) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8mb4;
