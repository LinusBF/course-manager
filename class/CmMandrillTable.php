<?php

/**
 * Created by PhpStorm.
 * User: Linus
 * Date: 2018-05-14
 * Time: 22:03
 */
class MandrillTable extends WP_List_Table{

	private $oMandrill = null;
	private $iCurrentTemplate = null;

	public function __construct($args = array() ) {
		parent::__construct($args);
		$this->oMandrill = new CmMandrillController();
		$oCM = new CourseManager();
		$this->iCurrentTemplate = $oCM->getOptions()['mandrill']['template_slug'];
	}

	public function get_columns() {
		$table_columns = array(
			'name' => TXT_CM_CHIMP_TABLE_LISTS_TITLE,
			'rb' => TXT_CM_STORE_TABLE_CHOICE,
		);

		return $table_columns;
	}

	public function column_name( $item) {
		return $item['name'];
	}

	protected function column_rb( $item ) {
		$current = ($this->iCurrentTemplate == $item['slug'] ? 'checked': '');
		return sprintf(
			"<input class='cm_mandrill_rb' type='radio' name='cm_md_template' id='{$item['slug']}' value='{$item['slug']}' {$current} />"
		);
	}

	public static function record_count()
	{
		return CmMandrillController::getNrOfTemplates();
	}

	public function prepare_items() {
		$this->_column_headers = array($this->get_columns(),array());

		$this->items = $this->oMandrill->getTemplatesInfo();
	}

	public function print_table(){
		?>
		<div class="wrap">
			<h3><?php echo TXT_CM_CHIMP_TABLE_TEMPLATE_DESC;?>
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