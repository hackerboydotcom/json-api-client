<?php

namespace Art4\JsonApiClient\Tests;

use Art4\JsonApiClient\RelationshipLink;
use Art4\JsonApiClient\Tests\Fixtures\HelperTrait;

class RelationshipLinkTest extends \PHPUnit_Framework_TestCase
{
	use HelperTrait;

	/**
	 * @setup
	 */
	public function setUp()
	{
		$this->manager = $this->buildManagerMock();
	}

	/**
	 * @test only self, related and pagination property can exist
	 *
	 * links: a links object containing at least one of the following:
	 * - self: a link for the relationship itself (a "relationship link"). This link allows
	 *   the client to directly manipulate the relationship. For example, it would allow a
	 *   client to remove an author from an article without deleting the people resource itself.
	 * - related: a related resource link
	 *
	 * A relationship object that represents a to-many relationship MAY also contain pagination
	 * links under the links member, as described below.
	 */
	public function testOnlySelfRelatedPaginationPropertiesExists()
	{
		$object = new \stdClass();
		$object->self = 'http://example.org/self';
		$object->related = 'http://example.org/related';
		$object->first = 'http://example.org/first';
		$object->last = 'http://example.org/last';
		$object->prev = 'http://example.org/prev';
		$object->next = 'http://example.org/next';
		$object->custom = 'http://example.org/custom';

		$link = new RelationshipLink($object, $this->manager);

		$this->assertInstanceOf('Art4\JsonApiClient\RelationshipLink', $link);
		$this->assertInstanceOf('Art4\JsonApiClient\AccessInterface', $link);
		$this->assertSame($link->getKeys(), array('self', 'related', 'first', 'last', 'prev', 'next', 'custom'));

		$this->assertTrue($link->has('custom'));
		$this->assertSame($link->get('custom'), 'http://example.org/custom');
		$this->assertTrue($link->has('self'));
		$this->assertSame($link->get('self'), 'http://example.org/self');
		$this->assertTrue($link->has('related'));
		$this->assertSame($link->get('related'), 'http://example.org/related');
		$this->assertTrue($link->has('first'));
		$this->assertSame($link->get('first'), 'http://example.org/first');
		$this->assertTrue($link->has('last'));
		$this->assertSame($link->get('last'), 'http://example.org/last');
		$this->assertTrue($link->has('prev'));
		$this->assertSame($link->get('prev'), 'http://example.org/prev');
		$this->assertTrue($link->has('next'));
		$this->assertSame($link->get('next'), 'http://example.org/next');

		$this->assertSame($link->asArray(), array(
			'self' => $link->get('self'),
			'related' => $link->get('related'),
			'first' => $link->get('first'),
			'last' => $link->get('last'),
			'last' => $link->get('last'),
			'prev' => $link->get('prev'),
			'next' => $link->get('next'),
			'custom' => $link->get('custom'),
		));

		// Test full array
		$this->assertSame($link->asArray(true), array(
			'self' => $link->get('self'),
			'related' => $link->get('related'),
			'first' => $link->get('first'),
			'last' => $link->get('last'),
			'prev' => $link->get('prev'),
			'next' => $link->get('next'),
			'custom' => $link->get('custom'),
		));
	}

	/**
	 * @test pagination links are parsed, if data in parent relationship object exists
	 */
	public function testPaginationParsedIfRelationshipDataExists()
	{
		$object = new \stdClass();
		$object->self = 'http://example.org/self';
		$object->first = new \stdClass();
		$object->last = new \stdClass();
		$object->prev = new \stdClass();
		$object->next = new \stdClass();

		// Mock Relationship
		$relationship = $this->getMockBuilder('Art4\JsonApiClient\RelationshipInterface')
			->getMock()
			->expects($this->any())
			->method('has')
			->will($this->returnValue(true));

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "first" has to be a string or null, "object" given.'
		);

		$link = new RelationshipLink($object, $this->manager, $relationship);
	}

	/**
	 * @test pagination links are not parsed, if data in parent relationship object doesnt exist
	 */
	public function testPaginationNotParsedIfRelationshipDataNotExists()
	{
		$object = new \stdClass();
		$object->self = 'http://example.org/self';
		$object->first = new \stdClass();
		$object->last = new \stdClass();
		$object->prev = new \stdClass();
		$object->next = new \stdClass();

		// Mock Relationship
		$relationship = $this->getMockBuilder('Art4\JsonApiClient\RelationshipInterface')
			->getMock()
			->expects($this->any())
			->method('has')
			->will($this->returnValue(false));

		$link = new RelationshipLink($object, $this->manager, $relationship);

		$this->assertInstanceOf('Art4\JsonApiClient\RelationshipLink', $link);
		$this->assertSame($link->getKeys(), array('self', 'first', 'last', 'prev', 'next'));

		$this->assertTrue($link->has('self'));
		$this->assertInstanceOf('Art4\JsonApiClient\Link', $link->get('self'));
		$this->assertTrue($link->has('first'));
		$this->assertInstanceOf('Art4\JsonApiClient\Link', $link->get('first'));
		$this->assertTrue($link->has('last'));
		$this->assertInstanceOf('Art4\JsonApiClient\Link', $link->get('last'));
		$this->assertTrue($link->has('prev'));
		$this->assertInstanceOf('Art4\JsonApiClient\Link', $link->get('prev'));
		$this->assertTrue($link->has('next'));
		$this->assertInstanceOf('Art4\JsonApiClient\Link', $link->get('next'));
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * links: a links object containing at least one of the following:
	 */
	public function testCreateWithoutObjectThrowsException($input)
	{
		// Input must be an object
		if ( gettype($input) === 'object' )
		{
			return;
		}

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'RelationshipLink has to be an object, "' . gettype($input) . '" given.'
		);

		$link = new RelationshipLink($input, $this->manager);
	}

	/**
	 * @test object contains at least one of the following: self, related
	 */
	public function testCreateWithoutSelfAndRelatedPropertiesThrowsException()
	{
		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'RelationshipLink has to be at least a "self" or "related" link'
		);

		$object = new \stdClass();
		$object->first = 'http://example.org/first';
		$object->next = 'http://example.org/next';

		$link = new RelationshipLink($object, $this->manager);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * self: a link for the relationship itself (a "relationship link").
	 */
	public function testSelfMustBeAString($input)
	{
		// Input must be a string
		if ( gettype($input) === 'string' )
		{
			return;
		}

		$object = new \stdClass();
		$object->self = $input;

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "self" has to be a string, "' . gettype($input) . '" given.'
		);

		$link = new RelationshipLink($object, $this->manager);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * related: a related resource link when the primary data represents a resource relationship.
	 * If present, a related resource link MUST reference a valid URL
	 */
	public function testRelatedMustBeAStringOrObject($input)
	{
		$object = new \stdClass();
		$object->related = $input;

		// Input must be a string or object
		if ( gettype($input) === 'string' or gettype($input) === 'object' )
		{
			$link = new RelationshipLink($object, $this->manager);

			$this->assertTrue($link->has('related'));

			return;
		}

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "related" has to be a string or object, "' . gettype($input) . '" given.'
		);

		$link = new RelationshipLink($object, $this->manager);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * Keys MUST either be omitted or have a null value to indicate that a particular link is unavailable.
	 */
	public function testFirstCanBeAStringOrNull($input)
	{
		$object = new \stdClass();
		$object->self = 'https://example.org/self';
		$object->first = $input;

		// Input must be null or string
		if ( gettype($input) === 'string' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self', 'first'));

			$this->assertTrue($link->has('first'));
			$this->assertSame($link->get('first'), $input);

			return;
		}
		elseif ( gettype($input) === 'NULL' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self'));

			$this->assertFalse($link->has('first'));

			return;
		}

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "first" has to be a string or null, "' . gettype($input) . '" given.'
		);

		$link = new RelationshipLink($object, $this->manager);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * Keys MUST either be omitted or have a null value to indicate that a particular link is unavailable.
	 */
	public function testLastCanBeAStringOrNull($input)
	{
		$object = new \stdClass();
		$object->self = 'https://example.org/self';
		$object->last = $input;

		// Input must be null or string
		if ( gettype($input) === 'string' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self', 'last'));

			$this->assertTrue($link->has('last'));
			$this->assertSame($link->get('last'), $input);

			return;
		}
		elseif ( gettype($input) === 'NULL' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self'));

			$this->assertFalse($link->has('last'));

			return;
		}

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "last" has to be a string or null, "' . gettype($input) . '" given.'
		);

		$link = new RelationshipLink($object, $this->manager);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * Keys MUST either be omitted or have a null value to indicate that a particular link is unavailable.
	 */
	public function testPrevCanBeAStringOrNull($input)
	{
		$object = new \stdClass();
		$object->self = 'https://example.org/self';
		$object->prev = $input;

		// Input must be null or string
		if ( gettype($input) === 'string' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self', 'prev'));

			$this->assertTrue($link->has('prev'));
			$this->assertSame($link->get('prev'), $input);

			return;
		}
		elseif ( gettype($input) === 'NULL' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self'));

			$this->assertFalse($link->has('prev'));

			return;
		}

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "prev" has to be a string or null, "' . gettype($input) . '" given.'
		);

		$link = new RelationshipLink($object, $this->manager);
	}

	/**
	 * @dataProvider jsonValuesProvider
	 *
	 * Keys MUST either be omitted or have a null value to indicate that a particular link is unavailable.
	 */
	public function testNextCanBeAStringOrNull($input)
	{
		$object = new \stdClass();
		$object->self = 'https://example.org/self';
		$object->next = $input;

		// Input must be null or string
		if ( gettype($input) === 'string' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self', 'next'));

			$this->assertTrue($link->has('next'));
			$this->assertSame($link->get('next'), $input);

			return;
		}
		elseif ( gettype($input) === 'NULL' )
		{
			$link = new RelationshipLink($object, $this->manager);
			$this->assertSame($link->getKeys(), array('self'));

			$this->assertFalse($link->has('next'));

			return;
		}

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\ValidationException',
			'property "next" has to be a string or null, "' . gettype($input) . '" given.'
		);

		$link = new RelationshipLink($object, $this->manager);
	}

	/**
	 * @test
	 */
	public function testGetOnANonExistingKeyThrowsException()
	{
		$object = new \stdClass();
		$object->self = 'http://example.org/self';
		$object->related = 'http://example.org/related';

		$link = new RelationshipLink($object, $this->manager);

		$this->assertFalse($link->has('something'));

		$this->setExpectedException(
			'Art4\JsonApiClient\Exception\AccessException',
			'"something" doesn\'t exist in this object.'
		);

		$link->get('something');
	}
}
