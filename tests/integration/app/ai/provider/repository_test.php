<?php

namespace local_mxaimanager\integration\app\ai\provider;


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

        // Remove all providers since db/install.php is called during setUp, which seeds the default providers.
        foreach ($base_factory->ai()->provider()->repository()->get_all() as $provider) {
            $base_factory->ai()->provider()->repository()->delete($provider->get_id());
        }
    }

    public function test_get_table(): void
    {
        $repository = base_factory::make()->ai()->provider()->repository();
        $this->assertEquals('local_mxaimanager_providers', $repository->get_table());
    }

    public function test_get_by_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->repository();

        $time = time();
        $provider = $factory->ai()->provider()->entity()
            ->set_name('Test Provider')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time)
            ->set_timemodified($time);

        $provider->set_id($repository->insert($provider));

        $retrieved_provider = $repository->get_by_id($provider->get_id());

        // Verify all fields match.
        $this->assertEquals($provider->get_id(), $retrieved_provider->get_id());
        $this->assertEquals('Test Provider', $retrieved_provider->get_name());
        $this->assertEquals(
            \local_mxaimanager\app\ai\provider\providers\openai::class,
            $retrieved_provider->get_classname()
        );
        $this->assertEquals($time, $retrieved_provider->get_timecreated());
        $this->assertEquals($time, $retrieved_provider->get_timemodified());
    }

    public function test_get_by_name(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->repository();

        $time = time();
        $provider = $factory->ai()->provider()->entity()
            ->set_name('Unique Test Provider')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\mistral::class)
            ->set_timecreated($time)
            ->set_timemodified($time);

        $provider->set_id($repository->insert($provider));

        $retrieved_provider = $repository->get_by_name('Unique Test Provider');

        // Verify fields match.
        $this->assertEquals($provider->get_id(), $retrieved_provider->get_id());
        $this->assertEquals('Unique Test Provider', $retrieved_provider->get_name());
        $this->assertEquals(
            \local_mxaimanager\app\ai\provider\providers\mistral::class,
            $retrieved_provider->get_classname()
        );
        $this->assertEquals($time, $retrieved_provider->get_timecreated());
        $this->assertEquals($time, $retrieved_provider->get_timemodified());
    }

    public function test_get_all(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->repository();

        $time1 = time();
        $provider1 = $factory->ai()->provider()->entity()
            ->set_name('Provider 1')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time1)
            ->set_timemodified($time1);
        $provider1->set_id($repository->insert($provider1));

        $time2 = time() + 1;
        $provider2 = $factory->ai()->provider()->entity()
            ->set_name('Provider 2')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\mistral::class)
            ->set_timecreated($time2)
            ->set_timemodified($time2);
        $provider2->set_id($repository->insert($provider2));

        $all_providers = $repository->get_all();

        $this->assertCount(2, $all_providers);

        // Convert to array for easier checking.
        $providers_array = iterator_to_array($all_providers);

        // Find the providers by name.
        $found_provider1 = null;
        $found_provider2 = null;
        foreach ($providers_array as $prov) {
            if ($prov->get_name() === 'Provider 1') {
                $found_provider1 = $prov;
            } elseif ($prov->get_name() === 'Provider 2') {
                $found_provider2 = $prov;
            }
        }

        $this->assertNotNull($found_provider1);
        $this->assertNotNull($found_provider2);

        // Verify details.
        $this->assertEquals(
            \local_mxaimanager\app\ai\provider\providers\openai::class,
            $found_provider1->get_classname()
        );
        $this->assertEquals(
            \local_mxaimanager\app\ai\provider\providers\mistral::class,
            $found_provider2->get_classname()
        );
    }

    public function test_get_all_by_classname(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->repository();

        $time = time();

        // Create providers with different classnames
        $openai_provider = $factory->ai()->provider()->entity()
            ->set_name('OpenAI Provider 1')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $openai_provider->set_id($repository->insert($openai_provider));

        $openai_provider2 = $factory->ai()->provider()->entity()
            ->set_name('OpenAI Provider 2')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $openai_provider2->set_id($repository->insert($openai_provider2));

        $mistral_provider = $factory->ai()->provider()->entity()
            ->set_name('Mistral Provider')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\mistral::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $mistral_provider->set_id($repository->insert($mistral_provider));

        // Test filtering by OpenAI classname
        $openai_providers = $repository->get_all_by_classname(
            \local_mxaimanager\app\ai\provider\providers\openai::class
        );

        $this->assertCount(2, $openai_providers);
        $providers_array = iterator_to_array($openai_providers, false);

        // Verify all returned providers have the correct classname
        foreach ($providers_array as $provider) {
            $this->assertEquals(
                \local_mxaimanager\app\ai\provider\providers\openai::class,
                $provider->get_classname()
            );
        }

        // Check both providers are returned
        $names = array_map(fn($p) => $p->get_name(), $providers_array);
        $this->assertContains('OpenAI Provider 1', $names);
        $this->assertContains('OpenAI Provider 2', $names);
    }

    public function test_get_all_by_classnames(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->repository();

        $time = time();

        // Create providers with different classnames
        $openai_provider = $factory->ai()->provider()->entity()
            ->set_name('OpenAI Provider')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $openai_provider->set_id($repository->insert($openai_provider));

        $mistral_provider = $factory->ai()->provider()->entity()
            ->set_name('Mistral Provider')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\mistral::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $mistral_provider->set_id($repository->insert($mistral_provider));

        $anthropic_provider = $factory->ai()->provider()->entity()
            ->set_name('Anthropic Provider')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\anthropic::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $anthropic_provider->set_id($repository->insert($anthropic_provider));

        // Test filtering by multiple classnames
        $selected_providers = $repository->get_all_by_classnames([
            \local_mxaimanager\app\ai\provider\providers\openai::class,
            \local_mxaimanager\app\ai\provider\providers\mistral::class
        ]);

        $this->assertCount(2, $selected_providers);
        $providers_array = iterator_to_array($selected_providers, false);

        // Verify only providers with the specified classnames are returned
        $classnames = array_map(fn($p) => $p->get_classname(), $providers_array);
        $this->assertContains(\local_mxaimanager\app\ai\provider\providers\openai::class, $classnames);
        $this->assertContains(\local_mxaimanager\app\ai\provider\providers\mistral::class, $classnames);
        $this->assertNotContains(\local_mxaimanager\app\ai\provider\providers\anthropic::class, $classnames);

        // Check specific providers are included
        $names = array_map(fn($p) => $p->get_name(), $providers_array);
        $this->assertContains('OpenAI Provider', $names);
        $this->assertContains('Mistral Provider', $names);
        $this->assertNotContains('Anthropic Provider', $names);
    }

    public function test_get_all_by_classnames_empty_array(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->repository();

        // Test with empty array - should return empty collection
        $empty_result = $repository->get_all_by_classnames([]);

        $this->assertCount(0, $empty_result);
    }

    public function test_get_all_by_classname_no_matches(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->repository();

        // Test with classname that doesn't exist
        $no_matches = $repository->get_all_by_classname('NonExistentClass');

        $this->assertCount(0, $no_matches);
    }
}
