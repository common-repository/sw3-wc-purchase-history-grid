<?php

/* init */
if(is_admin()){
    add_action( 'admin_menu', 'phg_my_plugin_menu' );
    add_action( 'admin_init', 'phg_register_mysettings' );
}

/* Register settings */
function phg_register_mysettings() { 

    register_setting( 'purchase-history-grid', 'num_of_columns' , 'phg_validate_num_cols'); // settings group, option name/field name, sanitizer callback

    register_setting( 'purchase-history-grid', 'num_of_products', 'phg_validate_num_products' );

    register_setting( 'purchase-history-grid', 'cat_operator');

    register_setting( 'purchase-history-grid', 'phg_cats');

    register_setting( 'purchase-history-grid', 'phg_order_by');

    register_setting( 'purchase-history-grid', 'phg_order');

    register_setting( 'purchase-history-grid', 'product_visibility');

    register_setting( 'purchase-history-grid', 'phg_tags');

    register_setting( 'purchase-history-grid', 'tag_operator');

 

    add_settings_section(
        'plugin_section',
        __( 'The Grid', 'sw3-wc-purchase-history-grid' ),
        'phg_settings_section_callback',
        'purchase-history-grid'
    );

    add_settings_field(
        'num_of_columns', //id
        'Number of Columns', //label
        'phg_show_fields', //callback
        'purchase-history-grid', //settings group/page slug
        'plugin_section', //section
        array(
            'option' => 'num_of_columns'  //args to the callback function
        )
    );

    add_settings_field(
        'num_of_products',
        'Number of Products to show',
        'phg_show_fields',
        'purchase-history-grid',
        'plugin_section',
        array(
            'option' => 'num_of_products'
        )

    );

    add_settings_field(
        'phg_cats',
        'Select categories',
        'phg_render_terms',
        'purchase-history-grid',
        'plugin_section',
        array(
            'option' => 'phg_cats'
        )

    );

    add_settings_field(
        'cat_operator',
        'Category Operator',
        'phg_render_term_op',
        'purchase-history-grid',
        'plugin_section',
        array(
            'option' => 'cat_operator'
        )

    );

    add_settings_field(
        'phg_order_by',
        'Order By',
        'phg_render_order_by',
        'purchase-history-grid',
        'plugin_section'

    );

    add_settings_field(
        'phg_order',
        'Order',
        'phg_render_order',
        'purchase-history-grid',
        'plugin_section'

    );

    add_settings_field(
        'product_visibility',
        'Product Visibility',
        'phg_render_visiblity',
        'purchase-history-grid',
        'plugin_section'

    );

    add_settings_field(
        'phg_tags',
        'Select Tags',
        'phg_render_terms',
        'purchase-history-grid',
        'plugin_section',
        array(
            'option' => 'phg_tags'
        )

    );

    add_settings_field(
        'tag_operator',
        'Tag Operator',
        'phg_render_term_op',
        'purchase-history-grid',
        'plugin_section',
        array(
            'option' => 'tag_operator'
        )

    );

  }


/** Add the menu option */
function phg_my_plugin_menu() {

	add_options_page( 'Purchase History Grid', //The text to be displayed in the title tags of the page 
                      'Purchase History Grid', //The text to be used for the menu.
                      'manage_options', //The capability required for this menu to be displayed to the user.
                      'purchase-history-grid',//The slug name to refer to this menu by
                      'phg_my_plugin_options' //The function to be called to output the content for this page.
                    );

}

/** Render the form */
function phg_my_plugin_options() {

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    ?>
	<div class="wrap">
        <h2>Purchase History Grid by SolutionsW3</h2>
	    <form method="post" action="options.php"> 

    <?php

        settings_fields( 'purchase-history-grid' );
        do_settings_sections( 'purchase-history-grid' );

        submit_button(); 

    ?>

    </form> 
	</div>

<?php

}

/* Render the field */
function phg_show_fields($args) {

    // get the value of the setting we've registered with register_setting()
    $option = $args['option'];
    $setting = get_option($option);

    // output the field
    ?>
    <input type="text" id="<?php echo esc_attr($option); ?>" name="<?php echo esc_attr($option); ?>" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
    <?php
}

/* Render the terms checkboxes (cats and tags) */
function phg_render_terms($args){

    //Available options
    //phg_cats
    //phg_tags

    if(!isset($args['option'])) return; //function is being called w/o options param

    $taxanomy = ($args['option'] == 'phg_cats') ? 'product_cat' : 'product_tag';

    // obtain all the product terms
    $terms = get_terms( $taxanomy ,array("hide_empty" => false)); // array of WP_Term objects

    // WP_Term Object
    // (
    //     [term_id] => 75
    //     [name] => Breakfast
    //     [slug] => breakfast
    //     [term_group] => 0
    //     [term_taxonomy_id] => 75
    //     [taxonomy] => product_cat
    //     [description] => 
    //     [parent] => 0
    //     [count] => 5
    //     [filter] => raw
    // )

    $selected =  get_option($args['option']) ;

    foreach( $terms as $term){

        if(isset($selected[$term->slug]) && $selected[$term->slug] == 'on') {
    ?>  
            <div>
                <input type="checkbox" name="<?php echo $args['option'].'['.esc_attr($term->slug).']' ; ?>" checked="checked" val="<?php echo esc_attr($term->slug) ; ?>" />
                <label><?php echo esc_attr($term->name); ?></label>
            </div>
    <?php    

        }
        else {
    ?>
            <div>
                <input type="checkbox" name="<?php echo $args['option'].'['.esc_attr($term->slug).']' ; ?>"  val="<?php echo esc_attr($term->slug) ; ?>" />
                <label><?php echo esc_attr($term->name); ?></label>
            </div>
    <?php        
        }

    }// end for loop    

}

/* render the cat operator */
function phg_render_term_op($args){

    $term_operator = get_option($args['option']);

    // cat_operator – Operator to compare category terms. Available options are:
    //     AND – Will display products that belong in all of the chosen categories.
    //     IN – Will display products within the chosen category. This is the default cat_operator value.
    //     NOT IN – Will display products that are not in the chosen category.

    ?>

    <select name="<?php echo $args['option']; ?>" >
        <option value="AND" class="" <?php echo ($term_operator=='AND') ? esc_attr('selected') : '';  ?> >AND - Will display products that belong in all of the chosen term. </option>
        <option value="IN" class="" <?php echo ($term_operator=='IN') ? esc_attr('selected') : '';  ?> >IN - Will display products within the chosen term (default) </option>
        <option value="NOT IN" class="" <?php echo ($term_operator=='NOT IN') ? esc_attr('selected') : '';  ?> >NOT IN - Will display products that are not in the selected term. </option>
    </select>

    <?php
}

/* render the orderby */
function phg_render_order_by(){

    $order_by = get_option('phg_order_by');

    ?>

    <select name="phg_order_by" >
        <option value="date" class="" <?php echo ($order_by=='date') ? esc_attr('selected') : '';  ?> >The date the product was published</option>
        <option value="id" class="" <?php echo ($order_by=='id') ? esc_attr('selected') : '';  ?> >The post ID of the product</option>
        <option value="menu_order" class="" <?php echo ($order_by=='menu_order') ? esc_attr('selected') : '';  ?> >The Menu Order, if set (lower numbers display first)</option>
        <option value="popularity" class="" <?php echo ($order_by=='popularity') ? esc_attr('selected') : '';  ?> >The number of purchases</option>
        <option value="rand" class="" <?php echo ($order_by=='rand') ? esc_attr('selected') : '';  ?> >Random</option>
        <option value="rating" class="" <?php echo ($order_by=='rating') ? esc_attr('selected') : '';  ?> >The average product rating</option>
        <option value="title" class="" <?php echo ($order_by=='title') ? esc_attr('selected') : '';  ?> >The product title</option>
     
    </select>

    <?php
}

/* render the order field */
function phg_render_order(){

    $order = get_option('phg_order');

    ?>

    <select name="phg_order" >
        <option value="ASC" class="" <?php echo ($order=='ASC') ? esc_attr('selected') : '';  ?> >Ascending</option>
        <option value="DESC" class="" <?php echo ($order=='DESC') ? esc_attr('selected') : '';  ?> >Descending</option>     
    </select>

    <?php

}

/* render product visiblity */
function phg_render_visiblity(){

    $visibility = get_option('product_visibility'); 

    // 'visible (default)'
    // 'catalog'
    // 'search'
    // 'hidden'
    // 'featured'
 
    ?>

    <select name="product_visibility" >
        <option value="visible" class="" <?php echo ($visibility=='visible') ? esc_attr('selected') : '';  ?> >Visible – Products visible on shop and search results</option>
        <option value="catalog" class="" <?php echo ($visibility=='catalog') ? esc_attr('selected') : '';  ?> >Catalog – Products visible on the shop only, but not search results</option>
        <option value="search" class="" <?php echo ($visibility=='search') ? esc_attr('selected') : '';  ?> >Search – Products visible in search results only, but not on the shop</option>    
        <option value="hidden" class="" <?php echo ($visibility=='hidden') ? esc_attr('selected') : '';  ?> >hidden – Products that are hidden from both shop and search, accessible only by direct URL</option>    
        <option value="featured" class="" <?php echo ($visibility=='featured') ? esc_attr('selected') : '';  ?> >Featured – Products that are marked as Featured Products</option>         
    </select>

    <?php
                                
    
}


/* THE END OF FIELD RENDERING FUNCTIONS */

/* section description */
function phg_settings_section_callback(  ) {
    echo  __( 'Please configure the grid here and use the shortcode <strong>[my_purchased_products]</strong> to render the grid.', 'sw3-wc-purchase-history-grid' );
}

function phg_validate_num_cols($input){

    if(!is_numeric($input)){
        add_settings_error(
            'num_of_columns', //Slug title of the setting
            esc_attr( 'settings_updated' ), //Slug-name to identify the error
            esc_html('Number of columns must be a number'), //The formatted message
            'error' //'error', 'success', 'warning', 'info'.
        );

        return 3;//default
    }  
    
    return $input;

}

function phg_validate_num_products($input){

    if(!is_numeric($input)){
        add_settings_error(
            'num_of_products',
            esc_attr( 'settings_updated' ),
            esc_html('Number of products must be a number'),
            'error'
        );

        return 6;
    }  
    
    return $input;
}

?>