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
}
