
/**
 * Schema required for MySQL tests.
 *
 * @package      SQL
 * @subpackage   MySQL
 */

CREATE TABLE kohana_test_table (
    id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
    value integer
)

-- Required for transaction support
ENGINE = InnoDB;
