<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Tag.php');


class TestOfTagClass extends UnitTestCase {
	
	function setUp() {
		$this->tag = new Tag();
	}
	
	function tearDown() {
		$this->tag->delete();
	}
	
	function testTagClass() {
		$this->assertIsA($this->tag, 'Tag');
	}
	
	function testTagId() {
		$this->assertEqual($this->tag->get_id(), 0);
	}
	
	function testTagName() {
		$name = 'Test';
		$this->tag->set_name($name);
		$this->assertEqual($this->tag->get_name(), $name);
        $badname = '<script>alert("xss");</script>';
        $this->tag->set_name($badname);
        $this->assertNotEqual($badname, $this->tag->get_name());
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->tag->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->tag->get_collection_definition(), 'String');
	}
	
	function testGetDisplays() {
		$this->assertIsA($this->tag->get_displays(), 'Array');
	}
	
	function testSaveDelete() {
		$name = 'Test';
		$this->tag->set_name($name);
		$this->assertTrue($this->tag->save());
		$id = $this->tag->get_id();
		$this->assertTrue($id > 0 );
		$newTag = new Tag($id);
		$this->assertEqual($newTag->get_name(), $name);
		$this->assertTrue($newTag->delete());
	}
}
?>