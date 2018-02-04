<?php
class SupportAppSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

  	public $support__reply_tickets = array(
			'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'unsigned' => false, 'key' => 'primary'),
			'ticket_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'unsigned' => false),
			'reply' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
			'author' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'unsigned' => false,),
			'created' => array('type' => 'string', 'length' => 50, 'null' => false, 'default' => null),
			'type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 11, 'unsigned' => false),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1)
			),
			'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
		);

	public $support__tickets = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'unsigned' => false, 'key' => 'primary'),
		'subject' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'author' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'unsigned' => false,),
		'state' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 1, 'unsigned' => false),
		'created' => array('type' => 'string', 'length' => 50, 'null' => false, 'default' => null),
		'reponse_text' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

}
