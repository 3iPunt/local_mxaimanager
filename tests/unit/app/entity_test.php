<?php

namespace local_mxaimanager\unit\app;


// Test entity class since entity is abstract
class test_entity extends \local_mxaimanager\app\entity
{
    public function to_array(): array
    {
        return $this->record;
    }
}

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

class entity_test extends \base_testcase
{
    public function test_constructor_with_record(): void
    {
        $record = ['id' => 1, 'name' => 'Alice'];
        $entity = new test_entity($record);

        $this->assertEquals(1, $entity->get_id());
    }

    public function test_constructor_empty(): void
    {
        $entity = new test_entity();

        $this->assertEquals(0, $entity->get_id());
        $this->assertEmpty($entity->record);
    }

    public function test_get_id_set_id(): void
    {
        $entity = new test_entity();

        $entity->set_id(42);
        $this->assertEquals(42, $entity->get_id());
    }

    public function test_magic_get(): void
    {
        $record = ['id' => 1, 'name' => 'Alice'];
        $entity = new test_entity($record);

        $this->assertEquals('Alice', $entity->name);
        $this->assertNull($entity->nonexistent);
    }

    public function test_magic_set(): void
    {
        $entity = new test_entity();

        $entity->name = 'Bob';
        $this->assertEquals('Bob', $entity->name);
        $this->assertEquals(['name' => 'Bob'], $entity->to_array());
    }

    public function test_magic_isset(): void
    {
        $record = ['name' => 'Alice'];
        $entity = new test_entity($record);

        $this->assertTrue(isset($entity->name));
        $this->assertFalse(isset($entity->nonexistent));
    }

    public function test_magic_unset(): void
    {
        $record = ['name' => 'Alice', 'age' => 25];
        $entity = new test_entity($record);

        unset($entity->name);
        unset($entity->age);

        $this->assertNull($entity->name);
        $this->assertNull($entity->age);

        // Check internal record - unset sets to null but keeps key
        $this->assertEquals(['name' => null, 'age' => null], $entity->to_array());
    }

    public function test_array_access_offset_set(): void
    {
        $entity = new test_entity();

        // Set a value
        $entity['key'] = 'value';
        $this->assertEquals('value', $entity['key']);

        // Set using null offset (should append)
        $entity[] = 'appended';
        // Note: This behavior may not be well-defined for entities vs collections
    }

    public function test_array_access_offset_get(): void
    {
        $record = ['name' => 'Alice'];
        $entity = new test_entity($record);

        $this->assertEquals('Alice', $entity['name']);
        $this->assertNull($entity['nonexistent']);
    }

    public function test_array_access_offset_exists(): void
    {
        $record = ['name' => 'Alice'];
        $entity = new test_entity($record);

        $this->assertTrue(isset($entity['name']));
        $this->assertFalse(isset($entity['nonexistent']));
    }

    public function test_array_access_offset_unset(): void
    {
        $record = ['name' => 'Alice', 'age' => 25];
        $entity = new test_entity($record);

        unset($entity['name']);
        $this->assertEquals(['age' => 25], $entity->to_array());
    }

    public function test_json_serialize(): void
    {
        $record = ['id' => 1, 'name' => 'Alice'];
        $entity = new test_entity($record);

        $serialized = json_encode($entity);
        $decoded = json_decode($serialized, true);

        $this->assertEquals(['id' => 1, 'name' => 'Alice'], $decoded);
    }

    public function test_to_array_abstract_contract(): void
    {
        // Since to_array is abstract, our test_entity implements it
        $record = ['id' => 1, 'name' => 'Alice'];
        $entity = new test_entity($record);

        $array = $entity->to_array();
        $this->assertEquals($record, $array);
    }

    public function test_property_access_through_record_array(): void
    {
        $entity = new test_entity();

        // Test that array access and magic properties work on the same record
        $entity['test'] = 'value1';
        $entity->another = 'value2';

        $this->assertEquals('value1', $entity['test']);
        $this->assertEquals('value2', $entity->another);
        $this->assertEquals('value1', $entity->test);  // Magic access

        // Check internal consistency
        $this->assertEquals(['test' => 'value1', 'another' => 'value2'], $entity->to_array());
    }

    public function test_chained_set_id(): void
    {
        $entity = new test_entity();

        $result = $entity->set_id(100);

        $this->assertSame($entity, $result); // Returns self for chaining
        $this->assertEquals(100, $entity->get_id());
    }

    public function test_complex_record_manipulation(): void
    {
        $initial = [
            'id' => 1,
            'name' => 'Alice',
            'data' => ['nested' => 'value']
        ];
        $entity = new test_entity($initial);

        // Modify using different access methods
        $entity['name'] = 'Bob';
        $entity->age = 30;

        // Note: Entity doesn't support nested array modification through property access
        // $entity->data['new'] = 'added'; would modify a copy, not the original
        // For nested changes, use direct array access:
        $data = $entity->data;
        $data['new'] = 'added';
        $entity->data = $data;

        // Verify all changes are reflected
        $expected = [
            'id' => 1,
            'name' => 'Bob',
            'age' => 30,
            'data' => ['nested' => 'value', 'new' => 'added']
        ];
        $this->assertEquals($expected, $entity->to_array());

        // Test JSON serialization includes all changes
        $json = json_encode($entity);
        $this->assertEquals($expected, json_decode($json, true));
    }
}
