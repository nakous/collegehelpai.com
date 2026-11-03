<?php

/**
 * Template Name: Single Stripe Plan
 * Template Post Type: stripe_plan
 */
get_header();

// Plan IDs based on environment
if (wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development') {
    // Local environment IDs
    $id_yearly = 503;   // ID for yearly plan (local)
    $id_monthly = 380;  // ID for monthly plan (local)
} else {
    // Production environment IDs
    $id_yearly = 534;   // ID for yearly plan (production)
    $id_monthly = 533;  // ID for monthly plan (production)
}

// Get the current plan
$current_plan = get_post();
$current_plan_id = $current_plan->ID;

// Determine which plan we're viewing and set the active toggle state
if ($current_plan_id == $id_yearly) {
    $active_plan = 'yearly';
    $plan_id = $id_yearly;
} else {
    $active_plan = 'monthly';
    $plan_id = $id_monthly;
}

// If this is neither the monthly nor yearly plan, redirect to yearly plan
if ($current_plan_id != $id_yearly && $current_plan_id != $id_monthly) {
    wp_redirect(add_query_arg('from_other_plan', 'true', get_permalink($id_yearly)));
    exit;
}

// Restore the original post to maintain template tags
$post = $current_plan;
setup_postdata($post);

// Get plan details - adding safeguards for empty values
$price = get_post_meta($plan_id, '_price_amount', true);
$price = !empty($price) ? (float)$price : 0;

$currency = get_post_meta($plan_id, '_price_currency', true) ?: 'USD';
$billing_interval = get_post_meta($plan_id, '_billing_interval', true);
$interval_count = get_post_meta($plan_id, '_interval_count', true) ?: 1;
$features = get_post_meta($plan_id, '_features', true);

// Function to get currency symbol
function get_currency_symbol($currency_code)
{
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'CAD' => 'CA$',
        'AUD' => 'A$'
    ];
    return $symbols[strtoupper($currency_code)] ?? $currency_code;
}

$currency_symbol = get_currency_symbol($currency);

// Get discount if available
$discount_id = get_post_meta($plan_id, '_discount_id', true);
$discount = null;
$discounted_price = $price;
$discount_badge = '';
$discount_duration_text = '';

if (!empty($discount_id)) {
    try {
        // Get Stripe feature and API
        $stripe_feature = \CobraAI\cobra_ai()->get_feature('stripesubscriptions');
        if ($stripe_feature && method_exists($stripe_feature, 'get_api')) {
            $api = $stripe_feature->get_api();
            if ($api && method_exists($api, 'get_discount')) {
                $discount = $api->get_discount($discount_id);
                if ($discount) {
                    // Calculate discounted price
                    $discount_percent = 0;
                    if (!empty($discount['percent_off'])) {
                        $discount_percent = $discount['percent_off'];
                        $discounted_price = $price * (1 - ($discount_percent / 100));
                        $discount_badge = '<div class="discount-badge"><span class="badge-text">-' . esc_html($discount_percent) . '%</span><span class="badge-subtext">OFF</span></div>';
                    } elseif (!empty($discount['amount_off'])) {
                        // Convert amount_off (in cents) to same unit as price
                        $amount_off = $discount['amount_off'] / 100;
                        $discounted_price = max(0, $price - $amount_off);
                        $discount_percent = round(($amount_off / $price) * 100);
                        $discount_badge = '<div class="discount-badge"><span class="badge-text">-' . esc_html($discount_percent) . '%</span><span class="badge-subtext">OFF</span></div>';
                    }

                    // Generate duration message
                    if (!empty($discount['duration'])) {
                        if ($discount['duration'] === 'once') {
                            $discount_duration_text = '<div class="promo-duration">🎁 <strong>First ' . ($billing_interval === 'month' ? 'month' : 'year') . ' only</strong> - then ' . $currency_symbol . number_format($price, 2) . $period_text . '</div>';
                        } elseif ($discount['duration'] === 'repeating' && !empty($discount['duration_months'])) {
                            $months = $discount['duration_months'];
                            $discount_duration_text = '<div class="promo-duration">🎁 <strong>First ' . $months . ' months</strong> at this price - then ' . $currency_symbol . number_format($price, 2) . $period_text . '</div>';
                        } elseif ($discount['duration'] === 'forever') {
                            $discount_duration_text = '<div class="promo-duration promo-forever">🔥 <strong>Lifetime discount!</strong> This price forever!</div>';
                        }
                    }
                }
            }
        }
    } catch (\Exception $e) {
        error_log('Error retrieving discount: ' . $e->getMessage());
    }
}

// Format display values
$period_text = ($billing_interval === 'month') ? '/month' : '/year';
if ($interval_count > 1) {
    $period_text = "/{$interval_count} " . ($billing_interval === 'month' ? 'months' : 'years');
}

$save_badge = '';
?>

<!-- Subscription Plan Section -->
<section class="pricing single-plan">
    <div class="container px-5">
        <div class="section-title">
            <h2>Choose Your Subscription Plan</h2>
            <!-- <p>Get unlimited access to College Help AI with our flexible pricing options</p> -->
        </div>

        <!-- Plan Period Toggle Switch -->
        <div class="plan-toggle-container">
            <div class="plan-toggle">
                <span class="plan-period <?php echo ($active_plan === 'monthly') ? 'active' : ''; ?>">Monthly</span>
                <label class="switch">
                    <input type="checkbox" id="planToggle"
                        <?php echo ($active_plan === 'yearly') ? 'checked' : ''; ?>
                        data-yearly-url="<?php echo get_permalink($id_yearly); ?>"
                        data-monthly-url="<?php echo get_permalink($id_monthly); ?>">
                    <span class="slider round"></span>
                </label>
                <span class="plan-period <?php echo ($active_plan === 'yearly') ? 'active' : ''; ?>">Annual</span>
            </div>
        </div>

        <div class="product-layout">
            <div class="product-image">
                <h1 class="product-title"><?php echo get_the_title($plan_id); ?></h1>
                <div class="product-description">
                    <?php echo get_post_field('post_content', $plan_id); ?>
                </div>
                <div class="product-features">
                    <h3>Features Include:</h3>
                    <ul>
                        <?php if (is_array($features) && !empty($features)) : ?>
                            <?php foreach ($features as $feature) : ?>
                                <?php if (!empty($feature)) : ?>
                                    <li><?php echo esc_html($feature); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php if (has_post_thumbnail($plan_id)) : ?>
                    <?php echo get_the_post_thumbnail($plan_id, 'large', array('class' => 'main-plan-image')); ?>
                <?php endif; ?>
            </div>

            <div class="product-details">
                <?php echo $save_badge; ?>
                <?php echo $discount_badge; ?>

                <div class="product-price">
                    <?php if ($discount): ?>
                        <div class="price-with-discount">
                            <div class="promo-header">
                                <span class="promo-label">🎉 LIMITED TIME OFFER</span>
                            </div>
                            <div class="price-comparison">
                                <div class="original-price">
                                    <span class="label-text">Regular Price:</span>
                                    <span class="price-currency"><?php echo esc_html($currency_symbol); ?></span>
                                    <span class="price-amount"><?php echo number_format($price, 2); ?></span>
                                    <span class="price-duration"><?php echo esc_html($period_text); ?></span>
                                </div>
                                <div class="discounted-price">
                                    <span class="label-text">Your Price:</span>
                                    <span class="price-currency"><?php echo esc_html($currency_symbol); ?></span>
                                    <span class="price-amount"><?php echo number_format($discounted_price, 2); ?></span>
                                    <span class="price-duration"><?php echo esc_html($period_text); ?></span>
                                </div>
                            </div>
                            <?php echo $discount_duration_text; ?>
                            <div class="savings-info">
                                <span class="savings-text">💰 You save: <?php echo $currency_symbol . number_format($price - $discounted_price, 2); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="price-normal">
                            <span class="price-currency"><?php echo esc_html($currency_symbol); ?></span>
                            <span class="price-amount"><?php echo number_format($price, 2); ?></span>
                            <span class="price-duration"><?php echo esc_html($period_text); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="subscription-button">
                    <?php echo do_shortcode('[stripe_action_subscription id="' . $plan_id . '"]'); ?>
                </div>

                <div class="payment-methods-container">
                    <div class="payment-methods">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/support-payment.png" alt="Supported Payment Methods">
                    </div>
                </div>



                <div class="terms-agreement">
                    <p>By clicking the button above, you agree to our
                        <a href="<?php echo get_permalink(get_option('cobra_terms_page')); ?>">Terms of Service</a>
                        and
                        <a href="<?php echo get_permalink(get_option('cobra_privacy_page')); ?>">Privacy Policy</a>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Methods Section -->

    </div>
</section>



<?php get_footer(); ?>