<?php
/*
    Plugin Name: Pskalski To do plugin
    Plugin URL: https://github.com/pawelskalski/todoplugin
    Description: Plugin which add simple to do list
    Author: Paweł Skalski
    Author URL: https://github.com/pawelskalski/
    Version: 1.0
    Text Domain: kved
    License: MIT
*/

if (!defined('ABSPATH')) exit;
define('PLUGIN_ROOT_DIR', plugin_dir_path(__FILE__));


/*******************************************************************
 * enqueue libraries and this plugin style files
 ******************************************************************/
function pskalski_enqueue_styles()
{
    wp_register_style('pskalski_style', plugins_url('styles/main.css', __FILE__));
    wp_register_style('bootstrap', plugins_url('styles/bootstrap.min.css', __FILE__));

    wp_enqueue_style('pskalski_style');
    wp_enqueue_style('bootstrap');

}

add_action('wp_enqueue_scripts', 'pskalski_enqueue_styles');


/*******************************************************************
 * enqueue libraries javascript files
 ******************************************************************/
function pskalski_enqueue_scripts()
{
    wp_register_script('jquery_js', plugins_url('js/jquery-3.4.1.min.js', __FILE__));
    wp_register_script('bootstrap_js', plugins_url('js/bootstrap.min.js', __FILE__));
    wp_enqueue_script('jquery_js');
    wp_enqueue_script('bootstrap_js');
}

add_action('wp_enqueue_scripts', 'pskalski_enqueue_scripts');


/*******************************************************************
 * enqueue jquery.ajax.js and create connection to admin-ajax.php
 ******************************************************************/
function pskalski_enqueue_ajax_handler()
{
    wp_register_script(
        'ajaxHandle',
        plugins_url('js/jquery.ajax.js', __FILE__),
        array(),
        false,
        true
    );
    wp_enqueue_script('ajaxHandle');

    wp_localize_script(
        'ajaxHandle',
        'ajax_object',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}

add_action('wp_enqueue_scripts', 'pskalski_enqueue_ajax_handler');


/*******************************************************************
 * Create new database record
 ******************************************************************/
function wp_ajax_insert_new_to_db()
{
    global $wpdb;
    global $message;
    global $checkbox;
    $message = $_POST['message'];
    $checkbox = $_POST['checkbox'];
    $table_name = $wpdb->prefix . "pskalski_todo";
    $wpdb->insert($table_name, array('id' => 'DEFAULT',
        'message' => $message,
        'status' => $checkbox));
    $table_name = $wpdb->prefix . "pskalski_todo";
    $data = $wpdb->get_results("SELECT * FROM " . $table_name);
    wp_send_json_success($data);

}

add_action("wp_ajax_nopriv_add_new_todo", "wp_ajax_insert_new_to_db"); // comment this line to disable sending requests by not loged users
add_action("wp_ajax_add_new_todo", "wp_ajax_insert_new_to_db");


/*******************************************************************
 * Update message for specific record
 ******************************************************************/
function wp_ajax_update_message_to_db()
{
    global $wpdb;
    global $message;
    global $ID;
    $message = $_POST['message'];
    $ID = (int)$_POST['id'];
    $table_name = $wpdb->prefix . "pskalski_todo";
    $wpdb->query($wpdb->prepare("UPDATE " . $table_name . " SET message = %s 
             WHERE ID = %s", $message, $ID)
    );
}

add_action("wp_ajax_nopriv_update_message_todo", "wp_ajax_update_message_to_db"); // comment this line to disable sending requests by not loged users
add_action("wp_ajax_update_message_todo", "wp_ajax_update_message_to_db");


/*******************************************************************
 * Update status for specific record
 ******************************************************************/
function wp_ajax_update_status_to_db()
{
    global $wpdb;
    global $status;
    global $ID;
    $status = $_POST['status'];
    $ID = (int)$_POST['id'];
    $table_name = $wpdb->prefix . "pskalski_todo";
    $wpdb->query($wpdb->prepare("UPDATE " . $table_name . " SET status = %s 
             WHERE ID = %s", $status, $ID)
    );
}

add_action("wp_ajax_nopriv_update_status_todo", "wp_ajax_update_status_to_db"); // comment this line to disable sending requests by not loged users
add_action("wp_ajax_update_status_todo", "wp_ajax_update_status_to_db");


/*******************************************************************
 * Delete specific record from database
 ******************************************************************/
function wp_ajax_delete_todo_record()
{
    global $wpdb;
    global $ID;
    $ID = (int)$_POST['id'];
    $table_name = $wpdb->prefix . "pskalski_todo";
    $wpdb->query($wpdb->prepare("Delete from " . $table_name . " WHERE ID = %s", $ID)
    );
}

add_action("wp_ajax_nopriv_delete_todo", "wp_ajax_delete_todo_record"); // comment this line to disable sending requests by not loged users
add_action("wp_ajax_delete_todo", "wp_ajax_delete_todo_record");


/*******************************************************************
 * Display plugin body
 ******************************************************************/
function pskalski_display_todo_body()
{
    ob_start();

    ?>
    <section id="pskalskiWrapper" class="pskalski-todo-container container-fluid">
        <div class="pskalski-todo-new-task-wrapper">
            <form class="row" id="create-new-todo" method="post">
                <div class="col-2 p-0 position-relative">
                    <label class="b-contain center-horizontaly-verticaly">
                        <input type="checkbox" id="checkbox">
                        <div class="b-input center-horizontaly-verticaly"></div>
                    </label>
                </div>
                <div class="col-10 p-0">
                        <textarea type="text" id="message" placeholder="Enter new task here..."
                                  oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'
                                  required></textarea>
                </div>
            </form>
        </div>

        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . "pskalski_todo";
        $todo_list = $wpdb->get_results("SELECT * FROM " . $table_name . " ORDER BY ID DESC");
        ?>
        <?php foreach ($todo_list as $todo) { ?>
            <div class="pskalski-todo-task-wrapper">
                <form class="row" id="item-<?php echo $todo->ID; ?>">
                    <div class=" position-relative p-0 col-2">
                        <label class="b-contain center-horizontaly-verticaly">
                            <input class="" type="checkbox"
                                   id="item-<?php echo $todo->ID; ?>-status" <?php if ($todo->status == 'true'): echo 'checked'; endif; ?>>
                            <div class="b-input center-horizontaly-verticaly"></div>
                        </label>
                    </div>
                    <div class="col-10 p-0">
                        <input type="hidden" id="item-<?php echo $todo->ID; ?>-id" value="<?php echo $todo->ID; ?>">
                        <textarea id="item-<?php echo $todo->ID; ?>-message"
                                  oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'><?php echo $todo->message; ?></textarea>
                        <button class="delete_button" id="delete-<?php echo $todo->ID; ?>-item">x</button>
                    </div>
                </form>
            </div>
        <?php } ?>
    </section>

    <?php
    return ob_get_clean();
}

add_shortcode('pskalski_todo', 'pskalski_display_todo_body');


/*******************************************************************
 * Installer, create plugin table
 ******************************************************************/
function installer()
{
    include('installer.php');
}

register_activation_hook(__file__, 'installer');


/*******************************************************************
 * Destroyer, when plugin is deleted drop plugin table
 ******************************************************************/
function destroyer()
{
    include('uninstall.php');
}

register_uninstall_hook(__FILE__, 'destroyer');

