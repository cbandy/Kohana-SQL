
/**
 * Schema required for PostgreSQL tests.
 *
 * @package      SQL
 * @subpackage   PostgreSQL
 */

CREATE TABLE kohana_test_table (
    id bigserial PRIMARY KEY,
    value integer
);
