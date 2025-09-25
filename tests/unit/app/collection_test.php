<?php

namespace local_mxaimanager\unit\app;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\collection;
use local_mxaimanager\app\exceptions\empty_collection_exception;

class collection_test extends \base_testcase
{
    public function test_constructor_with_items(): void
    {
        $items = [1, 2, 3];
        $collection = new collection($items);

        $this->assertEquals(3, $collection->count());
        $this->assertEquals([1, 2, 3], $collection->to_array());
    }

    public function test_constructor_empty(): void
    {
        $collection = new collection();

        $this->assertEquals(0, $collection->count());
        $this->assertTrue($collection->empty());
        $this->assertFalse($collection->not_empty());
    }

    public function test_set_method(): void
    {
        $collection = new collection();
        $collection->set([1, 2, 3]);

        $this->assertEquals(3, $collection->count());
    }

    public function test_array_access_offset_set(): void
    {
        $collection = new collection([1]);

        // Append to end with null offset
        $collection[] = 2;
        $this->assertEquals([1, 2], $collection->to_array());

        // Set specific index
        $collection[0] = 10;
        $this->assertEquals([10, 2], $collection->to_array());
    }

    public function test_array_access_offset_get(): void
    {
        $collection = new collection([1, 2, 3]);

        $this->assertEquals(1, $collection[0]);
        $this->assertEquals(2, $collection[1]);
        $this->assertNull($collection[99]);
    }

    public function test_array_access_offset_exists(): void
    {
        $collection = new collection([1, 2, 3]);

        $this->assertTrue(isset($collection[0]));
        $this->assertFalse(isset($collection[3]));
    }

    public function test_array_access_offset_unset(): void
    {
        $collection = new collection([1, 2, 3]);
        unset($collection[1]);

        $this->assertEquals([1, 3], array_values($collection->to_array()));
        $this->assertEquals(2, $collection->count());
    }

    public function test_iterator_foreach(): void
    {
        $collection = new collection([1, 2, 3]);
        $result = [];

        foreach ($collection as $item) {
            $result[] = $item;
        }

        $this->assertEquals([1, 2, 3], $result);
    }

    public function test_iterator_methods(): void
    {
        $collection = new collection([1, 2, 3]);

        // Test current
        $this->assertEquals(1, $collection->current());

        // Test key
        $this->assertEquals(0, $collection->key());

        // Move to next
        $collection->next();
        $this->assertEquals(2, $collection->current());
        $this->assertEquals(1, $collection->key());

        // Test valid
        $this->assertTrue($collection->valid());

        // Move to end
        $collection->next();
        $collection->next();
        $this->assertFalse($collection->valid());

        // Test rewind
        $collection->rewind();
        $this->assertEquals(0, $collection->key());
        $this->assertEquals(1, $collection->current());
    }

    public function test_countable(): void
    {
        $collection = new collection([1, 2, 3]);
        $this->assertEquals(3, count($collection));
    }

    public function test_filter(): void
    {
        $collection = new collection([1, 2, 3, 4, 5]);
        $filtered = $collection->filter(function ($item) {
            return $item > 3;
        });

        $this->assertEquals([3 => 4, 4 => 5], $filtered->to_array());
        $this->assertInstanceOf(collection::class, $filtered);
    }

    public function test_map(): void
    {
        $collection = new collection([1, 2, 3]);
        $mapped = $collection->map(function ($item) {
            return $item * 2;
        });

        $this->assertEquals([2, 4, 6], $mapped->to_array());
        $this->assertInstanceOf(collection::class, $mapped);
    }

    public function test_sort_asc(): void
    {
        $collection = new collection([3, 1, 2]);
        $sorted = $collection->sort_asc(function ($item) {
            return (string)$item;
        });

        $this->assertEquals([1, 2, 3], $sorted->to_array());
        $this->assertSame($sorted, $collection); // Returns self
    }

    public function test_sort_desc(): void
    {
        $collection = new collection([1, 3, 2]);
        $sorted = $collection->sort_desc(function ($item) {
            return (string)$item;
        });

        $this->assertEquals([3, 2, 1], $sorted->to_array());
    }

    public function test_pluck(): void
    {
        $data = [
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 30],
        ];
        $collection = new collection($data);
        $names = $collection->pluck(function ($item) {
            return $item['name'];
        });

        $this->assertEquals(['Alice', 'Bob'], $names->to_array());
        $this->assertInstanceOf(collection::class, $names);
    }

    public function test_to_array(): void
    {
        $collection = new collection([1, 2, 3]);
        $array = $collection->to_array();

        $this->assertEquals([1, 2, 3], $array);
    }

    public function test_to_array_indexed(): void
    {
        $collection = new collection([1, 2, 3]);
        $array = $collection->to_array(true);

        $this->assertEquals([1, 2, 3], $array);
    }

    public function test_implode(): void
    {
        $collection = new collection(['a', 'b', 'c']);
        $result = $collection->implode(',');

        $this->assertEquals('a,b,c', $result);
    }

    public function test_explode(): void
    {
        $collection = new collection();
        $result = $collection->explode('a,b,c', ',');

        $this->assertEquals(['a', 'b', 'c'], $result->to_array());
        $this->assertSame($result, $collection); // Returns self
    }

    public function test_first(): void
    {
        $collection = new collection([1, 2, 3]);
        $this->assertEquals(1, $collection->first());
    }

    public function test_first_empty_throws_exception(): void
    {
        $this->expectException(empty_collection_exception::class);
        $this->expectExceptionMessage('No first item in collection');

        $collection = new collection();
        $collection->first();
    }

    public function test_last(): void
    {
        $collection = new collection([1, 2, 3]);
        $this->assertEquals(3, $collection->last());
    }

    public function test_last_empty_throws_exception(): void
    {
        $this->expectException(empty_collection_exception::class);
        $this->expectExceptionMessage('No last item in collection');

        $collection = new collection();
        $collection->last();
    }

    public function test_by_key(): void
    {
        $collection = new collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(1, $collection->by_key('a'));
    }

    public function test_by_key_empty_throws_exception(): void
    {
        $this->expectException(empty_collection_exception::class);
        $this->expectExceptionMessage('Key not found in collection');

        $collection = new collection();
        $collection->by_key('nonexistent');
    }

    public function test_slice(): void
    {
        $collection = new collection([1, 2, 3, 4, 5]);
        $sliced = $collection->slice(1, 3);

        $this->assertEquals([2, 3, 4], $sliced->to_array());
        $this->assertSame($sliced, $collection); // Returns self
    }

    public function test_empty_and_not_empty(): void
    {
        $empty = new collection();
        $notEmpty = new collection([1]);

        $this->assertTrue($empty->empty());
        $this->assertFalse($empty->not_empty());
        $this->assertFalse($notEmpty->empty());
        $this->assertTrue($notEmpty->not_empty());
    }

    public function test_append(): void
    {
        $collection = new collection([1]);
        $result = $collection->append(2);

        $this->assertEquals([1, 2], $collection->to_array());
        $this->assertSame($result, $collection); // Returns self
    }

    public function test_prepend(): void
    {
        $collection = new collection([2]);
        $result = $collection->prepend(1);

        // Note: prepend returns a new collection, unlike append which modifies in place
        $this->assertEquals([1, 2], $result->to_array());
        $this->assertInstanceOf(collection::class, $result);
    }

    public function test_merge(): void
    {
        $collection1 = new collection([1, 2]);
        $collection2 = new collection([3, 4]);
        $result = $collection1->merge($collection2);

        $this->assertEquals([1, 2, 3, 4], $collection1->to_array());
        $this->assertSame($result, $collection1); // Returns self
    }

    public function test_contains(): void
    {
        $collection = new collection([1, 2, 3]);
        $found = $collection->contains(function ($item) {
            return $item === 2;
        }, true);

        $this->assertTrue($found);
    }

    public function test_contains_not_found(): void
    {
        $collection = new collection([1, 2, 3]);
        $found = $collection->contains(function ($item) {
            return $item === 5;
        }, true);

        $this->assertFalse($found);
    }

    public function test_column(): void
    {
        $data = [
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 30],
        ];
        $collection = new collection($data);
        $names = $collection->column('name');

        $this->assertEquals(['Alice', 'Bob'], $names);
    }

    public function test_find(): void
    {
        $objects = [
            (object)['id' => 1, 'name' => 'Alice'],
            (object)['id' => 2, 'name' => 'Bob'],
            (object)['id' => 3, 'name' => 'Alice'],
        ];
        $collection = new collection($objects);
        $found = $collection->find('Alice', 'name');

        $this->assertCount(2, $found);
        $this->assertEquals('Alice', $found[0]->name);
        $this->assertEquals('Alice', $found[1]->name);
    }

    public function test_shuffle(): void
    {
        $collection = new collection([1, 2, 3, 4, 5]);
        $original = $collection->to_array();

        $result = $collection->shuffle();
        $shuffled = $result->to_array();

        // Can't guarantee shuffle result, but ensure same items
        sort($original);
        sort($shuffled);
        $this->assertEquals($original, $shuffled);
        $this->assertSame($result, $collection); // Returns self
    }

    public function test_to_list(): void
    {
        $objects = [
            (object)['id' => 1, 'name' => 'Alice'],
            (object)['id' => 2, 'name' => 'Bob'],
        ];
        $collection = new collection($objects);
        $list = $collection->to_list(
            function ($item) {
                return $item->id;
            },
            function ($item) {
                return $item->name;
            }
        );

        $expected = [1 => 'Alice', 2 => 'Bob'];
        $this->assertEquals($expected, $list->to_array());
    }
}
