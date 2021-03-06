<?php 
/**
 * HECTOR - class.Ossec_Rule.php
 * 
 * @author Ubani A Balogun <ubani@sas.upenn.edu>
 * @package HECTOR
 * 
 */

/**
 *  Error reporting
 */

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
	die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');

/**
 * The Ossec_Rule class handles interactions with the HECTOR ossec_rule table
 * 
 * @package HECTOR
 * @author Ubani A Balogun <ubani@sas.upenn.edu>
 */

class Ossec_Rule extends Maleable_Object {
	// -- Attributes --
	
	/**
	 *  Instance of the Db
	 *
	 *  @access private
	 *  @var Db An instance of the Db
	 */
	private $db = null;
	
	/**
	 * Instance of the Log
	 *
	 * @access private
	 * @var Log An instance of the Log
	 */
	private $log = null;
	
	/**
	 * Unique id from the data layer
	 *
	 * @access protected
	 * @var int Unique id
	 */
	protected $id = null;
	
	/**
	 *  The ossec rule number
	 *   
	 *  @access private
	 *  @var Int The ossec rule number
	 */
	private $rule_number;
	
	/**
	 * The rule level
	 * 
	 * @access private 
	 * @var Int the ossec rule level
	 */
	private $rule_level;
	
	/**
	 * The rule message
	 * 
	 * @access private
	 * @var Int the ossec rule message
	 */
	private $rule_message;
	
	
	/**
	 * Contruct a new Ossec Rule or instanstiate one from the data layer by ID
	 *
	 * @access public
	 * @param Int the unique ID of the OSSEC Rule
	 * @return void
	 */
	public function __construct($id=''){
		$this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id !=''){
			$sql = array(
					'SELECT rule_id as ossec_rule_id, rule_number, rule_level, rule_message
					FROM ossec_rule r WHERE r.rule_id = ?i',
					$id
			);
			$result = $this->db->fetch_object_array($sql);
			if (is_object($result[0])){
				$r = $result[0];
				$this->set_id($r->ossec_rule_id);
				$this->set_rule_number($r->rule_number);
				$this->set_rule_level($r->rule_level);
				$this->set_rule_message($r->rule_message);
			}
		}
	}
	
	/**
	 *  Set the id attribute.
	 *
	 *  @access protected
	 *  @param Int The unique ID from the data layer
	 */
	protected function set_id($id){
		$this->id = intval($id);
	}
	
	/**
	 *  Get the unique ID for the object
	 *
	 *  @access public
	 *  @return Int The unique ID of the object
	 */
	public function get_id(){
		return intval($this->id);
	}
	
	/**
	 * Set the rule number attribute
	 * 
	 * @access public
	 * @param Int the rule number
	 */
	public function set_rule_number($number){
		$this->rule_number = intval($number);
	}
	
	/**
	 * Get the rule number
	 * 
	 * @access public
	 * @return Int the rule_number
	 */
	public function get_rule_number(){
		return $this->rule_number;
	}
	
	/**
	 * Set the rule level
	 * 
	 * @access public 
	 * @param Int the rule level
	 */
	public function set_rule_level($level){
		$this->rule_level = intval($level);
	}
	
	/**
	 * Get the rule level
	 * 
	 * @access public
	 * @return Int the rule level
	 */
	public function get_rule_level(){
		return $this->rule_level;
	}
	
	/**
	 * Set the rule message
	 * 
	 * @access public
	 * @param String the rule message
	 */
	public function set_rule_message($message){
		$this->rule_message = $message;
	}
	
	/**
	 * Get the rule message
	 * 
	 * @access public 
	 * @return String the html safe rule message
	 */
	public function get_rule_message(){
		return htmlspecialchars($this->rule_message);	
	}
	
	/**
	 *  This function directly supports the Collection class.
	 *
	 *  @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = ''){
		$sql = 'SELECT r.rule_id as ossec_rule_id FROM ossec_rule r WHERE r.rule_id > 0';
		if ($filter != '' && is_array($filter))  {
			$sql .= ' ' . array_shift($filter);
			$sql = $this->db->parse_query(array($sql, $filter));
		}
		if ($filter != '' && ! is_array($filter))  {
			$sql .= ' ' . $filter . ' ';
		}
		if ($orderby != '') {
			$sql .= ' ' . $orderby;
		}
		else if ($orderby == '') {
			$sql .= ' ORDER BY r.rule_id asc';
		}
		return $sql;
	}
	
	/**
	 * This function returns the object as an associative array
	 * 
	 * @access public
	 * @return Array an associative array of the objects attributes
	 */
	public function get_object_as_array(){
		return array(
				'id' => $this->get_id(),
				'rule_number' => $this->get_rule_number(),
				'rule_level' => $this->get_rule_level(),
				'rule_message' => $this->get_rule_message(),
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

?>