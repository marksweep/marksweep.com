<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\g11n\catalog\adapter;

use \Exception;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \lithium\tests\mocks\g11n\catalog\adapter\MockGettext;

class GettextTest extends \lithium\test\Unit {

	public $adapter;

	protected $_path;

	public function skip() {
		$path = LITHIUM_APP_PATH . '/resources/tmp/tests';
		$message = "Path {$path} is not writable.";
		$this->skipIf(!is_writable($path), $message);
	}

	public function setUp() {
		$this->_path = $path = LITHIUM_APP_PATH . '/resources/tmp/tests';
		mkdir("{$this->_path}/en/LC_MESSAGES", 0755, true);
		mkdir("{$this->_path}/de/LC_MESSAGES", 0755, true);
		$this->adapter = new MockGettext(compact('path'));
	}

	public function tearDown() {
		$this->_cleanUp();
	}

	public function testPathMustExist() {
		try {
			new MockGettext(array('path' => $this->_path));
			$result = true;
		} catch (Exception $e) {
			$result = false;
		}
		$this->assert($result);

		try {
			new MockGettext(array('path' => "{$this->_path}/i_do_not_exist"));
			$result = false;
		} catch (Exception $e) {
			$result = true;
		}
		$this->assert($result);
	}

	public function testReadNonExistent() {
		$result = $this->adapter->read('messageTemplate', 'root', null);
		$this->assertFalse($result);
	}

	public function testReadUnreadable() {
		$message = 'Permissions cannot be modified on Windows.';
		$this->skipIf(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN', $message);

		$data = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1', 'plural' => 'plural 1'),
				'flags' => array('fuzzy' => true),
				'translated' => array(),
				'occurrences' => array(
					array('file' => 'test.php', 'line' => 1)
				),
				'comments' => array(
					'comment 1'
				),
			)
		);

		$this->adapter->mo = false;
		$this->adapter->write('messageTemplate', 'root', null, $data);
		chmod("{$this->_path}/message_default.pot", 0222);
		$this->adapter->mo = true;

		$result = $this->adapter->read('messageTemplate', 'root', null);
		$this->assertNull($result);
	}

	public function testReadPoSingleItem() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<EOD
msgid "singular 1"
msgstr "translated 1"
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1'),
				'flags' => array(),
				'translated' => array('translated 1'),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);
	}

	public function testReadPoMultipleItems() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<EOD
msgid "singular 1"
msgstr "translated 1"

msgid "singular 2"
msgstr "translated 2"
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1'),
				'flags' => array(),
				'translated' => array('translated 1'),
				'occurrences' => array(),
				'comments' => array()
			),
			'singular 2' => array(
				'id' => 'singular 2',
				'ids' => array('singular' => 'singular 2'),
				'flags' => array(),
				'translated' => array('translated 2'),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);
	}

	public function testReadPoPlural() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<EOD
msgid "singular 1"
msgid_plural "plural 1"
msgstr[0] "translated 1-0"
msgstr[1] "translated 1-1"
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1', 'plural' => 'plural 1'),
				'flags' => array(),
				'translated' => array('translated 1-0', 'translated 1-1'),
				'occurrences' => array(),
				'comments' => array(),
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);
	}

	public function testReadPoWithGnuHeader() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<'EOD'
# SOME DESCRIPTIVE TITLE.
# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER
# This file is distributed under the same license as the PACKAGE package.
# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: PACKAGE VERSION\n"
"Report-Msgid-Bugs-To: \n"
"POT-Creation-Date: 2010-02-21 13:23+0100\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"
"Language-Team: LANGUAGE <LL@li.org>\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=CHARSET\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\n"

msgid "singular 1"
msgstr "translated 1"
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1'),
				'flags' => array(),
				'translated' => array('translated 1'),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);
	}

	public function testReadPoIgnoresDummyAndEmptyItems() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<EOD
#, fuzzy
msgid ""
msgstr ""

msgid ""
msgstr ""

msgid " "
msgstr ""
EOD;
		file_put_contents($file, $data);

		$result = $this->adapter->read('message', 'de', null);
		$this->assertFalse($result);
	}

	public function testReadPoWithFlagsAndComments() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<EOD
#: test.php:1
#, fuzzy
#, c-format
#. extracted comment
#  translator comment
msgid "singular 1"
msgstr "translated 1"
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1'),
				'flags' => array('fuzzy' => true, 'c-format' => true),
				'translated' => array('translated 1'),
				'occurrences' => array(
					array('file' => 'test.php', 'line' => 1)
				),
				'comments' => array(
					'extracted comment',
					'translator comment'
				),
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);
	}

	public function testReadPoMultiline() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<EOD
msgid "An id"
msgstr ""
"This is a translation spanning "
"multiple lines."
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'An id' => array(
				'id' => 'An id',
				'ids' => array(
					'singular' => 'An id'
				),
				'flags' => array(),
				'translated' => array(
					'This is a translation spanning multiple lines.'
				),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);

		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$data = <<<EOD
msgid ""
"This is an id spanning "
"multiple lines."
msgstr ""
"This is a translation spanning "
"multiple lines."
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'This is an id spanning multiple lines.' => array(
				'id' => 'This is an id spanning multiple lines.',
				'ids' => array(
					'singular' => 'This is an id spanning multiple lines.'
				),
				'flags' => array(),
				'translated' => array(
					'This is a translation spanning multiple lines.'
				),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);

		$data = <<<EOD
msgid ""
"This is an id spanning "
"multiple lines."
msgid_plural ""
"This is a plural id spanning "
"multiple lines."
msgstr[0] ""
"This is a translation spanning "
"multiple lines."
msgstr[1] ""
"This is a plural translation spanning "
"multiple lines."
EOD;
		file_put_contents($file, $data);

		$expected = array(
			'This is an id spanning multiple lines.' => array(
				'id' => 'This is an id spanning multiple lines.',
				'ids' => array(
					'singular' => 'This is an id spanning multiple lines.',
					'plural' => 'This is a plural id spanning multiple lines.'
				),
				'flags' => array(),
				'translated' => array(
					'This is a translation spanning multiple lines.',
					'This is a plural translation spanning multiple lines.'
				),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);
	}

	public function testReadPoLongIdsAndTranslations() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.po";
		$dummy = str_repeat('X', 10000);
		$data = <<<EOD
msgid "{$dummy}"
msgstr "translated 1"
EOD;
		file_put_contents($file, $data);

		$result = $this->adapter->read('message', 'de', null);
		$this->assertTrue(isset($result[$dummy]));

		$data = <<<EOD
msgid "singular 1"
msgstr "{$dummy}"
EOD;
		file_put_contents($file, $data);

		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($result['singular 1']['translated'][0], $dummy);
	}

	public function testReadMoLittleEndian() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.mo";
		$data = <<<EOD
3hIElQAAAAADAAAAHAAAADQAAAAFAAAATAAAAAAAAABgAAAAEwAAAGEAAAAKAAAAdQAAADUBAACAAAAAHQAAALYBAAAMAAAA1AEA
AAEAAAACAAAAAAAAAAMAAAAAAAAAAHNpbmd1bGFyIDEAcGx1cmFsIDEAc2luZ3VsYXIgMgBQcm9qZWN0LUlkLVZlcnNpb246IApQ
T1QtQ3JlYXRpb24tRGF0ZTogClBPLVJldmlzaW9uLURhdGU6IDIwMTAtMDItMjAgMTc6MTQrMDEwMApMYXN0LVRyYW5zbGF0b3I6
IERhdmlkIFBlcnNzb24gPGRhdmlkcGVyc3NvbkBnbXguZGU+Ckxhbmd1YWdlLVRlYW06IApNSU1FLVZlcnNpb246IDEuMApDb250
ZW50LVR5cGU6IHRleHQvcGxhaW47IGNoYXJzZXQ9VVRGLTgKQ29udGVudC1UcmFuc2Zlci1FbmNvZGluZzogOGJpdApYLVBvZWRp
dC1MYW5ndWFnZTogR2VybWFuClBsdXJhbC1Gb3JtczogbnBsdXJhbHM9MjsgcGx1cmFsPShuICE9IDEpOwoAdHJhbnNsYXRlZCAx
LTAAdHJhbnNsYXRlZCAxLTEAdHJhbnNsYXRlZCAyAA==
EOD;

		file_put_contents($file, base64_decode($data));

		$expected = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1', 'plural' => 'plural 1'),
				'flags' => array(),
				'translated' => array('translated 1-0', 'translated 1-1'),
				'occurrences' => array(),
				'comments' => array()
			),
			'singular 2' => array(
				'id' => 'singular 2',
				'ids' => array('singular' => 'singular 2', 'plural' => null),
				'flags' => array(),
				'translated' => array('translated 2'),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($expected, $result);
	}

	public function testReadMoMalformed() {
		$file = "{$this->_path}/de/LC_MESSAGES/default.mo";

		touch($file);

		try {
			$this->adapter->read('message', 'de', null);
			$result = false;
		} catch (Exception $e) {
			$result = true;
		}
		$this->assert($result);

		file_put_contents($file, '|---10---||---10---|');

		try {
			$this->adapter->read('message', 'de', null);
			$result = false;
		} catch (Exception $e) {
			$result = true;
		}
		$this->assert($result);

		file_put_contents($file, '|---10---||---10---||---10---|');

		try {
			$this->adapter->read('message', 'de', null);
			$result = false;
		} catch (Exception $e) {
			$result = true;
		}
		$this->assert($result);
	}

	public function testWriteMessageCompilesPo() {
		$data = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1', 'plural' => null),
				'flags' => array(),
				'translated' => array('translated 1'),
				'occurrences' => array(),
				'comments' => array(),
			)
		);
		$this->adapter->write('message', 'de', null, $data);
		$this->assertTrue(file_exists("{$this->_path}/de/LC_MESSAGES/default.po"));
	}

	public function testWriteMessageTemplateCompilesPot() {
		$data = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1', 'plural' => null),
				'flags' => array(),
				'translated' => array(),
				'occurrences' => array(),
				'comments' => array(),
			)
		);
		$this->adapter->write('messageTemplate', 'root', null, $data);
		$this->assertTrue(file_exists("{$this->_path}/message_default.pot"));
	}

	public function testWriteReadPo() {
		$this->adapter->mo = false;

		$data = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1', 'plural' => 'plural 1'),
				'flags' => array('fuzzy' => true),
				'translated' => array('translated singular 1', 'translated plural 1'),
				'occurrences' => array(
					array('file' => 'test.php', 'line' => 1)
				),
				'comments' => array(
					'comment 1'
				),
			)
		);

		$this->adapter->write('message', 'de', null, $data);
		$this->assertTrue(file_exists("{$this->_path}/de/LC_MESSAGES/default.po"));
		$result = $this->adapter->read('message', 'de', null);
		$this->assertEqual($data, $result);

		$this->adapter->write('messageTemplate', 'root', null, $data);
		$this->assertTrue(file_exists("{$this->_path}/message_default.pot"));

		$result = $this->adapter->read('messageTemplate', 'root', null);
		$this->assertEqual($data, $result);
	}

	public function testWrittenPoHasGnuHeader() {
		$this->adapter->mo = false;

		$data = array(
			'singular 1' => array(
				'id' => 'singular 1',
				'ids' => array('singular' => 'singular 1', 'plural' => 'plural 1'),
				'flags' => array(),
				'translated' => array('translated 1-0', 'translated 1-1'),
				'occurrences' => array(),
				'comments' => array()
			),
			'singular 2' => array(
				'id' => 'singular 2',
				'ids' => array('singular' => 'singular 2', 'plural' => null),
				'flags' => array(),
				'translated' => array('translated 2'),
				'occurrences' => array(),
				'comments' => array()
			)
		);
		$this->adapter->write('message', 'de', null, $data);
		$result = file_get_contents("{$this->_path}/de/LC_MESSAGES/default.po");

		$expected = '#, fuzzy\nmsgid ""\nmsgstr ""\n"Project-Id';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"Project-Id-Version: PACKAGE VERSION\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"POT-Creation-Date: YEAR-MO-DA HO:MI\+ZONE\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"PO-Revision-Date: YEAR-MO-DA HO:MI\+ZONE\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"Language-Team: LANGUAGE <EMAIL@ADDRESS>\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"MIME-Version: 1.0\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"Content-Type: text/plain; charset=CHARSET\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"Content-Transfer-Encoding: 8bit\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);

		$expected = '"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\\n"\n';
		$this->assertPattern("%{$expected}%", $result);
	}
}

?>