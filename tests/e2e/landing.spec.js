const { test, expect } = require('@playwright/test');

/**
 * MoleKVM Landing Page E2E Tests
 * Tests core functionality of the landing page
 */

test.describe('Landing Page', () => {
  
  test('page loads with correct title', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/MoleKVM/);
  });

  test('hero section is visible', async ({ page }) => {
    await page.goto('/');
    
    // Check main headline
    const heroHeading = page.locator('h1');
    await expect(heroHeading).toBeVisible();
    await expect(heroHeading).toContainText('AI mole');
    
    // Check CTA buttons
    const ctaButton = page.locator('.btn-primary').first();
    await expect(ctaButton).toBeVisible();
    await expect(ctaButton).toContainText('Pre-order');
  });

  test('navigation links work', async ({ page }) => {
    await page.goto('/');
    
    // Check nav links
    const navLinks = ['How it works', 'Use Cases', 'Pricing', 'FAQ'];
    for (const link of navLinks) {
      const navLink = page.locator('.nav-links').getByText(link);
      await expect(navLink).toBeVisible();
    }
  });

  test('pricing section displays three tiers', async ({ page }) => {
    await page.goto('/');
    
    // Scroll to pricing
    await page.click('text=Pricing');
    
    // Check pricing cards
    const priceCards = page.locator('.price-card');
    await expect(priceCards).toHaveCount(3);
    
    // Check prices are visible
    const prices = page.locator('.price-amount');
    await expect(prices.first()).toContainText('€');
  });

  test('FAQ accordion works', async ({ page }) => {
    await page.goto('/');
    
    // Scroll to FAQ
    await page.click('text=FAQ');
    
    // Find first FAQ item
    const firstFaq = page.locator('.faq-item').first();
    const faqQuestion = firstFaq.locator('.faq-q');
    
    // Click to open
    await faqQuestion.click();
    
    // Check answer is visible
    const faqAnswer = firstFaq.locator('.faq-a');
    await expect(faqAnswer).toBeVisible();
  });

  test('checkout modal opens', async ({ page }) => {
    await page.goto('/');
    
    // Click on first pre-order button
    await page.locator('.price-btn.primary').first().click();
    
    // Check modal is visible
    const modal = page.locator('#checkout-modal');
    await expect(modal).toHaveClass(/active/);
    
    // Check form elements
    await expect(page.locator('#cust-email')).toBeVisible();
    await expect(page.locator('#cust-name')).toBeVisible();
    await expect(page.locator('#stripe-card')).toBeVisible();
  });

  test('modal closes on overlay click', async ({ page }) => {
    await page.goto('/');
    
    // Open modal
    await page.locator('.price-btn.primary').first().click();
    
    // Click overlay to close
    const overlay = page.locator('.modal-overlay');
    await overlay.click({ position: { x: 10, y: 10 } });
    
    // Check modal is closed
    await expect(overlay).not.toHaveClass(/active/);
  });

  test('smooth scroll to sections', async ({ page }) => {
    await page.goto('/');
    
    // Click "See how it works" button
    await page.click('text=See how it works');
    
    // Wait for scroll
    await page.waitForTimeout(500);
    
    // Check URL has hash
    await expect(page).toHaveURL(/#how/);
  });

  test('stats are visible in hero', async ({ page }) => {
    await page.goto('/');
    
    const stats = page.locator('.hero-stat');
    await expect(stats).toHaveCount(4);
    
    // Check specific stats
    await expect(page.locator('.hero-stat').filter({ hasText: '21mm' })).toBeVisible();
    await expect(page.locator('.hero-stat').filter({ hasText: '€49' }).or(
      page.locator('.hero-stat').filter({ hasText: '€99' })
    )).toBeVisible();
  });

  test('responsive navigation on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    
    // On mobile, nav links should be hidden
    const navLinks = page.locator('.nav-links a');
    await expect(navLinks).toHaveCount(0);
    
    // Logo should still be visible
    await expect(page.locator('.nav-logo')).toBeVisible();
  });

  test('kickstarter section is present', async ({ page }) => {
    await page.goto('/');
    
    // Scroll to kickstarter
    await page.click('text=Kickstarter');
    
    // Check kickstarter badge
    const kickstarter = page.locator('.kickstarter');
    await expect(kickstarter).toBeVisible();
    await expect(kickstarter).toContainText('Kickstarter');
  });

  test('footer links are present', async ({ page }) => {
    await page.goto('/');
    
    // Check footer
    const footer = page.locator('footer');
    await expect(footer).toBeVisible();
    
    // Check GitHub link
    await expect(footer.getByText('GitHub')).toBeVisible();
    
    // Check copyright
    await expect(footer).toContainText('2026');
  });
});

test.describe('Checkout Flow', () => {
  
  test('form validation - empty fields', async ({ page }) => {
    await page.goto('/');
    
    // Open checkout
    await page.locator('.price-btn.primary').first().click();
    
    // Try to submit empty form
    await page.click('#pay-btn');
    
    // Check for validation message
    await expect(page.locator('#pay-btn')).toContainText('Please fill');
  });

  test('price updates based on selection', async ({ page }) => {
    await page.goto('/');
    
    // Open checkout for different plans
    const buttons = await page.locator('.price-btn.primary').all();
    
    for (const button of buttons.slice(0, 2)) {
      await button.click();
      
      // Check price is shown
      const price = page.locator('#modal-price');
      await expect(price).toContainText('€');
      
      // Close modal
      await page.click('.modal-close');
    }
  });
});

test.describe('Accessibility', () => {
  
  test('has proper heading structure', async ({ page }) => {
    await page.goto('/');
    
    // Check h1 exists
    const h1 = page.locator('h1');
    await expect(h1).toHaveCount(1);
    
    // Check multiple h2 sections
    const h2s = page.locator('h2');
    await expect(await h2s.count()).toBeGreaterThan(3);
  });

  test('images have alt text', async ({ page }) => {
    await page.goto('/');
    
    // Check all images
    const images = page.locator('img');
    const count = await images.count();
    
    for (let i = 0; i < count; i++) {
      const alt = await images.nth(i).getAttribute('alt');
      // Allow empty alt for decorative images
      expect(alt).not.toBeNull();
    }
  });

  test('interactive elements have focus states', async ({ page }) => {
    await page.goto('/');
    
    // Tab to first button
    await page.keyboard.press('Tab');
    
    // Check some element is focused
    const focused = page.locator(':focus');
    await expect(focused).toBeVisible();
  });
});
