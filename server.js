const express = require('express');
const Stripe = require('stripe');
const { Pool } = require('pg');
const cors = require('cors');

const app = express();
const stripe = Stripe(process.env.STRIPE_SECRET_KEY);
const pool = new Pool({ connectionString: process.env.DATABASE_URL });

// Parse JSON for all routes except webhook
app.use((req, res, next) => {
  if (req.originalUrl === '/webhook') {
    next();
  } else {
    express.json()(req, res, next);
  }
});

app.use(cors({ origin: ['https://molekvm.com', 'http://localhost'] }));

// ═══ HEALTH CHECK ═══
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// ═══ CREATE PAYMENT INTENT (pre-order) ═══
app.post('/create-payment-intent', async (req, res) => {
  try {
    const { plan, email, name, country } = req.body;

    const prices = {
      device: 9900,   // €99.00
      pro: 9900,      // €99.00 device + subscription created separately
      fleet: 7900,    // €79.00 per device
    };

    const amount = prices[plan];
    if (!amount) return res.status(400).json({ error: 'Invalid plan' });

    // Create or retrieve Stripe customer
    let customer;
    const existing = await stripe.customers.list({ email, limit: 1 });
    if (existing.data.length > 0) {
      customer = existing.data[0];
    } else {
      customer = await stripe.customers.create({
        email,
        name,
        metadata: { country, plan, source: 'molekvm-landing' }
      });
    }

    // Create PaymentIntent
    const paymentIntent = await stripe.paymentIntents.create({
      amount,
      currency: 'eur',
      customer: customer.id,
      metadata: {
        plan,
        country,
        product: 'molekvm-preorder'
      },
      receipt_email: email,
      description: `MoleKVM Pre-order — ${plan} plan`,
      // Capture later (charge at shipment)
      capture_method: 'manual',
    });

    // Save order to database
    await pool.query(
      `INSERT INTO orders (stripe_customer_id, stripe_pi_id, email, name, country, plan, amount_eur, status)
       VALUES ($1, $2, $3, $4, $5, $6, $7, 'pending')`,
      [customer.id, paymentIntent.id, email, name, country, plan, amount / 100]
    );

    res.json({ clientSecret: paymentIntent.client_secret });
  } catch (err) {
    console.error('Payment intent error:', err);
    res.status(500).json({ error: err.message });
  }
});

// ═══ CREATE SUBSCRIPTION (for AI Pro plan) ═══
app.post('/create-subscription', async (req, res) => {
  try {
    const { customerId, priceId } = req.body;

    const subscription = await stripe.subscriptions.create({
      customer: customerId,
      items: [{ price: priceId || process.env.STRIPE_PRICE_SUB_PRO }],
      trial_period_days: 30, // First month free
      payment_behavior: 'default_incomplete',
      expand: ['latest_invoice.payment_intent'],
    });

    res.json({
      subscriptionId: subscription.id,
      clientSecret: subscription.latest_invoice.payment_intent.client_secret,
    });
  } catch (err) {
    console.error('Subscription error:', err);
    res.status(500).json({ error: err.message });
  }
});

// ═══ KICKSTARTER NOTIFICATION SIGNUP ═══
app.post('/kickstarter-notify', async (req, res) => {
  try {
    const { email } = req.body;
    if (!email) return res.status(400).json({ error: 'Email required' });

    await pool.query(
      `INSERT INTO kickstarter_signups (email, signed_up_at)
       VALUES ($1, NOW())
       ON CONFLICT (email) DO NOTHING`,
      [email]
    );

    res.json({ success: true });
  } catch (err) {
    console.error('Signup error:', err);
    res.status(500).json({ error: err.message });
  }
});

// ═══ STRIPE WEBHOOK ═══
app.post('/webhook', express.raw({ type: 'application/json' }), async (req, res) => {
  const sig = req.headers['stripe-signature'];
  let event;

  try {
    event = stripe.webhooks.constructEvent(
      req.body,
      sig,
      process.env.STRIPE_WEBHOOK_SECRET
    );
  } catch (err) {
    console.error('Webhook signature error:', err.message);
    return res.status(400).send(`Webhook Error: ${err.message}`);
  }

  switch (event.type) {
    case 'payment_intent.succeeded':
      const pi = event.data.object;
      await pool.query(
        `UPDATE orders SET status = 'paid', paid_at = NOW() WHERE stripe_pi_id = $1`,
        [pi.id]
      );
      console.log(`✅ Payment succeeded: ${pi.id} (${pi.amount / 100} EUR)`);
      // TODO: Send confirmation email via SendGrid
      break;

    case 'payment_intent.payment_failed':
      const failed = event.data.object;
      await pool.query(
        `UPDATE orders SET status = 'failed' WHERE stripe_pi_id = $1`,
        [failed.id]
      );
      console.log(`❌ Payment failed: ${failed.id}`);
      break;

    case 'customer.subscription.created':
      const sub = event.data.object;
      await pool.query(
        `INSERT INTO subscriptions (stripe_customer_id, stripe_sub_id, plan, status, current_period_end)
         VALUES ($1, $2, 'ai_pro', $3, to_timestamp($4))`,
        [sub.customer, sub.id, sub.status, sub.current_period_end]
      );
      break;

    case 'invoice.payment_succeeded':
      // Subscription renewal
      const invoice = event.data.object;
      if (invoice.subscription) {
        await pool.query(
          `UPDATE subscriptions SET status = 'active', current_period_end = to_timestamp($1)
           WHERE stripe_sub_id = $2`,
          [invoice.lines.data[0]?.period?.end, invoice.subscription]
        );
      }
      break;

    default:
      console.log(`Unhandled event: ${event.type}`);
  }

  res.json({ received: true });
});

// ═══ ADMIN: ORDER STATS ═══
app.get('/admin/stats', async (req, res) => {
  // TODO: Add auth middleware
  try {
    const orders = await pool.query(`
      SELECT
        COUNT(*) as total_orders,
        COUNT(*) FILTER (WHERE status = 'paid') as paid_orders,
        SUM(amount_eur) FILTER (WHERE status = 'paid') as total_revenue,
        COUNT(DISTINCT email) as unique_customers,
        COUNT(*) FILTER (WHERE plan = 'pro') as pro_orders,
        COUNT(*) FILTER (WHERE plan = 'device') as device_orders,
        COUNT(*) FILTER (WHERE plan = 'fleet') as fleet_orders
      FROM orders
    `);

    const subs = await pool.query(`
      SELECT COUNT(*) as active_subs,
             COUNT(*) * 9.99 as mrr
      FROM subscriptions WHERE status = 'active'
    `);

    const signups = await pool.query(`SELECT COUNT(*) as total FROM kickstarter_signups`);

    res.json({
      orders: orders.rows[0],
      subscriptions: subs.rows[0],
      kickstarter_signups: signups.rows[0].total
    });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🐾 MoleKVM API running on port ${PORT}`);
});
