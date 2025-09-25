<?php

function xmldb_local_mxaimanager_install(): void
{
    $base_factory = \local_mxaimanager\app\factory::make();

    // Seed default AI actions.
    $base_factory->ai()->action()->manager()->install_defaults();

    // Seed default AI providers.
    $base_factory->ai()->provider()->manager()->install_defaults();
}
