<?php
/*
Plugin Name: Whatsapp Subscription
Plugin URI: http://jothirajan-developer.blogspot.in/
Description: This plugin will set a text box for users to enter their mobile numbers (mobile number subscription) and the site admin can maintain the list
Author: Jothirajan
Version: 1.0
Author URI: http://jothirajan-developer.blogspot.in/
*/


add_action('admin_menu', 'whatsapp_plugin_menu');
add_filter( 'widget_text', 'do_shortcode', 1);

function whatsapp_plugin_menu() {
    if (is_admin()){
        add_menu_page( 'Whatsapp Plugin Options', 'Whatsapp Plugin', 'manage_options', 'whatsapp-plugin', 'whatsapp_plugin_options',  plugin_dir_url( __FILE__ ) . 'icon.png'  );
    }
	
	
	 if (isset($_GET['page']) && $_GET['page'] == 'whatsapp-plugin') {
        wp_enqueue_media();
        wp_register_script('whatsapp-admin-js', plugin_dir_url( __FILE__ ).'/my-admin.js', array('jquery'));
        wp_enqueue_script('whatsapp-admin-js');
    }
}


function wpct_create_db_table() {
 
global $wpdb;
/* create the table name using the wordpress table prefix for this site */
$wpct_tablename = $wpdb->prefix . "whatsapp_software";


if($wpdb->get_var("show tables like '$wpct_tablename'") != $wpct_tablename ){

				/* setup the structure of the table creating these as a variable */
				$wpct_sql = "CREATE TABLE IF NOT EXISTS $wpct_tablename (
				id int(11) NOT NULL AUTO_INCREMENT,
				mobile_no varchar(20) NOT NULL,
				PRIMARY KEY (id)
				);";
				/* include dbdelta stuff */
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				/* build the table using the variables above */
				dbDelta( $wpct_sql );

}
 
}
 
/* run the table creation function only on plugin activation */
register_activation_hook( __FILE__, 'wpct_create_db_table' );


function add_subscription( $atts ){
    global $wpdb;
	 $return = $content;       
	 $return .= '<form method="post"  enctype="multipart/form-data"><input type="text" value="" name="whatsapp_orginal_ver" maxlength="18"><input type="submit" value="Add number" name="whatsapp_add_software" id="whatsapp_add_software" class="button-secondary"></form>';
	
	if(isset($_REQUEST['whatsapp_orginal_ver'])&&$_REQUEST['whatsapp_add_software']=="Add number")
	 {
		$orginal_ver=$_REQUEST['whatsapp_orginal_ver'];
			if(!empty($orginal_ver)) {
			$the_software = $wpdb->get_results("SELECT * FROM " .$wpdb->prefix ."whatsapp_software WHERE mobile_no='".$orginal_ver."'");
			if(empty($the_software[0])) {
		    $wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."whatsapp_software(mobile_no) VALUES (%d)",$orginal_ver) );
			$return .='<span style="color:green; font-size:12px;">Thanks for your subscription</span>';
			}
			if(!empty($the_software[0])) {
			$return .='<span style="color:red; font-size:12px;">Already your number is in our subscription list</span>';
			}
	}
	else
	{
	    $return .='<span style="color:red; font-size:12px;">Please enter the Mobile Number</span>';
	}
	}
		   return $return;    
}
add_shortcode( 'ADD-SUBSCRIPTION-WHATSAPP', 'add_subscription' );





 function wptuts_header_image($params, $content = null) {
       extract(shortcode_atts( array('image' => ''), $params));      

       $return = $content;          
       $return .= '<div class="row-fluid header-image ' . $image . '">' . do_shortcode($content) . '</div>';  
         
       return $return;    
}
add_shortcode('header-image', 'wptuts_header_image');








/**Get all the data from the tabe wp_whatsapp_software**/
function whatsapp_get_software() {
    global $wpdb;
    $software = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."whatsapp_software ORDER BY id ASC");
    return $software;
}

/**Get an specific row from the table wp_whatsapp_software**/
function whatsapp_get_softwarerow($id) {
    global $wpdb;
    $the_software = $wpdb->get_results("SELECT * FROM " .$wpdb->prefix ."whatsapp_software WHERE id='".$id."'");
    if(!empty($the_software[0])) {
        return $the_software[0];
    }
    return;
}


function whatsapp_software_meta_box() {
    global $edit_software;
?>
    <p>Mobile Number: <input type="text" name="whatsapp_orginal_ver" value="<?php if(isset($edit_software)) echo $edit_software->mobile_no;?>" /></p>
 
<?php
}


function whatsapp_plugin_options(){

    $G_Edit=$_GET['edit'];
    /**Manipulate data of the custom table**/
    whatsapp_action();
    if (empty($G_Edit)) {
	
      /**Display the data into the Dashboard**/
        whatsapp_manage_software();
    } else {
      /**Display a form to add or update the data**/
        whatsapp_add_software();  
    }
}

function whatsapp_action(){
    global $wpdb;
    
    $whatsapp_id = $_REQUEST['whatsapp_id'];
    $whatsapp_action = sanitize_text_field( $_REQUEST['whatsapp_action'] );
    $whatsapp_add_software = sanitize_text_field( $_POST['whatsapp_add_software'] );
    $whatsapp_orginal_ver = sanitize_text_field( $_POST['whatsapp_orginal_ver'] );
    $whatsapp_software_id = sanitize_text_field( $_POST['whatsapp_software_id'] );
    $G_delete = intval( $_GET['delete'] );
    
	
	/**Delete the data if the variable "delete" is set**/
	/**Multiple**/
	if(is_array($whatsapp_id)&&$whatsapp_action=="delete")
	{
	$selected=implode(",",$whatsapp_id);
	$wpdb->query("DELETE FROM " .$wpdb->prefix ."whatsapp_software WHERE id in ($selected)");
	
	}
	
	/**Single**/
    if(isset($G_delete)) {
        $G_delete = absint($G_delete);
        $wpdb->query( $wpdb->prepare(" DELETE FROM ".$wpdb->prefix ."whatsapp_software WHERE id = %d", $G_delete ));
        
    }


    /**Process the changes in the custom table**/
    if(isset($whatsapp_add_software) and isset($whatsapp_orginal_ver)  ) {   
        /**Add new row in the custom table**/
		 $orginal_ver = $whatsapp_orginal_ver;
  

        if(empty($whatsapp_software_id)&&!empty($orginal_ver)) {
		 /**Insert the data**/	
		 $wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."whatsapp_software(mobile_no) VALUES (%d)",$orginal_ver) );

           
           
        } else {
        /**Update the data**/
            $software_id = $whatsapp_software_id;
             $wpdb->query( $wpdb->prepare( "UPDATE " .$wpdb->prefix. "whatsapp_software SET mobile_no = %d WHERE id = $software_id", $orginal_ver ) );            
        }
    }  
}




function whatsapp_add_software(){

	$G_id = intval( $_GET['id'] );
	$G_paged = intval( $_GET['paged'] );
	

	$software_id =0;
	if($G_id) $software_id = $G_id;
	$paged = isset( $G_paged ) ? absint( $G_paged ) : 1; 
	if($paged==0) {$paged=1;}
	

    /**Get an specific row from the table wp_whatsapp_software**/
    global $edit_software;
    if ($software_id) $edit_software = whatsapp_get_softwarerow($software_id);  
	
    /**create meta box**/
    add_meta_box('whatsapp-meta', __('Add/ Edit Number'), 'whatsapp_software_meta_box', 'whatsapp', 'normal', 'core' );
?>

    /**Add new number settings**/
    <div class="wrap">
      <div id="faq-wrapper">
        <form method="post" action="?page=whatsapp-plugin&amp;paged=<?php echo $paged; ?>"  enctype="multipart/form-data">
          <h2>
          <?php if( $software_id == 0 ) {
                $tf_title = __('Add Number');
          }else {
                $tf_title = __('Edit Number');
          }
        
          ?>
          </h2>
          <div id="poststuff" class="metabox-holder">
            <?php do_meta_boxes('whatsapp', 'normal','low'); ?>
          </div>
          <input type="hidden" name="whatsapp_software_id" value="<?php echo $software_id?>" />
          <input type="submit" value="<?php echo $tf_title;?>" name="whatsapp_add_software" id="whatsapp_add_software" class="button-secondary">

        </form>
      </div>
    </div>
<?php
}


function whatsapp_manage_software(){
?>
<?php 
  global $wpdb;
  
  $G_id = intval( $_GET['id'] );
	$G_paged = intval( $_GET['paged'] );
  
$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; 

$limit = 10;
$offset = ( $paged - 1 ) * $limit;


$entries = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}whatsapp_software order by id DESC LIMIT $offset, $limit" );

$total = $wpdb->get_var( "SELECT COUNT(`id`) FROM {$wpdb->prefix}whatsapp_software" );
 $num_of_pages = ceil( $total / $limit );

$big = 999999999; // need an unlikely integer


$page_links = paginate_links( array(
	'base' => str_replace( $big, '%#%', html_entity_decode( get_pagenum_link( $big ) ) ),
	'format' => '?paged=%#%',
	'prev_text' => __( '&laquo;', 'aag' ),
	'next_text' => __( '&raquo;', 'aag' ),
	'total' => $num_of_pages,
		'current' => $paged
) );



if ( $page_links ) {
	echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
}

?>




<div class="wrap">
  <div class="icon32" id="icon-edit"><br></div>
  <h2><?php _e('Whatsapp Mobile Number Subscription') ?></h2>
  <form method="post" action="?page=whatsapp-plugin" id="whatsapp_form_action">
    <p>
        <select name="whatsapp_action">
            <option value="actions"><?php _e('Actions')?></option>
            <option value="delete"><?php _e('Delete')?></option>
      </select>
      <input type="submit" name="whatsapp_form_action_changes" class="button-secondary" value="<?php _e('Apply')?>" />
        <input type="button" class="button-secondary" value="<?php _e('Add a new Number')?>" onclick="window.location='?page=whatsapp-plugin&amp;edit=true'" />
    </p>
    <table class="widefat page fixed" cellpadding="0">
      <thead>
        <tr>
        <th id="cb" class="manage-column column-cb check-column" style="" scope="col">
          <input type="checkbox"/>
        </th>
          <th class="manage-column"><?php _e('#')?></th>
          <th class="manage-column"><?php _e('Mobile Number')?></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
        <th id="cb" class="manage-column column-cb check-column" style="" scope="col">
          <input type="checkbox"/>
        </th>
           <th class="manage-column"><?php _e('#')?></th>
          <th class="manage-column"><?php _e('Mobile Number')?></th>
        </tr>
      </tfoot>
      <tbody>
        <?php
          $table = whatsapp_get_software();
          if($entries){
           $i=0;
           foreach($entries as $software) {
               $i++;
			   $rows=($paged-1)*$limit+$i;
        ?>
      <tr class="<?php echo (ceil($i/2) == ($i/2)) ? "" : "alternate"; ?>">
        <th class="check-column" scope="row">
          <input type="checkbox" value="<?php echo $software->id?>" name="whatsapp_id[]" />
        </th>
          <td>
          <strong><?php echo $rows?></strong>
          <div class="row-actions-visible">
          <span class="edit"><a href="?page=whatsapp-plugin&amp;paged=<?php echo $paged; ?>&amp;id=<?php echo $software->id?>&amp;edit=true">Edit</a> | </span>
          <span class="delete"><a href="?page=whatsapp-plugin&amp;delete=<?php echo $software->id?>" onclick="return confirm('Are you sure you want to delete this Number?');">Delete</a></span>
          </div>
          </td>
          <td><?php echo $software->mobile_no?></td>

        </tr>
        <?php
           }
        }
        else{  
      ?>
        <tr><td colspan="3"><?php _e('There are no data.')?></td></tr>  
        <?php
      }
        ?>  
      </tbody>
    </table>
    <p>
        <select name="whatsapp_action-2">
            <option value="actions"><?php _e('Actions')?></option>
            <option value="delete"><?php _e('Delete')?></option>
        </select>
        <input type="submit" name="whatsapp_form_action_changes-2" class="button-secondary" value="<?php _e('Apply')?>" />
        <input type="button" class="button-secondary" value="<?php _e('Add a new Number')?>" onclick="window.location='?page=whatsapp-plugin&amp;edit=true'" />
    </p>

  </form>
</div>
<?php
}
?>
