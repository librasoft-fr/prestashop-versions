-- Create psshipping_address table
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_psshipping_address` (
    `id_address` INT AUTO_INCREMENT NOT NULL,
    `pickup_point_id` INT NOT NULL,
    `network_code` VARCHAR(20) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `address` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `zip_code` VARCHAR(255) NOT NULL,
    `department` VARCHAR(255) DEFAULT NULL,
    `country` VARCHAR(255) NOT NULL,
    UNIQUE INDEX UNIQ_ED8CCBFF682033F1 (`pickup_point_id`),
    PRIMARY KEY(`id_address`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Create psshipping_address_orders table
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_psshipping_address_orders` (
    `id_order` INT NOT NULL,
    `id_shop` INT NOT NULL,
    `id_address` INT NOT NULL,
    INDEX IDX_A5067A5CD3D3C6F1 (`id_address`),
    PRIMARY KEY(`id_order`, `id_shop`, `id_address`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
