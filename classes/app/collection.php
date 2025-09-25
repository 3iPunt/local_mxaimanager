<?php

namespace local_mxaimanager\app;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\exceptions\empty_collection_exception;

/**
 * @template T
 */
class collection implements \Iterator, \Countable, \ArrayAccess
{
    /** @var T[] */
    protected array $items = [];

    /**
     * @param T[] $items
     */
    public function __construct(array $items = [])
    {
        $this->set($items);
    }

    /**
     * @param T[] $items
     */
    public function set(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @param callable(T): string $selector
     * @return collection<T>
     */
    public function sort_asc(callable $selector): self
    {
        $this->sort($selector);
        return $this;
    }

    /**
     * @param callable(T): string $selector
     * @return collection<T>
     */
    public function sort_desc(callable $selector): self
    {
        $this->sort($selector, false);
        return $this;
    }

    /**
     * Example:
     * $test = new TestCollection();
     * $test->sort(function(entity $entity) {
     *    return $entity->id;
     * });
     *
     * @param callable(T): string $selector
     * @param bool $direction_asc
     * @return void
     */
    private function sort(callable $selector, bool $direction_asc = true): void
    {
        usort($this->items, static function ($a, $b) use ($selector, $direction_asc) {
            if ($direction_asc) {
                return strnatcasecmp(
                    $selector($a),
                    $selector($b)
                );
            }

            return strnatcasecmp(
                $selector($b),
                $selector($a)
            );
        });
    }

    /**
     * Example:
     * $test = new TestCollection();
     * $names = $collection->pluck(function($instance) {
     *    return $instance['name'] ?? null;
     * });
     *
     * @param callable(T): mixed $callback
     * @return self
     */
    public function pluck(callable $callback): self
    {
        $values = [];
        foreach ($this->items as $item) {
            $values[] = $callback($item);
        }

        return new static($values);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * The model needs to implement \JsonSerializable and
     * use the method "jsonSerialize" for this to work.
     *
     * @param bool $indexed
     * @return array
     * @throws \JsonException
     */
    public function to_array(bool $indexed = false): array
    {
        $encoded = json_encode($this->items, JSON_THROW_ON_ERROR);
        $items = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);

        if ($indexed) {
            return array_values($items);
        }

        return $items;
    }

    public function implode(string $separator): string
    {
        return implode($separator, $this->items);
    }

    /**
     * Adds a string of items to the collection
     * @param string $text
     * @param string $separator
     * @return collection<T>
     */
    public function explode(string $text, string $separator = ','): self
    {
        foreach (explode($separator, $text) as $item) {
            $this->append(trim($item));
        }

        return $this;
    }

    /**
     * @param string $key
     * @return T|null
     * @throws empty_collection_exception
     */
    public function by_key(string $key): mixed
    {
        if ($this->empty()) {
            throw new empty_collection_exception('Key not found in collection');
        }

        return $this->items[$key] ?? null;
    }

    /**
     * @return T
     * @throws empty_collection_exception
     */
    public function first(): mixed
    {
        if ($this->empty()) {
            throw new empty_collection_exception('No first item in collection');
        }

        return reset($this->items);
    }

    /**
     * @return T
     * @throws empty_collection_exception
     */
    public function last(): mixed
    {
        if ($this->empty()) {
            throw new empty_collection_exception('No last item in collection');
        }

        return end($this->items);
    }

    /**
     * @param int $offset
     * @param int $length
     * @return collection<T>
     */
    public function slice(int $offset, int $length): self
    {
        $this->items = array_values(array_slice($this->items, $offset, $length));

        return $this;
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->items);
    }

    /**
     * @return bool
     */
    public function not_empty(): bool
    {
        return !$this->empty();
    }

    /**
     * @param callable(T): bool $callback
     * @return collection<T>
     */
    public function filter(callable $callback): self
    {
        return new static(array_filter($this->items, $callback));
    }

    /**
     * @param callable(T): mixed $callback
     * @return collection<T>
     */
    public function map(callable $callback): self
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * Example:
     * $test = new TestCollection();
     * $list = $collection->to_list(
     *    function($item){
     *       return $item->id;
     *    },
     *    function($item){
     *       return $item->name;
     *    }
     * );
     *
     * @param callable(T): mixed $key_callback
     * @param callable(T): mixed $value_callback
     * @param bool $append_items
     * @return collection<T>
     */
    public function to_list(callable $key_callback, callable $value_callback, bool $append_items = false): self
    {
        $items = [];
        foreach ($this->items as $instance) {
            if ($append_items) {
                $items[$key_callback($instance)][] = $value_callback($instance);
                continue;
            }

            $items[$key_callback($instance)] = $value_callback($instance);
        }

        return new static($items);
    }

    /**
     * @param int $offset
     * @param int $length
     * @param T[] $replacement
     * @return collection<T>
     */
    public function splice(int $offset, int $length, array $replacement): self
    {
        $items = array_splice($this->items, $offset, $length, $replacement);

        return new static($items);
    }

    /**
     * @param int $times
     * @return collection<T>
     */
    public function shuffle(int $times = 1): self
    {
        for ($i = 0; $i < $times; $i++) {
            shuffle($this->items);
        }

        return $this;
    }

    /**
     * @param mixed $value
     * @param string $field
     * @return collection<T>
     */
    public function find(mixed $value, string $field = ''): self
    {
        $found = [];
        foreach ($this as $item) {
            if (is_object($item) && isset($item->$field)) {
                if ($item->$field == $value) {
                    $found[] = $item;
                }
            } elseif (is_array($item) && isset($item[$field])) {
                if ($item[$field] == $value) {
                    $found[] = $item;
                }
            } elseif (empty($field) && $item == $value) {
                $found[] = $item;
            }
        }

        return new static($found);
    }

    /**
     * @param T $item
     * @return collection<T>
     */
    public function append(mixed $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param T $item
     * @return collection<T>
     */
    public function prepend(mixed $item): collection
    {
        array_unshift($this->items, $item);

        return new static($this->items);
    }

    /**
     * @param collection<T> $collection
     * @return collection<T>
     */
    public function merge(self $collection): self
    {
        foreach ($collection as $item) {
            $this->append($item);
        }

        return $this;
    }

    /**
     * @param callable(T): mixed $field_callback
     * @param mixed $value
     * @return bool
     */
    public function contains(callable $field_callback, mixed $value): bool
    {
        foreach ($this->items as $item) {
            if ($field_callback($item) === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $key
     * @return T[]
     */
    public function column(string $key): array
    {
        return array_column($this->items, $key);
    }

    /**
     * @param mixed $offset
     * @param T $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
            return;
        }

        $this->items[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     * @return T|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @return T|false
     */
    public function current(): mixed
    {
        return current($this->items);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->items);
    }

    /**
     * @return string|int|null
     */
    public function key(): string|int|null
    {
        return key($this->items);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return array_key_exists(key($this->items), $this->items);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->items);
    }
}
