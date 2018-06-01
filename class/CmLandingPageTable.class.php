<?php

/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2017-12-09
 * Time: 19:28
 */

//check for WP_List_Table
if (!class_exists("WP_List_Table")) {
	require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

class LandingPageTable extends WP_List_Table{


	public function get_columns() {
		$table_columns = array(
			'page_id' => TXT_CM_ID,
			'title' => TXT_CM_STORE_TABLE_PAGE_TITLE,
			'rb' => TXT_CM_STORE_TABLE_CHOICE,
		);

		return $table_columns;
	}


	public function column_page_id( $item ) {
		return $item->ID;
	}

	public function column_title( $item) {
		return $item->post_title;
	}

	protected function column_rb( $item ) {
		$options = CmCourseStoreHandler::getStoreOptionsForCourse($_GET['course']);

		$current = ($options['landing_page'] == $item->ID ? 'checked': '');

		return sprintf(
			"<input type='radio' name='landing_page' id='page_{$item->ID}' value='{$item->ID}' {$current} />"
		);
	}

	public static function record_count()
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."posts"." WHERE post_type = 'page' AND post_name != 'course-store'";

		return $wpdb->get_var($sql);
	}

	public function prepare_items() {
		$aColumns = $this->get_columns();
		$aHidden = array();

		$this->_column_headers = array($aColumns,$aHidden);

		//$this->process_bulk_action();

		$iPerPage = 999;

		$this->items = $this->fetch_table_data(1, $iPerPage);

		$iTotalItems = count($this->items);

		$this->set_pagination_args([
			'total_items' => $iTotalItems,
			'per_page' => $iPerPage
		]);
	}

	private function fetch_table_data($page = 1, $per_page = 999) {
		global $wpdb;

		$filter_query = "SELECT meta_value
						FROM ".DB_CM_STORE_META."
						WHERE meta_key = 'landing_page'";
		$filter_values = $wpdb->get_col($filter_query);

		$wpdb_table = $wpdb->prefix . 'posts';
		$orderby = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'ID';
		$order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'ASC';

		$query = "SELECT post_title, ID 
				  FROM $wpdb_table 
				  WHERE post_type = 'page' AND post_name != 'course-store' 
				  ORDER BY %s %s 
				  LIMIT %d
				  OFFSET %d";

		$results = $wpdb->get_results($wpdb->prepare($query, $orderby, $order, $per_page, ($page - 1)*$per_page));

		$filtered_result = array();
		$options = CmCourseStoreHandler::getStoreOptionsForCourse($_GET['course']);

		foreach ($results as $result){
			if (!in_array($result->ID, $filter_values) || $result->ID == $options['landing_page']){
				array_push($filtered_result, $result);
			}
		}

		return $filtered_result;
	}

	public function print_landing_page_table(){
		?>
		<div class="wrap">
			<h3><?php echo TXT_CM_STORE_SELECT_LADNING_PAGE;?>
			</h3>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<?php
							$this->prepare_items();
							$this->display();
							?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}
}