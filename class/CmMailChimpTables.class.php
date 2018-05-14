<?php
/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-02-17
 * Time: 14:42
 */

//check for WP_List_Table
if (!class_exists("WP_List_Table")) {
	require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

class MailChimpTable extends WP_List_Table{

	private $sType = null;
	private $iCurrentId = null;

	public function __construct($sItemType = "list", $iCourseId = -1, $args = array() ) {
		parent::__construct($args);
		$this->sType = $sItemType;
		switch ($this->sType){
			case "group":
				$this->iCurrentId = CmMailController::getGroupCatId($iCourseId);
				break;
			case "template":
				$this->iCurrentId = CmMailController::getTemplateId();
				break;
			default:
				$this->iCurrentId = CmMailController::getListId();
		}
	}


	public function get_columns() {
		$table_columns = array(
			'name' => TXT_CM_CHIMP_TABLE_LISTS_TITLE,
			'rb' => TXT_CM_STORE_TABLE_CHOICE,
		);

		return $table_columns;
	}

	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}


	public function column_item_id( $item ) {
		return $item->id;
	}

	public function column_name( $item) {
		return ($this->sType === "group" ? $item->title : $item->name);
	}

	protected function column_rb( $item ) {
		$current = ($this->iCurrentId == $item->id ? 'checked': '');

		$sElemId = $this->sType."_".$item->id;
		if($this->sType !== "group") {
			return sprintf(
				"<input class='cm_mc_{$this->sType}_rb' type='radio' name='cm_mc_{$this->sType}' id='{$sElemId}' value='{$item->id}' {$current} />"
			);
		} else{
			return sprintf(
				"<input class='cm_mc_{$this->sType}_rb' type='radio' name='mc_group_category' id='{$sElemId}' value='{$item->id}' {$current} />"
			);
		}
	}

	public static function record_count($sType)
	{
		switch ($sType){
			case "group":
				$oItems = CmMailController::getGroups(0, 999, true);
				break;
			case "template":
				$oItems = CmMailController::getTemplates(0, 999, true);
				break;
			default:
				$oItems = CmMailController::getLists(0, 999, true);
		}
		return $oItems->total_items;
	}

	public function prepare_items() {
		$aColumns = $this->get_columns();
		$aHidden = array("item_id");

		$this->_column_headers = array($aColumns,$aHidden);

		$iPerPage = $this->get_items_per_page("lists_per_page", 10);
		$iCurrPage = $this->get_pagenum();
		$iTotalItems = $this->record_count($this->sType);

		$this->set_pagination_args([
			'total_items' => $iTotalItems,
			'per_page' => $iPerPage
		]);

		$this->items = $this->fetch_table_data($iCurrPage, $iPerPage);
	}

	private function fetch_table_data($page = 0, $per_page = 10) {
		switch ($this->sType){
			case "group":
				$results = CmMailController::getGroups($page - 1, $per_page);
				break;
			case "template":
				$results = CmMailController::getTemplates($page - 1, $per_page);
				break;
			default:
				$results = CmMailController::getLists($page - 1, $per_page);
		}

		return $results;
	}

	public function print_table(){
		?>
		<div class="wrap">
			<h3>
			<?php
				switch ($this->sType){
					case "group":
						echo TXT_CM_CHIMP_TABLE_GROUPS_DESC;
						break;
					case "template":
						echo TXT_CM_CHIMP_TABLE_TEMPLATE_DESC;
						break;
					default:
						echo TXT_CM_CHIMP_TABLE_LISTS_DESC;
				}
			?>
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