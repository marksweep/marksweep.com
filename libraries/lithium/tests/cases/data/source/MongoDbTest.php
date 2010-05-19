<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 *
 */

namespace lithium\tests\cases\data\source;

use \lithium\data\source\MongoDb;

use \MongoId;
use \lithium\data\Connections;
use \lithium\data\Model;
use \lithium\data\model\Query;
use lithium\tests\mocks\data\MockPost;
use \lithium\data\collection\Document;

class MongoDbTest extends \lithium\test\Unit {

	protected $_testConfig = array(
		'type' => 'MongoDb',
		'adapter' => false,
		'database' => 'lithium_test',
		'host' => 'localhost',
		'port' => '27017',
		'persistent' => false
	);

	public function skip() {
		$this->skipIf(!MongoDb::enabled(), 'MongoDb Extension is not loaded');

		$db = new MongoDb($this->_testConfig);
		$message = "`{$this->_testConfig['database']}` database or connection unavailable";
		$this->skipIf(!$db->isConnected(), $message);

		Connections::add('lithium_mongo_test', array($this->_testConfig));
	}

	public function setUp() {
		$this->db = Connections::get('lithium_mongo_test');
		$model = '\lithium\tests\mocks\data\source\MockMongoPost';

		$this->query = new Query(compact('model') + array(
			'record' => new Document(compact('model'))
		));
	}

	public function tearDown() {
		unset($this->query);
		$this->db->dropDB('lithium_test');
	}

	public function testBadConnection() {
		$db = new MongoDb(array('host' => null, 'autoConnect' => false));
		$this->assertFalse($db->connect());
		$this->assertTrue($db->disconnect());
	}

	public function testGoodConnectionBadDatabase() {
		$db = new MongoDb(array('database' => null, 'autoConnnect' => false));
		$this->assertFalse($db->connect());
	}

	public function testGoodConnectionGoodDatabase() {
		$db = new MongoDb(array('autoConnect' => false) + $this->_testConfig);
		$this->assertTrue($db->connect());
	}

	public function testEntities() {
		$result = $this->db->entities();
		$expected = array();
		$this->assertEqual($expected, $result);
	}

	public function testDescribe() {
		$result = $this->db->describe('test');
		$expected = array();
		$this->assertEqual($expected, $result);
	}

	public function testName() {
		$result = $this->db->name('{(\'Li\':"∆")}');
		$expected = '{(\'Li\':"∆")}';
		$this->assertEqual($expected, $result);
	}

	public function testSchema() {
		$result = $this->db->schema($this->query);
		$expected = array();
		$this->assertEqual($expected, $result);
	}

	public function testCreateFail() {
		$this->expectException('no elements in doc');
		$result = $this->db->create($this->query);
	}

	public function testCreateSuccess() {
		$this->query->data(array('title' => 'Test Post'));
		$result = $this->db->create($this->query);
		$this->assertTrue($result);
	}

	public function testConditions() {
		$result = $this->db->conditions(null, null);
		$this->assertEqual(array(), $result);

		$function = 'function() { return this.x < y;}';
		$conditions = new \MongoCode($function);
		$result = $this->db->conditions($conditions, null);

		$this->assertTrue(is_array($result));
		$this->assertTrue(isset($result['$where']));
		$this->assertEqual($conditions, $result['$where']);

		$conditions = $function;
		$result = $this->db->conditions($conditions, null);
		$this->assertTrue(is_array($result));
		$this->assertTrue(isset($result['$where']));
		$this->assertEqual($conditions, $result['$where']);

		$conditions = array('key' => 'value', 'anotherkey' => 'some other value');
		$result = $this->db->conditions($conditions, null);
		$this->assertTrue(is_array($result));
		$this->assertEqual($conditions, $result);

		$conditions = array('key' => array('one', 'two', 'three'));
		$result = $this->db->conditions($conditions, null);
		$this->assertTrue(is_array($result));
		$this->assertTrue(isset($result['key']));
		$this->assertTrue(isset($result['key']['$in']));
		$this->assertEqual($conditions['key'], $result['key']['$in']);

		$conditions = array('$where' => array('some' => 'value'));
		$result = $this->db->conditions($conditions, null);
		$this->assertTrue(is_array($result));
		$this->assertEqual($conditions, $result);
	}

	public function testMongoConditionalOperators() {
		$conditions = array('key' => array('<' => 10));
		$expected = array('key' => array('$lt' => 10));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);

		$conditions = array('key' => array('<=' => 10));
		$expected = array('key' => array('$lte' => 10));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);

		$conditions = array('key' => array('>' => 10));
		$expected = array('key' => array('$gt' => 10));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);

		$conditions = array('key' => array('>=' => 10));
		$expected = array('key' => array('$gte' => 10));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);

		$conditions = array('key' => array('!=' => 10));
		$expected = array('key' => array('$ne' => 10));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);

		$conditions = array('key' => array('<>' => 10));
		$expected = array('key' => array('$ne' => 10));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);

		$conditions = array('key' => array('!=' => array(10, 20, 30)));
		$expected = array('key' => array('$nin' => array(10, 20, 30)));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);

		$conditions = array('key' => array('<>' => array(10, 20, 30)));
		$expected = array('key' => array('$nin' => array(10, 20, 30)));
		$result = $this->db->conditions($conditions, null);
		$this->assertEqual($expected, $result);
	}

	public function testReadNoConditions() {
		$data = array('title' => 'Test Post');
		$this->query->data($data);
		$this->db->create($this->query);

		$result = $this->db->read($this->query);
		$this->assertTrue($result);

		$expected = 1;
		$this->assertEqual($expected, $result->count());

		$expected = $data['title'];
		$this->assertEqual($expected, $result->first()->title);
	}

	public function testReadWithConditions() {
		$data = array('title' => 'Test Post');
		$this->query->data($data);
		$this->db->create($this->query);
		$this->query->data(null);

		$this->query->conditions(array('title' => 'Nonexistent Post'));
		$result = $this->db->read($this->query);
		$this->assertTrue($result);

		$expected = 0;
		$this->assertEqual($expected, $result->count());

		$this->query->conditions($data);
		$result = $this->db->read($this->query);
		$this->assertTrue($result);

		$expected = 1;
		$this->assertEqual($expected, $result->count());
	}

	public function testUpdate() {
		$data = array('title' => 'Test Post');
		$this->query->data($data);
		$this->db->create($this->query);

		$result = $this->db->read($this->query);
		$original = $result->first()->to('array');

		$model = '\lithium\tests\mocks\data\source\MockMongoPost';
		$this->query = new Query(compact('model') + array(
			'record' => new Document(compact('model'))
		));
		$newData = array('title' => 'New Post Title');
		$this->query->data($newData);
		$this->query->conditions(array('_id' => $original['_id']));

		$result = $this->db->update($this->query);
		$this->assertTrue($result);

		$result = $this->db->read($this->query);

		$expected = 1;
		$this->assertEqual($expected, $result->count());

		$updated = $result->first()->to('array');
		$expected = $original['_id'];
		$result = $updated['_id'];
		$this->assertEqual($expected, $result);

		$expected = $newData['title'];
		$result = $updated['title'];
		$this->assertEqual($expected, $result);
	}

	public function testDelete() {
		$data = array('title' => 'Delete Me');
		$this->query->data($data);
		$this->db->create($this->query);

		$result = $this->db->read($this->query);
		$expected = 1;
		$this->assertEqual($expected, $result->count());

		$record = $result->first()->to('array');

		$model = '\lithium\tests\mocks\data\source\MockMongoPost';
		$this->query = new Query(compact('model') + array(
			'record' => new Document(compact('model'))
		));
		$this->query->conditions(array('_id' => $record['_id']));
		$result = $this->db->delete($this->query);

		$this->assertTrue($result);

		$result = $this->db->read($this->query);
		$this->assertTrue($result);

		$expected = 0;
		$this->assertEqual($expected, $result->count());
	}

	public function testItem() {
		$model = '\lithium\tests\mocks\data\source\MockMongoPost';
		$data = array('title' => 'New Item');
		$result = $this->db->item($model, $data);

		$this->assertTrue($result instanceof \lithium\data\collection\Document);

		$expected = $data;
		$result = $result->to('array');
		$this->assertEqual($expected, $result);
	}

	public function testCalculation() {
		$result = $this->db->calculation('count', $this->query);
		$expected = 0;
		$this->assertEqual($expected, $result);
	}

	public function testEnabled() {
		$this->assertTrue(MongoDb::enabled());
	}

	public function testArbitraryMethodCalls() {
		$config = $this->_testConfig;
		$this->assertEqual("{$config['host']}:{$config['port']}", $this->db->__toString());
		$this->assertTrue(is_array($this->db->listDBs()));
	}

	public function testDocumentSorting() {
		$model = '\lithium\tests\mocks\data\source\MockMongoPost';
		$model::config(array('connection' => 'lithium_mongo_test', 'source' => 'ordered_docs'));

		$model::create(array('title' => 'Third document',  'position' => 3))->save();
		$model::create(array('title' => 'First document',  'position' => 1))->save();
		$model::create(array('title' => 'Second document', 'position' => 2))->save();

		$documents = $model::all(array('order' => 'position'));

		$this->assertEqual('First document', $documents[0]->title);
		$this->assertEqual('Second document', $documents[1]->title);
		$this->assertEqual('Third document', $documents[2]->title);

		$documents = $model::all(array('order' => array('position' => 'asc')));

		$this->assertEqual('First document', $documents[0]->title);
		$this->assertEqual('Second document', $documents[1]->title);
		$this->assertEqual('Third document', $documents[2]->title);

		$copy = $model::all(array('order' => array('position')));
		$this->assertIdentical($documents->data(), $copy->data());

		$documents = $model::all(array('order' => array('position' => 'desc')));

		$this->assertEqual('Third document', $documents[0]->title);
		$this->assertEqual('Second document', $documents[1]->title);
		$this->assertEqual('First document', $documents[2]->title);

		foreach ($documents as $i => $doc) {
			$this->assertTrue($doc->delete());
		}
	}

	public function testMongoIdPreservation() {
		$model = '\lithium\tests\mocks\data\source\MockMongoPost';
		$model::config(array('connection' => 'lithium_mongo_test', 'source' => 'ordered_docs'));

		$post = $model::create(array('title' => 'A post'));
		$post->save();

		$id = $post->_id;
		$this->assertTrue(is_string($id));

		$data = Connections::get('lithium_mongo_test')->connection->ordered_docs->findOne(array(
			'_id' => new MongoId($id)
		));
		$this->assertEqual('A post', $data['title']);
		$this->assertEqual($id, (string) $data['_id']);
		$this->assertTrue($data['_id'] instanceof MongoId);

		$post->title = 'An updated post';
		$post->save();

		$data = Connections::get('lithium_mongo_test')->connection->ordered_docs->findOne(array(
			'_id' => new MongoId($id)
		));
		$this->assertEqual('An updated post', $data['title']);
		$this->assertEqual($id, (string) $data['_id']);
	}

	public function testRelationshipGeneration() {
		Connections::add('mock-source', $this->_testConfig);
		$model = 'lithium\tests\mocks\data\MockComment';
		$result = $this->db->relationship($model, 'belongsTo', 'MockPost');
		$expected = array(
			'name' => 'MockPost',
			'type' => 'belongsTo',
			'key' => 'mockComment',
			'from' => 'lithium\tests\mocks\data\MockComment',
			'link' => 'contained',
			'to' => 'lithium\tests\mocks\data\MockPost',
			'scope' => null,
			'fields' => true,
			'fieldName' => 'mockPost',
			'init' => true
		);
		$this->assertEqual($expected, $result->data());
	}
}

?>