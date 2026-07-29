<?php

namespace App\Libraries;

/**
 * SmartObject
 *
 * A robust wrapper class that treats arrays/objects as safe, iterable objects
 */

class SmartObject implements \IteratorAggregate, \Countable
{
    /**
     * Stores the data array.
     * @var array
     */
    protected array $items;

    /**
     * Constructor.
     * Accepts an array, object, or null and converts it to a safe array.
     *
     * @param array|object|null $items The data to wrap.
     */
    public function __construct($items = [])
    {
        // Force cast to array.
        // null -> [], object -> ['key'=>'val'], array -> array
        $this->items = (array) $items;
    }

    /**
     * Magic Getter (__get).
     * Called when accessing a property like $obj->email_host
     *
     * @param string $key The property name.
     * @return mixed|null Returns the value or NULL (Prevents "Undefined Property").
     */
    public function __get(string $key)
    {
        return $this->items[$key] ?? null;
    }

    /**
     * Magic Isset (__isset).
     * Called when using isset($obj->key) or empty($obj->key).
     *
     * @param string $key The property name.
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Magic ToString (__toString).
     * Prevents fatal errors if the object is treated as a string (e.g., echo $obj).
     *
     * @return string Empty string.
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * IteratorAggregate Implementation.
     * Allows the object to be used in foreach loops directly.
     * Example: foreach ($config->extensions as $ext) { ... }
     *
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Countable Implementation.
     * Allows using count() function on the object.
     * Example: if (count($config->extensions) > 0) { ... }
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Returns the raw array data.
     * Useful when you need to use array functions like array_merge or implode manually.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }
}