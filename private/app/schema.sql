CREATE TABLE IF NOT EXISTS rb_records (
 id VARCHAR(160) NOT NULL PRIMARY KEY,
 kind VARCHAR(30) NOT NULL,
 parent_id VARCHAR(80) NOT NULL DEFAULT '',
 owner_hash VARCHAR(64) NOT NULL DEFAULT '',
 data_json LONGTEXT NOT NULL,
 version BIGINT NOT NULL DEFAULT 1,
 created_at BIGINT NOT NULL,
 updated_at BIGINT NOT NULL,
 expires_at BIGINT NULL,
 INDEX rb_kind_parent(kind,parent_id),
 INDEX rb_owner(kind,owner_hash),
 INDEX rb_expiry(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
