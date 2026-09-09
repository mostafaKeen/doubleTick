-- Database Schema for DoubleTick WhatsApp Bitrix24 Local Application
-- Compatible with SQLite and MySQL

CREATE TABLE IF NOT EXISTS b24_portals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id VARCHAR(64) NOT NULL UNIQUE,
    domain VARCHAR(255) NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    expires_at INTEGER NOT NULL,
    client_endpoint VARCHAR(255) NOT NULL,
    server_endpoint VARCHAR(255) DEFAULT 'https://oauth.bitrix.info/rest/',
    application_token VARCHAR(64) DEFAULT NULL,
    dt_api_key VARCHAR(255) DEFAULT NULL,
    dt_waba_number VARCHAR(32) DEFAULT NULL,
    open_line_id INTEGER DEFAULT NULL,
    is_active INTEGER DEFAULT 1,
    installed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS message_mappings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    portal_id INTEGER NOT NULL,
    b24_chat_id INTEGER NOT NULL,
    b24_message_id INTEGER NOT NULL,
    dt_message_id VARCHAR(128) NOT NULL,
    whatsapp_message_id VARCHAR(128) DEFAULT NULL,
    customer_phone VARCHAR(32) NOT NULL,
    direction VARCHAR(16) NOT NULL, -- 'INBOUND' or 'OUTBOUND'
    message_type VARCHAR(32) DEFAULT 'text',
    status VARCHAR(32) DEFAULT 'sent',
    raw_data TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_msg_dt_id ON message_mappings (dt_message_id);
CREATE INDEX IF NOT EXISTS idx_msg_b24_id ON message_mappings (b24_chat_id, b24_message_id);
CREATE INDEX IF NOT EXISTS idx_msg_phone ON message_mappings (customer_phone);

CREATE TABLE IF NOT EXISTS lead_attributions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    portal_id INTEGER NOT NULL,
    lead_id INTEGER DEFAULT NULL,
    customer_phone VARCHAR(32) NOT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,
    is_ctwa INTEGER DEFAULT 0,
    source_url TEXT DEFAULT NULL,
    source_id VARCHAR(128) DEFAULT NULL,
    headline VARCHAR(255) DEFAULT NULL,
    ctwa_clid VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_attr_phone ON lead_attributions (customer_phone);

CREATE TABLE IF NOT EXISTS webhook_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source VARCHAR(32) NOT NULL, -- 'DOUBLETICK' or 'BITRIX24'
    event_type VARCHAR(64) NOT NULL,
    payload TEXT,
    response TEXT DEFAULT NULL,
    status_code INTEGER DEFAULT 200,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
