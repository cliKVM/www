-- MoleKVM order database schema

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    stripe_customer_id VARCHAR(255),
    stripe_pi_id VARCHAR(255) UNIQUE,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    country VARCHAR(10),
    plan VARCHAR(50) NOT NULL,
    amount_eur DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT NOW(),
    paid_at TIMESTAMP,
    shipped_at TIMESTAMP,
    tracking_number VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS subscriptions (
    id SERIAL PRIMARY KEY,
    stripe_customer_id VARCHAR(255) NOT NULL,
    stripe_sub_id VARCHAR(255) UNIQUE,
    plan VARCHAR(50) DEFAULT 'ai_pro',
    status VARCHAR(50) DEFAULT 'incomplete',
    current_period_end TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS kickstarter_signups (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    signed_up_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_orders_email ON orders(email);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_subs_customer ON subscriptions(stripe_customer_id);
