<?php
/*
Template Name: Front Page
*/
get_header(); ?>
<!-- Hero Section -->
<section class="hero" aria-labelledby="hero-heading">
    <div class="container">
        <div class="hero-content">
            <h1 id="hero-heading"><?php esc_html_e('CollegeHelp Smart Tools for Homework & Exams', 'cobra'); ?></h1>
            <p><?php esc_html_e('Study smarter with our Chrome extension. Solve homework, quizzes, and exams fast using simple tools designed for every college student.', 'cobra'); ?></p>
            <a href="<?php echo esc_url(get_option('cobra_chrome_extension')); ?>" 
               class="btn install-btn"
               aria-label="<?php esc_attr_e('Install Chrome extension - Opens Chrome Web Store', 'cobra'); ?>">
                <i class="fab fa-chrome" aria-hidden="true"></i>
                <span><?php esc_html_e('INSTALL NOW', 'cobra'); ?></span>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
        <div class="hero-image">
            <div class="hero-video">
                <iframe
                    width="560"
                    height="315"
                    src="https://www.youtube.com/embed/rwN6TT3y4uA"
                    title="<?php esc_attr_e('CollegeHelp AI demonstration video', 'cobra'); ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>
<!-- Video Demonstration Section -->
<section id="get-started" class="get-started" aria-labelledby="demo-heading">
    <div class="container">
        <div class="section-title reveal reveal-up">
            <h2 id="demo-heading"><?php esc_html_e('College Help in Action', 'cobra'); ?></h2>
            <p><?php esc_html_e('See how our extension solves complex problems instantly', 'cobra'); ?></p>
        </div>

        <div class="demo-showcase reveal reveal-up">
            <div class="device-mockup">
                <div class="device-frame">
                    <video autoplay loop muted playsinline class="device-screen"
                           aria-label="<?php esc_attr_e('Demonstration of CollegeHelp AI solving problems', 'cobra'); ?>">
                        <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/solve.webm" type="video/webm">
                        <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/solve.mp4" type="video/mp4">
                        <p><?php esc_html_e('Your browser does not support the video tag.', 'cobra'); ?></p>
                    </video>
                </div>
                <div class="device-shadow" aria-hidden="true"></div>
            </div>

            <div class="demo-features">
                <div class="demo-feature">
                    <div class="feature-icon" aria-hidden="true">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="feature-text">
                        <h3><?php esc_html_e('Instant Answers', 'cobra'); ?></h3>
                        <p><?php esc_html_e('Get solutions in seconds, not minutes', 'cobra'); ?></p>
                    </div>
                </div>

                <div class="demo-feature">
                    <div class="feature-icon" aria-hidden="true">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="feature-text">
                        <h3><?php esc_html_e('Step-by-Step Explanations', 'cobra'); ?></h3>
                        <p><?php esc_html_e('Understand the process, not just the answer', 'cobra'); ?></p>
                    </div>
                </div>

                <div class="demo-feature">
                    <div class="feature-icon" aria-hidden="true">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="feature-text">
                        <h3><?php esc_html_e('Learn While Solving', 'cobra'); ?></h3>
                        <p><?php esc_html_e('Improve your knowledge with each question', 'cobra'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-banner reveal reveal-up">
            <div class="cta-content">
                <h3><?php esc_html_e('Ready to transform your study experience?', 'cobra'); ?></h3>
                <a href="<?php echo esc_url(get_option('cobra_chrome_extension')); ?>" 
                   class="btn btn-cta"
                   aria-label="<?php esc_attr_e('Download College Help AI Chrome extension', 'cobra'); ?>">
                    <i class="fab fa-chrome" aria-hidden="true"></i>
                    <span><?php esc_html_e('Download College Help AI', 'cobra'); ?></span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section with Animation -->
<section id="features" class="features">
    <div class="container">
        <div class="section-title reveal reveal-up">
            <h2>Powerful Features</h2>
            <p>Tools designed to make your academic life easier</p>
        </div>

        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-card reveal reveal-left">
                <div class="feature-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <h3>Screenshot Questions</h3>
                <p>Capture any question on your screen and get instant answers in the same language, formatted beautifully for easy understanding.</p>
                <!-- This is where you would add your feature image -->
                <!-- <img src="<?php echo get_template_directory_uri(); ?>/assets/images/feature-screenshot.png" alt="Screenshot Feature" class="feature-image"> -->
                <video autoplay loop muted playsinline class="device-screen">
                    <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/take-screenshot.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <!-- Feature 2 -->
            <div class="feature-card reveal reveal-up delay-100">
                <div class="feature-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <h3>Right-Click Assistance</h3>
                <p>Simply select text and right-click to "Ask College Help AI" for immediate assistance with any selected content.</p>
                <!-- This is where you would add your feature image -->
                <!-- <img src="<?php echo get_template_directory_uri(); ?>/assets/images/feature-rightclick.png" alt="Right-Click Feature" class="feature-image"> -->
                <video autoplay loop muted playsinline class="device-screen">
                    <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/right-click-assistance.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card reveal reveal-right delay-200">
                <div class="feature-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>Multiple Languages</h3>
                <p>Get help in your preferred language with our multilingual support system that works seamlessly across various subjects.</p>
                <!-- This is where you would add your feature image -->

                <!-- <img src="<?php echo get_template_directory_uri(); ?>/assets/images/feature-languages.png" alt="Languages Feature" class="feature-image"> -->
                <video autoplay loop muted playsinline class="device-screen">
                    <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/multiple-languages.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <!-- Feature 4 -->
            <div class="feature-card reveal reveal-left delay-300">
                <div class="feature-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3>Code & Math Support</h3>
                <p>Receive perfectly formatted answers for programming assignments and mathematical problems with step-by-step explanations.</p>
                <!-- This is where you would add your feature image -->
                <video autoplay loop muted playsinline class="device-screen">
                    <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/code-math-support.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <!-- Feature 5 -->
            <div class="feature-card reveal reveal-up delay-400">
                <div class="feature-icon">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <h3>Ghost Mode</h3>
                <p>Enable our discreet Ghost Mode to lower the opacity of answer boxes, making them visible only when you hover over them.</p>
                <!-- This is where you would add your feature image -->
                <video autoplay loop muted playsinline class="device-screen">
                    <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/ghost-mode.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
 
            </div>

            <!-- Feature 6 -->
            <div class="feature-card reveal reveal-right delay-500">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Instant Response</h3>
                <p>Get lightning-fast answers to your questions without unnecessary waiting, helping you maintain your study momentum.</p>
                <!-- This is where you would add your feature image -->
                <video autoplay loop muted playsinline class="device-screen">
                    <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/instant-response.mp4" type="video/mp4">
                    Your browser does not support the video tag.
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="pricing">
    <div class="container">
        <div class="section-title reveal reveal-up">
            <h2>Choose Your Plan</h2>
            <p>Flexible pricing options to fit your needs</p>
        </div>

        <div class="pricing-grid">
            <!-- Free Plan -->
            <div class="pricing-card reveal reveal-left">
                <div class="plan-name">Free Plan</div>
                <div class="price">0<span class="price-currency">$</span></div>
                <ul class="pricing-features">
                    <li>10 questions per day</li>
                    <li>Email verification required</li>
                    <li>Basic support</li>
                    <li>Standard response time</li>
                </ul>
                <a href="<?php echo home_url('/#get-started'); ?>" class="btn pricing-btn">Get Started</a>
            </div>

            <?php
            // Get plan IDs based on environment
            if (wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development') {
                $monthly_plan_id = 380;
                $yearly_plan_id = 503;
            } else {
                $monthly_plan_id = 533;
                $yearly_plan_id = 534;
            }

            // Function to get currency symbol
            function get_pricing_currency_symbol($currency_code) {
                $symbols = [
                    'USD' => '$',
                    'EUR' => '€',
                    'GBP' => '£',
                    'CAD' => 'CA$',
                    'AUD' => 'A$'
                ];
                return $symbols[strtoupper($currency_code)] ?? $currency_code;
            }

            // Function to get plan pricing with discount
            function get_plan_pricing($plan_id) {
                $price = get_post_meta($plan_id, '_price_amount', true);
                $price = !empty($price) ? (float)$price : 0;
                $currency = get_post_meta($plan_id, '_price_currency', true) ?: 'USD';
                $currency_symbol = get_pricing_currency_symbol($currency);
                $discount_id = get_post_meta($plan_id, '_discount_id', true);
                
                $result = [
                    'price' => $price,
                    'currency_symbol' => $currency_symbol,
                    'has_discount' => false,
                    'discounted_price' => $price,
                    'discount_badge' => '',
                    'discount_percent' => 0
                ];

                if (!empty($discount_id)) {
                    try {
                        $stripe_feature = \CobraAI\cobra_ai()->get_feature('stripesubscriptions');
                        if ($stripe_feature && method_exists($stripe_feature, 'get_api')) {
                            $api = $stripe_feature->get_api();
                            if ($api && method_exists($api, 'get_discount')) {
                                $discount = $api->get_discount($discount_id);
                                if ($discount) {
                                    $result['has_discount'] = true;
                                    if (!empty($discount['percent_off'])) {
                                        $result['discount_percent'] = $discount['percent_off'];
                                        $result['discounted_price'] = $price * (1 - ($result['discount_percent'] / 100));
                                    } elseif (!empty($discount['amount_off'])) {
                                        $amount_off = $discount['amount_off'] / 100;
                                        $result['discounted_price'] = max(0, $price - $amount_off);
                                        $result['discount_percent'] = round(($amount_off / $price) * 100);
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        error_log('Error retrieving discount: ' . $e->getMessage());
                    }
                }

                return $result;
            }

            // Get monthly plan pricing
            $monthly_pricing = get_plan_pricing($monthly_plan_id);
            // Get yearly plan pricing
            $yearly_pricing = get_plan_pricing($yearly_plan_id);
            ?>

            <!-- Monthly Plan -->
            <div class="pricing-card featured reveal reveal-up delay-200">
                <?php if ($monthly_pricing['has_discount']): ?>
                    <div class="discount-badge-small">-<?php echo esc_html($monthly_pricing['discount_percent']); ?>%</div>
                <?php endif; ?>
                <div class="plan-name">Monthly Plan</div>
                <?php if ($monthly_pricing['has_discount']): ?>
                    <div class="price-wrapper">
                        <div class="price-original"><?php echo esc_html($monthly_pricing['currency_symbol']) . number_format($monthly_pricing['price'], 2); ?></div>
                        <div class="price"><span class="price-currency"><?php echo esc_html($monthly_pricing['currency_symbol']); ?></span><?php echo number_format($monthly_pricing['discounted_price'], 2); ?><span class="price-duration">/month</span></div>
                    </div>
                <?php else: ?>
                    <div class="price"><span class="price-currency"><?php echo esc_html($monthly_pricing['currency_symbol']); ?></span><?php echo number_format($monthly_pricing['price'], 2); ?><span class="price-duration">/month</span></div>
                <?php endif; ?>
                <ul class="pricing-features">
                    <li>Unlimited questions</li>
                    <li>24/7 priority support</li>
                    <li>Advanced features</li>
                </ul>
                <a href="<?php echo home_url('/plans/monthly-plan/'); ?>" class="btn pricing-btn">Subscribe Now</a>
            </div>

            <!-- Annual Plan -->
            <div class="pricing-card reveal reveal-right delay-400">
                <?php if ($yearly_pricing['has_discount']): ?>
                    <div class="discount-badge-small">-<?php echo esc_html($yearly_pricing['discount_percent']); ?>%</div>
                <?php endif; ?>
                <div class="plan-name">Annual Plan</div>
                <?php if ($yearly_pricing['has_discount']): ?>
                    <div class="price-wrapper">
                        <div class="price-original"><?php echo esc_html($yearly_pricing['currency_symbol']) . number_format($yearly_pricing['price'], 2); ?></div>
                        <div class="price"><span class="price-currency"><?php echo esc_html($yearly_pricing['currency_symbol']); ?></span><?php echo number_format($yearly_pricing['discounted_price'], 2); ?><span class="price-duration">/year</span></div>
                    </div>
                <?php else: ?>
                    <div class="price"><span class="price-currency"><?php echo esc_html($yearly_pricing['currency_symbol']); ?></span><?php echo number_format($yearly_pricing['price'], 2); ?><span class="price-duration">/year</span></div>
                <?php endif; ?>
                <ul class="pricing-features">
                    <li>Unlimited questions</li>
                    <li>24/7 priority support</li>
                    <li>All premium features</li>
                    <li>Early access to new features</li>
                </ul>
                <a href="<?php echo home_url('/plans/annual-plan/'); ?>" class="btn pricing-btn">Subscribe & Save</a>
            </div>
        </div>
    </div>
</section>

<!-- Payment Section -->
<section id="payment" class="payment">
    <div class="container">
        <div class="section-title reveal reveal-up">
            <h2>Secure Payment Methods</h2>
            <p>We accept all major payment providers</p>
        </div>
        <div class="payment-methods reveal reveal-up">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/support-payment.png" alt="payment mode stripe" class="payment-logo">
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="contact">
    <div class="container">
        <div class="section-title reveal reveal-up">
            <h2>Get in Touch</h2>
            <p>We're here to help with any questions you might have</p>
        </div>
        <div class="contact-flex">
            <div class="contact-info reveal reveal-left">
                <h3>Contact Information</h3>
                <p>Have questions about College Help? Our support team is ready to assist you with anything you need - from technical issues to subscription inquiries.</p>

                <div class="contact-detail">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Email Us</h4>
                        <a href="mailto:support@collegehelpai.com">support@collegehelpai.com</a>
                    </div>
                </div>
                <div class="contact-detail">
                    <div class="contact-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Live Chat</h4>
                        <p>Available 24/7 in the extension</p>
                    </div>
                </div>
            </div>
            <div class="contact-form reveal reveal-right">
                <?php echo do_shortcode('[cobra_contact_form]'); ?>
            </div>
        </div>
    </div>
</section>
<?php get_footer(); ?>