<?php

namespace local_mxaimanager\integration\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class repository_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();

        $base_factory = base_factory::make();

        // Remove all features since db/install.php is called during setUp, which may seed features.
        foreach ($base_factory->ai()->feature()->repository()->get_all() as $feature) {
            $base_factory->ai()->feature()->repository()->delete($feature->get_id());
        }
    }

    public function test_get_table(): void
    {
        $repository = base_factory::make()->ai()->feature()->repository();
        $this->assertEquals('local_mxaimanager_features', $repository->get_table());
    }

    public function test_get_by_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->repository();

        $feature = $factory->ai()->feature()->entity()
            ->set_component('mod_test')
            ->set_name_identifier('test_feature')
            ->set_description_identifier('Test Feature Description');
        $feature->set_id($repository->insert($feature));

        $retrieved_feature = $repository->get_by_id($feature->get_id());

        $this->assertEquals($feature->get_id(), $retrieved_feature->get_id());
        $this->assertEquals('mod_test', $retrieved_feature->get_component());
        $this->assertEquals('test_feature', $retrieved_feature->get_name_identifier());
        $this->assertEquals('Test Feature Description', $retrieved_feature->get_description_identifier());
    }

    public function test_get_all_by_name_identifier(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->repository();

        $feature1 = $factory->ai()->feature()->entity()
            ->set_component('mod_test1')
            ->set_name_identifier('shared_name')
            ->set_description_identifier('Desc1');
        $repository->insert($feature1);

        $feature2 = $factory->ai()->feature()->entity()
            ->set_component('mod_test2')
            ->set_name_identifier('shared_name')
            ->set_description_identifier('Desc2');
        $repository->insert($feature2);

        $features = $repository->get_all_by_name_identifier('shared_name');

        $this->assertCount(2, $features);

        $components = [];
        foreach ($features as $feature) {
            $components[] = $feature->get_component();
        }
        sort($components);

        $this->assertEquals(['mod_test1', 'mod_test2'], $components);
    }

    public function test_get_all_by_component(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->repository();

        $feature1 = $factory->ai()->feature()->entity()
            ->set_component('mod_test')
            ->set_name_identifier('feature1')
            ->set_description_identifier('Feature1 Desc');
        $repository->insert($feature1);

        $feature2 = $factory->ai()->feature()->entity()
            ->set_component('mod_test')
            ->set_name_identifier('feature2')
            ->set_description_identifier('Feature2 Desc');
        $repository->insert($feature2);

        $features = $repository->get_all_by_component('mod_test');

        $this->assertCount(2, $features);

        $names = [];
        foreach ($features as $feature) {
            $names[] = $feature->get_name_identifier();
        }
        sort($names);

        $this->assertEquals(['feature1', 'feature2'], $names);
    }

    public function test_get_by_component_and_name_identifier(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->repository();

        $feature = $factory->ai()->feature()->entity()
            ->set_component('mod_test')
            ->set_name_identifier('unique_feature')
            ->set_description_identifier('Unique Feature Desc');
        $repository->insert($feature);

        $retrieved_feature = $repository->get_by_component_and_name_identifier('mod_test', 'unique_feature');

        $this->assertEquals('mod_test', $retrieved_feature->get_component());
        $this->assertEquals('unique_feature', $retrieved_feature->get_name_identifier());
        $this->assertEquals('Unique Feature Desc', $retrieved_feature->get_description_identifier());
    }

    public function test_get_all(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->repository();

        $feature1 = $factory->ai()->feature()->entity()
            ->set_component('mod_test1')
            ->set_name_identifier('all1')
            ->set_description_identifier('All1 Desc');
        $repository->insert($feature1);

        $feature2 = $factory->ai()->feature()->entity()
            ->set_component('mod_test2')
            ->set_name_identifier('all2')
            ->set_description_identifier('All2 Desc');
        $repository->insert($feature2);

        $all_features = $repository->get_all();

        $this->assertCount(2, $all_features);
    }
}
