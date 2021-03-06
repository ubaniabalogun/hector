<?php
/**
 * HECTOR - class.Tag.php
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Error reporting
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
 * Tags are free taxonomies used to group hosts.
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Tag extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;

    /**
     * Unique ID from the data layer
     *
     * @access protected
     * @var int Unique id
     */
    protected $id = null;

	/**
	 * Tag name
	 * 
	 * @access private
	 * @var String The name of the tag
	 */
    private $name;
    
    /**
     * Instance of the Log
     * 
     * @access private
     * @var Log An instance of the Log
     */
    private $log = null;

	/**
	 * Hosts associated with this tag.  This
	 * is just a convenience (for reporting).
	 * There is no interface for altering this
	 * attribute.
	 * 
	 * @access public
	 * @var Array The host_ids for Host objects
	 */
    public $host_ids = array();

    // --- OPERATIONS ---

    /**
     * Construct a new blank Tag or instantiate one
     * from the data layer based on ID
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The unique ID of the Tag
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
		$this->log = Log::get_instance();
		if ($id != '') {
			$sql = array(
				'SELECT * FROM tag WHERE tag_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
			$this->set_id($result[0]->tag_id);
			$this->set_name($result[0]->tag_name);
			$sql = array(
				'SELECT host_id FROM host_x_tag WHERE tag_id = ?i',
				$id
			);
			$result = $this->db->fetch_object_array($sql);
	    	if (is_array($result) && count($result) > 0) {
	    		foreach($result as $row) {
	    			$this->host_ids[] = $row->host_id;
	    		}
	    	}
		}
    }


    /**
     * Delete the record from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
    public function delete() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Delete an existing record
	    	$sql = array(
	    		'DELETE FROM tag WHERE tag_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Delete mappings
	    	$sql = array(
	    		'DELETE FROM host_x_tag WHERE tag_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM vuln_x_tag WHERE tag_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM malware_x_tag WHERE tag_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM article_x_tag WHERE tag_id = \'?i\'',
	    		$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
	    	$sql = array(
	    		'DELETE FROM incident_x_tag WHERE tag_id = \'?i\'',
	    			$this->get_id()
	    	);
	    	$this->db->iud_sql($sql);
    	}
    	return $retval;
    }

	/**
	 * This is a functional method designed to return
	 * the form associated with altering a tag.
	 * 
	 * @access public
	 * @return Array The array for the default CRUD template.
	 */
	public function get_add_alter_form() {
		return array (
			array('label'=>'Tag name',
					'type'=>'text',
					'name'=>'tagname',
					'value_function'=>'get_name',
					'process_callback'=>'set_name')
		);
	}
    
    /**
     * Returns an array of article ids mapped to this tag
     * 
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return Array an array of article ids (int)
     */
    public function get_article_ids(){
    	$retval = array();
    	$sql = array(
    			'SELECT article_id FROM article_x_tag WHERE tag_id = ?i AND article_id > 0',
    			$this->get_id()
    	);
    	$result = $this->db->fetch_object_array($sql);
    	if (isset($result[0])){
    		foreach ($result as $row){
    			$retval[] = $row->article_id;
    		}
    	}
    	return $retval;
    }

    /**
     *  This function directly supports the Collection class.
	 *
	 * @return String SQL select string
	 */
	public function get_collection_definition($filter = '', $orderby = '') {
		$query_args = array();
		$sql = 'SELECT t.tag_id FROM tag t WHERE t.tag_id > 0';
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
			$sql .= ' ORDER BY t.tag_name';
		}
		return $sql;
	}

	/**
	 * Get the displays for the default details template
	 * 
	 * @return Array Dispalays for default template
	 */
	public function get_displays() {
		return array('Name'=>'get_linked_name');
	}
    
    /**
     * Returns an array of host ids mapped to this tag
     * 
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return Array an array of host ids (int)
     */
    public function get_host_ids(){
    	$retval = array();
    	$sql = array(
    			'SELECT host_id FROM host_x_tag WHERE tag_id = ?i AND host_id > 0',
    			$this->get_id()
    	);
    	$result = $this->db->fetch_object_array($sql);
    	if (isset($result[0])){
    		foreach ($result as $row){
    			$retval[] = $row->host_id;
    		}
    	}
    	return $retval;
    }

    /**
     * Get the unique ID for the object
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The unique ID of the object
     */
    public function get_id() {
       return intval($this->id);
    }
    
    /**
     * Returns an array of incident ids mapped to the tag id
     * 
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return Array of incident ids (int)
     */
    public function get_incident_ids(){
    	$retval = array();
    	$sql = array(
    		'SELECT incident_id FROM incident_x_tag WHERE tag_id = ?i AND incident_id > 0',
    			$this->get_id()
    	);
    	$result = $this->db->fetch_object_array($sql);
    	if (isset($result[0])){
    		foreach ($result as $row){
    			$retval[] = $row->incident_id;
    		}
    	}
    	return $retval;
    }
    
    /**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Tag';
    } 
    
    /**
     * Looks up a Tag by name, creating it if it doesn't exist
     * 
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param String The name of the Tag to search for
     * @return Int the id of the tag. 0 on an error
     */
    public function lookup_by_name($name){
    	$sql = array(
    		'SELECT tag_id FROM tag WHERE tag_name = \'?s\'',
    			$name
    	);
    	$result = $this->db->fetch_object_array($sql);
    	// Found an existing Tag
    	if (isset($result[0])){
    		$this->__construct($result[0]->tag_id);
    	}
    	// Create a new Tag
    	else {
    		$this->set_name($name);
    		$this->save();
    	}
    	return $this->get_id();
    }
    
    /**
     * The HTML linked title of the Tag
     *  
     *  @access public 
     *  @author Ubani A Balogun <ubani@sas.upenn.edu>
     *  @return String the HTML linked safe title of the Article
     */
    public function get_linked_name(){
    	return "<a href='?action=tag_details&id=$this->id'>" . $this->get_name() . "</a>";
    }

	/**
	 * The HTML safe name of the Tag
	 * 
	 * @access public
	 * @return String The HTML display safe name of the Tag.
	 */
    public function get_name() {
		return htmlspecialchars($this->name);
    }
    
    /**
     * Returns the tag object as an array
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return Array an associative array of tag attributes 
     */
    public function get_object_as_array(){
    	return array(
    			'id' => $this->get_id(),
    			'name' => $this->get_name(),
    	);
    }
    
    /**
     * Returns an array of vulnerability ids mapped to this tag
     *
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return Array an array of article ids (int)
     */
    public function get_vuln_ids(){
    	$retval = array();
    	$sql = array(
    			'SELECT vuln_id FROM vuln_x_tag WHERE tag_id = ?i AND vuln_id > 0',
    			$this->get_id()
    	);
    	$result = $this->db->fetch_object_array($sql);
    	if (isset($result[0])){
    		foreach ($result as $row){
    			$retval[] = $row->vuln_id;
    		}
    	}
    	return $retval;
    }
    
	/**
	 * Persist the Tag to the data layer
	 * 
	 * @access public
	 * @return Boolean True if everything worked, FALSE on error.
	 */
    public function save() {
    	$retval = FALSE;
    	if ($this->id > 0 ) {
    		// Update an existing tag
	    	$sql = array(
	    		'UPDATE tag SET tag_name = \'?s\' WHERE tag_id = \'?i\'',
	    		$this->get_name(),
	    		$this->get_id()
	    	);
	    	$retval = $this->db->iud_sql($sql);
    	}
    	else {
    		$sql = array(
				'INSERT INTO tag SET tag_name = \'?s\'',
    			$this->get_name()
	    	);
	    	$retval = $this->db->iud_sql($sql);
	    	// Now set the id
	    	$sql = 'SELECT LAST_INSERT_ID() AS last_id';
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0]) && $result[0]->last_id > 0) {
	    		$this->set_id($result[0]->last_id);
	    	}
	    	// Auto update all content with this tag 
	    	$sql = array('SELECT article_id ' .
	    					'FROM article ' .
	    					'WHERE lower(article_title) LIKE \'%?s%\' ' .
	    					'OR lower(article_teaser) LIKE \'%?s%\' ' .
	    					'OR lower(article_body) LIKE \'%?s%\'',
	    					strtolower($this->get_name()),
	    					strtolower($this->get_name()),
	    					strtolower($this->get_name()) );
	    	$result = $this->db->fetch_object_array($sql);
	    	if (isset($result[0])) {
	    		foreach ($result as $row) {
	    			$sql = array('INSERT INTO article_x_tag SET article_id = ?i, tag_id = ?i',
	    						$row->article_id,
	    						$this->get_id());
	    			$this->db->iud_sql($sql);
	    		}
	    	}
    	}
    	return $retval;
    }
    
    /**
     * Set the id attribute.
     * 
     * @access protected
     * @param Int The unique ID from the data layer
     */
    protected function set_id($id) {
    	$this->id = intval($id);
    }

	/**
	 * Set the name of the Tag
	 * 
	 * @access public
	 * @param String The name of the tag
	 */
    public function set_name($name) {
    	$this->name = $name;
    }
    

} /* end of class Tag */

?>