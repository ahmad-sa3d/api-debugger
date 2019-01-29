<?php

namespace Lanin\Laravel\ApiDebugger\Collections;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Lanin\Laravel\ApiDebugger\Collection;

class QueriesCollection implements Collection
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $queries;

    /**
     * QueriesCollection constructor.
     *
     */
    public function __construct()
    {
        $this->connection = app('db.connection');
        $this->queries = collect();

        $this->listen();
    }

    /**
     * Collection name.
     *
     * @return string
     */
    public function name()
    {
        return 'database';
    }

    /**
     * Returns resulting collection.
     *
     * @return array
     */
    public function items()
    {
        return [
            'total' => $this->queries->count(),
            'time' => $this->queries->sum('time'),
            'items' => $this->queries->toArray(),
            'duplicated' => $this->getDuplicatedQueries()->toArray(),
        ];
    }

    /**
     * Duplicated Queries
     *
     * @return Collection
     */
    protected function getDuplicatedQueries(): \Illuminate\Support\Collection
    {
        return $this->queries->groupBy(function ($data) {
            return $data['connection'] . ':' . $data['query'];
        })
            ->filter(function ($group) {
                return $group->count() > 1;
            })
            ->map(function (\Illuminate\Support\Collection $group) {
                $first = $group->first();
                return [
                    'connection' => $first['connection'],
                    'query' => $first['query'],
                    'executions_count' => $group->count(),
                    'total_time' => $group->sum('time'),
                ];
            })->values();
    }

    /**
     * Listen query events.
     */
    public function listen()
    {
        $this->connection->enableQueryLog();

        $this->connection->listen(function (QueryExecuted $event) {
            $this->logQuery($event->connectionName, $event->sql, $event->bindings, $event->time);
        });
    }

    /**
     * Log DB query.
     *
     * @param string $connection
     * @param string $query
     * @param array $bindings
     * @param float $time
     */
    public function logQuery($connection, $query, array $bindings, $time)
    {
        if (! empty($bindings)) {
            $query = vsprintf(
            // Replace pdo bindings to printf string bindings escaping % char.
                str_replace(['%', '?'], ['%%', "'%s'"], $query),

                // Convert all query attributes to strings.
                $this->normalizeQueryAttributes($bindings)
            );
        }

        // Finish query with semicolon.
        $query = rtrim($query, ';') . ';';

        $this->queries->push(compact('connection', 'query', 'time'));
    }

    /**
     * Be sure that all attributes sent to DB layer are strings.
     *
     * @param  array $attributes
     * @return array
     */
    protected function normalizeQueryAttributes(array $attributes)
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $result[] = $this->convertAttribute($attribute);
        }

        return $result;
    }

    /**
     * Convert attribute to string.
     *
     * @param  mixed $attribute
     * @return string
     */
    protected function convertAttribute($attribute)
    {
        try {
            return (string) $attribute;
        } catch (\Exception $e) {
            switch (true) {
                // Handle DateTime attribute pass.
                case $attribute instanceof \DateTime:
                    return $attribute->format('Y-m-d H:i:s');

                // Handle callables.
                case $attribute instanceof \Closure:
                    return $this->convertAttribute($attribute());

                // Handle arrays using json by default or print_r if error occurred.
                case is_array($attribute):
                    $json = json_encode($attribute);

                    return json_last_error() === JSON_ERROR_NONE
                        ? $json
                        : print_r($attribute);

                // Handle all other object.
                case is_object($attribute):
                    return get_class($attribute);

                // For all unknown.
                default:
                    return '?';
            }
        }
    }
}
