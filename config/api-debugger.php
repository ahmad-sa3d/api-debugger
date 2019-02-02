<?php

return [
    /**
     * Enable or disable API debugging mode
     */
    'enabled' => env('ENABLE_API_DEBUG', false),

    /**
     * Specify what data to collect.
     */
    'collections' => [
        // Database queries.
        \Lanin\Laravel\ApiDebugger\Collections\QueriesCollection::class,

        // Show cache events.
        \Lanin\Laravel\ApiDebugger\Collections\CacheCollection::class,

        // Profile custom events.
        \Lanin\Laravel\ApiDebugger\Collections\ProfilingCollection::class,

        // Memory usage.
        \Lanin\Laravel\ApiDebugger\Collections\MemoryCollection::class,
    ],
];
