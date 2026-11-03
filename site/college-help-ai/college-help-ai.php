<?php
/*
Plugin Name: College Help AI
Description: A plugin to manage user sessions, fetch answers, and interact with the College Help AI Chrome extension.
Version: 3.0.1
Author: Your Name
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class College_Help_AI_Settings
{
    private $options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // Add the menu item under Settings
        add_options_page(
            'College Help AI Settings',
            'College Help AI',
            'manage_options',
            'college-help-ai-settings',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('college_help_ai_options');
?>
        <div class="wrap">
            <h1>College Help AI Settings</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('college_help_ai_option_group');
                do_settings_sections('college-help-ai-settings');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'college_help_ai_option_group', // Option group
            'college_help_ai_options', // Option name
            array($this, 'sanitize') // Sanitize
        );

        // Free Plan Limits Section
        add_settings_section(
            'free_plan_limits_section', // ID
            'Free Plan Limits', // Title
            array($this, 'print_section_info_limits'), // Callback
            'college-help-ai-settings' // Page
        );

        add_settings_field(
            'total_limit', // ID
            'Total Lifetime Requests', // Title 
            array($this, 'total_limit_callback'), // Callback
            'college-help-ai-settings', // Page
            'free_plan_limits_section' // Section           
        );

        add_settings_field(
            'last_hour_limit',
            'Hourly Requests Limit',
            array($this, 'last_hour_limit_callback'),
            'college-help-ai-settings',
            'free_plan_limits_section'
        );

        add_settings_field(
            'today_limit',
            'Daily Requests Limit',
            array($this, 'today_limit_callback'),
            'college-help-ai-settings',
            'free_plan_limits_section'
        );

        // Prompts Section
        add_settings_section(
            'prompts_section',
            'AI Prompts Configuration',
            array($this, 'print_section_info_prompts'),
            'college-help-ai-settings'
        );

        add_settings_field(
            'select_prompt',
            'Text Selection Prompt',
            array($this, 'select_prompt_callback'),
            'college-help-ai-settings',
            'prompts_section'
        );

        add_settings_field(
            'image_prompt',
            'Image Analysis Prompt',
            array($this, 'image_prompt_callback'),
            'college-help-ai-settings',
            'prompts_section'
        );

        add_settings_field(
            'default_prompt',
            'Default Prompt',
            array($this, 'default_prompt_callback'),
            'college-help-ai-settings',
            'prompts_section'
        );

        // Logging Section
        add_settings_section(
            'logging_section',
            'Logging Configuration',
            array($this, 'print_section_info_logging'),
            'college-help-ai-settings'
        );

        add_settings_field(
            'enable_logging',
            'Enable Spam Logging',
            array($this, 'enable_logging_callback'),
            'college-help-ai-settings',
            'logging_section'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();

        // Sanitize numeric values
        if (isset($input['total_limit']))
            $new_input['total_limit'] = absint($input['total_limit']);

        if (isset($input['last_hour_limit']))
            $new_input['last_hour_limit'] = absint($input['last_hour_limit']);

        if (isset($input['today_limit']))
            $new_input['today_limit'] = absint($input['today_limit']);

        // Sanitize text areas
        if (isset($input['select_prompt']))
            $new_input['select_prompt'] = sanitize_textarea_field($input['select_prompt']);

        if (isset($input['image_prompt']))
            $new_input['image_prompt'] = sanitize_textarea_field($input['image_prompt']);

        if (isset($input['default_prompt']))
            $new_input['default_prompt'] = sanitize_textarea_field($input['default_prompt']);

        // Sanitize checkbox
        $new_input['enable_logging'] = isset($input['enable_logging']);

        return $new_input;
    }

    /** 
     * Print the Section text for limits
     */
    public function print_section_info_limits()
    {
        print 'Configure the usage limits for users on the free plan:';
    }

    /** 
     * Print the Section text for prompts
     */
    public function print_section_info_prompts()
    {
        print 'Configure the AI prompts used for different types of queries:';
    }

    /** 
     * Print the Section text for logging
     */
    public function print_section_info_logging()
    {
        print 'Configure logging options:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function total_limit_callback()
    {
        $value = isset($this->options['total_limit']) ? esc_attr($this->options['total_limit']) : 100;
        echo '<input type="number" id="total_limit" name="college_help_ai_options[total_limit]" value="' . $value . '" min="1" />';
        echo '<p class="description">Maximum number of requests a free user can make in total</p>';
    }

    public function last_hour_limit_callback()
    {
        $value = isset($this->options['last_hour_limit']) ? esc_attr($this->options['last_hour_limit']) : 50;
        echo '<input type="number" id="last_hour_limit" name="college_help_ai_options[last_hour_limit]" value="' . $value . '" min="1" />';
        echo '<p class="description">Maximum number of requests a free user can make per hour</p>';
    }

    public function today_limit_callback()
    {
        $value = isset($this->options['today_limit']) ? esc_attr($this->options['today_limit']) : 10;
        echo '<input type="number" id="today_limit" name="college_help_ai_options[today_limit]" value="' . $value . '" min="1" />';
        echo '<p class="description">Maximum number of requests a free user can make per day</p>';
    }

    public function select_prompt_callback()
    {
        $default = "Answer quizzes, exercises, clarifications, or questions concisely. For multiple-choice quizzes, provide only the correct answer(s). If unsure, note your confidence level. If there are multiple questions, answer only the first one.";
        $value = isset($this->options['select_prompt']) ? esc_textarea($this->options['select_prompt']) : $default;
        echo '<textarea id="select_prompt" name="college_help_ai_options[select_prompt]" rows="4" cols="80">' . $value . '</textarea>';
        echo '<p class="description">System prompt for text selection queries</p>';
    }

    public function image_prompt_callback()
    {
        $default = "Solve the visible problem or exercise in the image. Provide only the solution with minimal explanation.";
        $value = isset($this->options['image_prompt']) ? esc_textarea($this->options['image_prompt']) : $default;
        echo '<textarea id="image_prompt" name="college_help_ai_options[image_prompt]" rows="4" cols="80">' . $value . '</textarea>';
        echo '<p class="description">System prompt for image analysis queries</p>';
    }

    public function default_prompt_callback()
    {
        $default = "Answer the question Provide only the solution with minimal explanation.";
        $value = isset($this->options['default_prompt']) ? esc_textarea($this->options['default_prompt']) : $default;
        echo '<textarea id="default_prompt" name="college_help_ai_options[default_prompt]" rows="4" cols="80">' . $value . '</textarea>';
        echo '<p class="description">Default system prompt when no specific query type is detected</p>';
    }

    public function enable_logging_callback()
    {
        $checked = isset($this->options['enable_logging']) ? checked($this->options['enable_logging'], true, false) : '';
        echo '<input type="checkbox" id="enable_logging" name="college_help_ai_options[enable_logging]" ' . $checked . ' />';
        echo '<p class="description">Enable detailed logging of spam registration attempts</p>';
    }
}

$college_help_ai_settings = new College_Help_AI_Settings();

/**
 * Helper function to get a specific setting
 */
function college_help_ai_get_setting($key, $default = null)
{
    $options = get_option('college_help_ai_options');
    return isset($options[$key]) ? $options[$key] : $default;
}



// Update your get_prompt function to use the new settings
add_filter('college_help_ai_get_prompt', function ($prompt, $request) {
    if (($request->tool === 'image' || $request->tool === 'base64') && $request->isImage === true) {
        // Image Prompt
        $prompt['system'] = college_help_ai_get_setting(
            'image_prompt',
            "Solve the visible problem or exercise in the image. Provide only the solution with minimal explanation."
        );
    } else if ($request->tool === 'select') {
        // Select Prompt
        $prompt['system'] = college_help_ai_get_setting(
            'select_prompt',
            "Answer quizzes, exercises, clarifications, or questions concisely. For multiple-choice quizzes, provide only the correct answer(s). If unsure, note your confidence level. If there are multiple questions, answer only the first one."
        );
    } else {
        // Default prompt
        $prompt['system'] = college_help_ai_get_setting(
            'default_prompt',
            "Answer the question Provide only the solution with minimal explanation."
        );
    }

    return $prompt;
}, 10, 2);

// Update your logging function to check if logging is enabled
add_filter('college_help_ai_should_log_spam', function ($should_log) {
    return college_help_ai_get_setting('enable_logging', false);
});



use function CobraAI\cobra_ai;

class CollegeHelpAnswer
{
    public $id = '';
    public $question = '';
    public $image = '';
    public $answer = '';
    public $error = '';
    public $success = false;
    public $user_id = '';
    public $tracking_id = 0;

    public function __construct($id = '', $question = '', $image = '', $answer = '', $error = '', $success = false, $user_id = '')
    {
        $this->id = $id;
        $this->question = $question;
        $this->image = $image;
        $this->answer = $answer;
        $this->error = $error;
        $this->success = $success;
        $this->user_id = $user_id;
    }
}


class CroppedImage
{
    public $x = 0;
    public $y = 0;
    public $width = 0;
    public $height = 0;
    public $devicePixelRatio = 1;

    public function __construct($data = array())
    {
        if (!empty($data)) {
            $this->x = isset($data['x']) ? $data['x'] : 0;
            $this->y = isset($data['y']) ? $data['y'] : 0;
            $this->width = isset($data['width']) ? $data['width'] : 0;
            $this->height = isset($data['height']) ? $data['height'] : 0;
            $this->devicePixelRatio = isset($data['devicePixelRatio']) ? $data['devicePixelRatio'] : 1;
        }
    }
}

class CollegeHelpRequest
{
    public $question = '';
    public $tool = ''; // value in  ['select', 'image', 'base64']
    public $type = ''; // value in ['global', 'math', explain', 'describe_image', 'translate',extract_text']
    public $image = '';
    public $language = '';
    public $user_id = '';
    public $base64 = '';
    public $isImage = false;
    public $fromUrl = false;
    public $croppedImage;

    public function __construct($question, $tool, $type, $image, $language, $user_id, $base64, $isImage, $fromUrl, $croppedImage)
    {
        $this->question = $question;
        $this->tool = $tool;
        $this->type = $type;
        $this->image = $image;
        $this->language = $language;
        $this->user_id = $user_id;
        $this->base64 = $base64;
        $this->isImage = $isImage;
        $this->fromUrl = $fromUrl;
        // Initialize the cropped image object
        if ($croppedImage) {
            $this->croppedImage = new CroppedImage($croppedImage);
        } else {
            $this->croppedImage = new CroppedImage();
        }
    }

    public function saveImage()
    {
        if (!$this->isImage) {
            throw new InvalidArgumentException('Image is not set or is not an image type');
        }

        // Process the base64 data
        $dataUrl = str_replace('data:image/png;base64,', '', $this->base64);
        $dataUrl = str_replace(' ', '+', $dataUrl);
        $imgData = base64_decode($dataUrl);

        // Create a temporary file to work with
        $upload_dir = wp_upload_dir();
        $temp_file = $upload_dir['path'] . '/' . uniqid('temp_') . '.png';
        file_put_contents($temp_file, $imgData);

        // Load WordPress image editing functionality
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Create WP_Image_Editor instance
        $image = wp_get_image_editor($temp_file);

        if (is_wp_error($image)) {
            @unlink($temp_file); // Clean up
            throw new Exception('WordPress image editor could not load the image.');
        }

        // Calculate the crop dimensions
        $adjustedLeft = round($this->croppedImage->x * $this->croppedImage->devicePixelRatio);
        $adjustedTop = round($this->croppedImage->y * $this->croppedImage->devicePixelRatio);
        $adjustedWidth = round($this->croppedImage->width * $this->croppedImage->devicePixelRatio);
        $adjustedHeight = round($this->croppedImage->height * $this->croppedImage->devicePixelRatio);

        // Crop the image
        $result = $image->crop($adjustedLeft, $adjustedTop, $adjustedWidth, $adjustedHeight);

        if (is_wp_error($result)) {
            @unlink($temp_file); // Clean up
            throw new Exception('Failed to crop the image: ' . $result->get_error_message());
        }

        // Generate a unique filename
        $file_name = uniqid('college_help_ai_') . '.png';
        $file_path = $upload_dir['path'] . '/' . $file_name;

        // Save the image
        $result = $image->save($file_path);

        if (is_wp_error($result)) {
            @unlink($temp_file); // Clean up
            throw new Exception('Failed to save the image: ' . $result->get_error_message());
        }

        // Clean up the temporary file
        @unlink($temp_file);

        // Get file URL
        $file_url = $upload_dir['url'] . '/' . $file_name;

        // Insert into media library
        $file_type = wp_check_filetype($file_name, null);
        $attachment = array(
            'guid'           => $file_url,
            'post_mime_type' => $file_type['type'],
            'post_title'     => sanitize_file_name($file_name),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment, $file_path);

        // Generate metadata
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        // Return URL of the image
        return $file_url;
    }
    public function validator(): void
    {
        // validator user 
        // TO DO : move to cobra ai. 

        // get prompte from the request
        $user_id = $this->user_id;
        if (empty($user_id)) {
            throw new InvalidArgumentException('User ID is required');
        }
        if (!is_numeric($user_id)) {
            throw new InvalidArgumentException('User ID must be a number');
        }


        // check if user has a valid token 
        $token = get_user_meta($user_id, 'ext_token', true);
        if (empty($token)) {
            throw new InvalidArgumentException('Token is invalid or expired');
        }
        //- has  subscubed 
        $subscription = cobra_ai()->get_feature('stripesubscriptions');
        if (is_object($subscription) && method_exists($subscription, 'get_subscriptions')) {
            $subscriptions = $subscription->get_subscriptions();

            $isSubscribed =  $subscriptions->has_active_subscription($user_id);;
        } else {
            $isSubscribed = false;
        }
        if (!$isSubscribed) {
            $total = 0;
            $last_hour = 0;
            $today = 0;
            // load the tracking feature
            // $tracking_feature = cobra_ai()->get_feature('tracking');
            $tracking_feature = cobra_ai()->get_feature('ai');

            if (is_object($tracking_feature) && isset($tracking_feature->tracking) && method_exists($tracking_feature->tracking, 'get_user_tracking_count')) {
                $subscription_link = site_url() . '/plans/annual-plan/?utm_source=chrome_extension&utm_medium=extension&utm_campaign=profile_access&utm_content=profile_button'; // Replace with your subscription link
                $today = $tracking_feature->tracking->get_user_tracking_count($user_id, ['period' => 'today']);
                $last_hour = $tracking_feature->tracking->get_user_tracking_count($user_id, ['period' => 'hour']);

                $total_limit = college_help_ai_get_setting('total_limit', 100);
                $hour_limit = college_help_ai_get_setting('last_hour_limit', 50);
                $day_limit = college_help_ai_get_setting('today_limit', 10);

                if ($total >=  $total_limit) {
                    throw new InvalidArgumentException(
                        'You have reached your lifetime free-request limit. '
                            . 'Please <a href="' . $subscription_link . '" target="_blank" rel="noopener">subscribe to a plan</a> '
                            . 'or try again later.'
                    );
                } else if ($today >= $day_limit) {
                    throw new InvalidArgumentException(
                        'You have reached your daily free-request limit. '
                            . 'Please <a href="' . $subscription_link . '" target="_blank" rel="noopener">subscribe now</a> '
                            . 'or come back tomorrow.'
                    );
                } else if ($last_hour >=  $hour_limit) {
                    throw new InvalidArgumentException(
                        'You have reached your hourly free-request limit. '
                            . 'Please wait an hour, or <a href="' . $subscription_link . '" target="_blank" rel="noopener">upgrade your plan</a> for more access.'
                    );
                }
            }
            $email_verified = get_user_meta($user_id, '_email_verified', true);
            if (empty($email_verified)) {
                $prodile_link = site_url() . "/my-account/?utm_source=chrome_extension&utm_medium=extension&utm_campaign=profile_access&utm_content=profile_button"; // Replace with your profile link
                throw new InvalidArgumentException('Email is not verified please verify your email to continue using the service. <a href="' . $prodile_link . '" target="_blank">Verify now</a>');
            }
        }



        if ($this->isImage) {
            if (empty($this->base64) && empty($this->image)) {
                throw new InvalidArgumentException('Screen or Image is required');
            }
            if ($this->croppedImage->width <= 0) {
                throw new InvalidArgumentException('Width must be greater than 0');
            }
            if ($this->croppedImage->height <= 0) {
                throw new InvalidArgumentException('Height must be greater than 0');
            }
            if ($this->croppedImage->x < 0) {
                throw new InvalidArgumentException('Left position cannot be negative');
            }
            if ($this->croppedImage->y < 0) {
                throw new InvalidArgumentException('Top position cannot be negative');
            }
            if ($this->croppedImage->devicePixelRatio <= 0) {
                throw new InvalidArgumentException('Device pixel ratio must be greater than 0');
            }
        }

        if ($this->tool === "select" && empty($this->question)) {
            throw new InvalidArgumentException('Question is required');
        }
    }
}


// class College_help_request :
// Include necessary files
class College_Help_AI_API_Handler
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_api_routes'));
        // Add this to your plugin file

        // Register the AJAX action for both logged in and non-logged in users
        add_action('wp_ajax_validate_my_session',  array($this, 'validate_my_session_callback'));
        add_action('wp_ajax_nopriv_validate_my_session',  array($this, 'validate_my_session_callback'));
    }

    public function register_api_routes()
    {


        register_rest_route('college-help-ai/v1', '/fetch-answer', array(
            'methods' => 'POST',
            'callback' => array($this, 'fetch_answer'),
            'permission_callback' => array($this, 'verify_user_token')
        ));

        register_rest_route('college-help-ai/v1', '/user-data', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_user_data'),
            'permission_callback' => array($this, 'verify_user_token')
        ));

        register_rest_route('college-help-ai/v1', '/auth/logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'logout_user'),
            'permission_callback' => array($this, 'verify_user_token')
        ));
    }


    public function validate_my_session_callback()
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            wp_send_json_error(array(
                'message' => 'Session is invalid or expired'
            ), 401);
            exit;
        }

        // get current user token and expiration time
        $token = get_user_meta($user_id, 'ext_token', true);
        $expires_at = get_user_meta($user_id, 'ext_expires_at', true);

        if (!$token || !$expires_at || $expires_at < time()) {
            $token = wp_hash(uniqid($user_id, true));
            // Set expiration time (30 days)
            $expires_at = time() + (24 * 60 * 60 * 30);

            update_user_meta($user_id, 'ext_token', $token);
            update_user_meta($user_id, 'ext_expires_at', $expires_at);
        }
        $subscription = cobra_ai()->get_feature('stripesubscriptions');
        if ($user_id == 1) {
            $isSubscribed = true;
        } else if (is_object($subscription) && method_exists($subscription, 'get_subscriptions')) {
            $subscriptions = $subscription->get_subscriptions();

            $isSubscribed =  $subscriptions->has_active_subscription($user_id);;
        } else {
            $isSubscribed = false;
        }
        // get_user_credit_total($user_id);
        $credit_feature = cobra_ai()->get_feature('credits');
        if (!is_object($credit_feature) || !method_exists($credit_feature, 'get_user_credit_total')) {
            $credits = $credit_feature->get_user_credit_total($user_id);
        } else {
            $credits = 10;
        }
        $total = 0;
        $last_hour = 0;
        $today = 0;
        // load the tracking feature
        $tracking_feature = cobra_ai()->get_feature('ai');

        if (is_object($tracking_feature) && isset($tracking_feature->tracking) && method_exists($tracking_feature->tracking, 'get_user_tracking_count')) {
            $total = $tracking_feature->tracking->get_user_tracking_count($user_id);
            $today = $tracking_feature->tracking->get_user_tracking_count($user_id, ['period' => 'today']);
            $last_hour = $tracking_feature->tracking->get_user_tracking_count($user_id, ['period' => 'hour']);
        }
        wp_send_json_success(array(
            'user_id' => $user_id,
            'nonce' => wp_create_nonce('wp_rest'),
            'token' => $token,
            'expires_at' => $expires_at,
            'name' => get_userdata($user_id)->display_name,
            'email' => get_userdata($user_id)->user_email,
            "credits" =>  $credits,
            "lastHour" => $last_hour,
            "consumed" => $today,
            "totalConsumed" => $total,
            "isSubscribed" =>  $isSubscribed,
            "planType" => '',

        ));
    }
    public function fetch_answer($request)
    {
        $data = $request->get_json_params();
        try {

            $requestParam = new CollegeHelpRequest(
                $data['question'],
                $data['tool'],
                $data['type'],
                $data['image'],
                $data['language'],
                $data['user_id'],
                $data['base64'],
                $data['isImage'],
                $data['fromUrl'],
                $data['croppedImage']
            );

            // Validate the request parameters
            $requestParam->validator();
            // Save the image if it's an image type
            if ($requestParam->isImage) {
                $requestParam->image = $requestParam->saveImage();
            }
            // Request validator 

            // $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
            // $token = $request->get_header('Authorization');
            // if (!$user_id || !$token) {
            //     return true;
            // }
            // $stored_token = get_user_meta($user_id, 'ext_token', true);
            // // befor compare need to delete  Bearer from token
            // $token = str_replace('Bearer ', '', $token);
            // $compare_token = hash_equals($stored_token, $token);

            // get prompte from the request

            $prompt = $this->get_prompt($requestParam);
            // ask AI
            $answer = new CollegeHelpAnswer();
            $api_rest = $this->askAi($prompt);

            if (isset($api_rest['error'])) {
                $answer->error = $api_rest['error'];
                $answer->success = false;
            } else {
                $answer->question = $requestParam->question;
                $answer->image = $requestParam->image;
                $answer->answer = $api_rest['content'];
                $answer->success = true;
                $answer->tracking_id = $api_rest['tracking_id'];
            }
            $answer->user_id = $data['user_id'];
            // handle errors
            // save answer to the database
            // $answer->save_to_db();
            // return answer to the user

            return new WP_REST_Response($answer, 200);
        } catch (Exception $e) {
            $error = new CollegeHelpAnswer();
            $error->error = $e->getMessage();
            $error->success = false;
            $error->user_id =  $data['user_id'];
            // $error->question = $request->question;
            // $error->image = $request->image;
            // $error->answer = $request->answer;
            // $error->save_to_db();
            return new WP_REST_Response($error, 200);
        }
    }
    public function askAi($prompt)
    {
        // Call OPEN AI API or any other AI service here

        $ai = cobra_ai()->get_feature('ai');
        // check if $ai is not nulle and have process_request method
        if (!is_object($ai) || !method_exists($ai, 'process_request')) {
            return new WP_Error('invalid_ai', 'AI service is not available', array('status' => 500));
        }
        return $ai->process_request('openai', $prompt);
    }
    public function get_prompt($request)
    {
        // public $tool = ''; // value in  ['select', 'image', 'base64']
        // public $type = ''; // value in ['global', 'math', explain', 'describe_image', 'translate',extract_text']
        // public $isImage = false;
        $options = get_option('college_help_ai_options', array());

        $prompt = array();
        $ppt_text = array();
        $question = $request->question;
        $tool = $request->tool;
        $type = $request->type;
        $isImage = $request->isImage;

        if (($tool === 'image' || $tool === 'base64') && $isImage === true) {
            // Image Prompt
            $default_image_prompt = "Solve the visible problem or exercise in the image. Provide only the solution with minimal explanation.";
            $ppt_text = isset($options['image_prompt']) ? $options['image_prompt'] : $default_image_prompt;

            $prompt = array(
                'system' => $ppt_text,
                'image' =>  $request->image, // or URL image or base64               
            );
        } else if ($tool === 'select') {
            // Select Prompt
            $default_select_prompt = "Answer quizzes, exercises, clarifications, or questions concisely. For multiple-choice quizzes, provide only the correct answer(s). If unsure, note your confidence level. If there are multiple questions, answer only the first one.";
            $ppt_text = isset($options['select_prompt']) ? $options['select_prompt'] : $default_select_prompt;

            $prompt = array(
                'system' => $ppt_text,
                'user' => $question
            );
        } else {
            // Default prompt
            $default_prompt = "Answer the question Provide only the solution with minimal explanation.";
            $ppt_text = isset($options['default_prompt']) ? $options['default_prompt'] : $default_prompt;

            $prompt = array(
                'system' => $ppt_text,
                'user' => $question
            );
        }

        // handle the request and return the answer
        return $prompt;
    }
    public function get_user_data($request)
    {

        // TODO : apply the same logic as in validate_my_session_callback
        $data = $request->get_json_params();
        $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
        $token = $request->get_header('Authorization');

        if (!$user_id || !$token) {
            return true;
        }

        $stored_token = get_user_meta($user_id, 'ext_token', true);

        $user_data = array(
            'name' => get_userdata($user_id)->display_name,
            'plan' => get_user_meta($user_id, 'college_help_ai_plan', true),
            'credits' => 'free', //get_user_meta($user_id, 'college_help_ai_credits', true),
            'email' => get_userdata($user_id)->user_email,
            'user_id' => $user_id,
            'token' => $stored_token,
            'expires_at' => get_user_meta($user_id, 'ext_expires_at', true)
        );

        return new WP_REST_Response($user_data, 200);
    }

    public function handle_logout($request)
    {
        wp_logout();

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Logged out successfully'
        ), 200);
    }


    public function verify_user_token($request)
    {

        $data = $request->get_json_params();
        $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
        $token = $request->get_header('Authorization');

        if (!$user_id || !$token) {
            return true;
        }
        // echo $user_id;
        $stored_token = get_user_meta($user_id, 'ext_token', true);
        // befor compare need to delete  Bearer from token
        $token = str_replace('Bearer ', '', $token);
        $compare_token = hash_equals($stored_token, $token);
        // log all the tokens for debugging
        error_log($compare_token ? 'true' : 'false');
        return hash_equals($stored_token, $token);
    }
}

register_activation_hook(__FILE__,   'create_tables');

function create_tables()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'college_help_ai_data';

    $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            total BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            last_hour BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            today BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            liked_ext bool DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}


// Initialize the plugin
function college_help_ai_init()
{
    $api_handler = new College_Help_AI_API_Handler();
}
add_action('plugins_loaded', 'college_help_ai_init');

/**
 * TODO : Move to COBRA AI 
 */

function custom_wp_login_action_redirects()
{
    // Vérifier si l'action est définie
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    // Si c'est une requête POST pour l'enregistrement, c'est potentiellement du spam
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
        // Obtenir l'adresse IP
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        // plugin option logger  from settings
        if (college_help_ai_get_setting('enable_logging', false)) {
            // Loguer la tentative
            error_log(sprintf(
                "[SPAM ATTEMPT] IP: %s, User-Agent: %s, Username: %s, Email: %s",
                $ip,
                isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown',
                isset($_POST['user_login']) ? $_POST['user_login'] : 'Unknown',
                isset($_POST['user_email']) ? $_POST['user_email'] : 'Unknown'
            ));
        }

        // Continuer normalement - WordPress gérera l'erreur reCAPTCHA
        return;
    }

    // Ne pas rediriger sur les requêtes POST (quand les formulaires sont soumis)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return;
    }

    // Pour les requêtes GET, continuer avec les redirections existantes
    if (!isset($_GET['action'])) {
        return;
    }

    switch ($action) {
        case 'login':
            wp_redirect(home_url('/login/'));
            exit;
        case 'resetpass':
            wp_redirect(home_url('/my-account/#password'));
            exit;
        case 'lostpassword':
            wp_redirect(home_url('/forgot-password/'));
            exit;
        case 'register':
            wp_redirect(home_url('/registration/'));
            exit;
        default:
            // Pas de redirection pour les autres actions
            break;
    }
}
add_action('login_init', 'custom_wp_login_action_redirects');

// TODO :Move to template 
function custom_author_page_redirect()
{
    if (is_author()) {
        if (is_user_logged_in()) {
            // Redirect logged-in users to /my-account/
            wp_redirect(home_url('/my-account/'));
            exit;
        } else {
            // Show 404 page to non-logged-in users
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include(get_query_template('404'));
            exit;
        }
    }
}
add_action('template_redirect', 'custom_author_page_redirect');
