<?php
class ZAP_Tests_HelpersTest extends PHPUnit_Framework_TestCase
{
	public function testArrayToXml()
	{
		$books = array(
			'book' => array(
				array(
					'title' => 'Book 01',
					'public' => 1999,
					'publisher' => 'Publisher 01',
					'author' => array(
						'name' => 'Author 01'
					),
				),
				array(
					'title' => 'Book 02',
					'public' => 1998,
					'publisher' => 'Publisher 02',
					'author' => array(
						'name' => 'Author 02',
					),
				),
				array(
					'title' => 'Book 03',
					'public' => 1999,
					'publisher' => 'Publisher 03',
					'author' => array(
						'name' => 'Author 03',
					),
				),
			),
		);
		$xml = ZAP_Helpers::arrayToXml('books', $books);
		$this->assertObjectHasAttribute('book', $xml);
		$this->assertObjectHasAttribute('author', $xml->book);
		$this->assertEquals(3, count($xml->book));
	}

	public function testXmlToObject()
	{
		$xml = 
			'<books>'
				.'<book title="Book 01" public="1999" publisher="Publisher 01">'
					.'<author><name>Author 01</name></author>'
					.'<author name="Author 02" />'
				.'</book>'
				.'<book title="Book 02" public="1998" publisher="Publisher 02">'
					.'<author name="Author 04" />'
				.'</book>'
				.'<book title="Book 02" public="1995" publisher="Publisher 02">'
					.'<author name="Author 01" />'
					.'<author name="Author 03" />'
				.'</book>'
			.'</books>';
		$object = ZAP_Helpers::xmlToObject(new SimpleXMLElement($xml));
		$firstBook = current($object->book);

		$this->assertObjectHasAttribute('book', $object);
		$this->assertObjectHasAttribute('title', $firstBook);
		$this->assertEquals(3, count($object->book));
		$this->assertObjectHasAttribute('name', current($firstBook->author));
		$this->assertEquals(2, count($firstBook->author));
	}
}