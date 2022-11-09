<?php

namespace SmurfWorks\ModelFinder;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModelFinder
{
    /**
     * Flag to see if this has run or not.
     *
     * @var boolean
     */
    protected $warm = false;

    /**
     * Models that the service has found.
     *
     * @var array
     */
    protected $models = [];

    /**
     * The namespaces to find models in.
     *
     * @var array
     */
    protected $namespaces = [];

    /**
     * Set the namespaces to use, if a single namespace string is used,
     * convert to an array.
     *
     * @var array|string $namespaces The namespaces to discover models from
     *
     * @return self
     */
    public function configure($namespaces) : self
    {
        $this->namespaces = (!is_array($namespaces))
            ? [$namespaces]
            : $namespaces;

        return $this;
    }

    /**
     * Discover all of the models that implement the sieve interface.
     *
     * @return array
     */
    public function discover()
    {
        if (!$this->warm) {
            $this->process();
        }

        return $this->models;
    }

    /**
     * Prepare the classes for discovery.
     *
     * @return void
     */
    protected function process() : void
    {
        /**
         * Get all classes in the given namespace(s).
         *
         * @var array $classes
         */
        $classes = [];

        foreach ($this->namespaces as $namespace) {
            $classes = array_merge(
                $classes,
                ClassFinder::getClassesInNamespace($namespace, ClassFinder::RECURSIVE_MODE)
            );
        }

        /**
         * Prepare a list of model classes.
         *
         * @var array $models
         */
        $models = [];

        foreach ($classes as $class) {
            if (!is_subclass_of($class, Model::class)) {
                continue;
            }

            $models[] = $class;
        }

        foreach ($models as $modelName) {
            $this->populate($modelName);
        }

        $this->warm = true;
    }

    /**
     * Populate data about the model class.
     *
     * @param string $modelClass The model class to analyse
     *
     * @return void
     */
    protected function populate(string $modelClass) : void
    {
        /**
         * Get a reflection of the given model instance.
         *
         * @var \ReflectionClass $classReflection
         */
        $classReflection = (new \ReflectionClass($modelClass));

        if ($classReflection->isAbstract()) {
            return;
        }

        /**
         * Create an instance of the model.
         *
         * @var Model $instance
         */
        $instance = (new $modelClass);

        /* Ignore this this model if requested */
        if ($this->hasAttribute($classReflection, 'IgnoreDiscovery')) {
            return;
        }

        /* Register the model */
        $this->models[$modelClass] = [
            'meta' => [
                'name' => $this->parseAttribute($classReflection, 'Name', $classReflection->getName()),
                'describe' => $this->parseAttribute($classReflection, 'Describe'),
            ],
            'attributes' => [],
            'relations' => [],
            'scopes' => []
        ];

        /* Loop attributes */
        foreach (Schema::getColumnListing($table = $instance->getTable()) as $column) {
            /**
             * Attempt to set a default for the column.
             *
             * @var mixed $default
             */
            $default = null;

            try {
                $default = $instance->getAttribute($column);
            } catch (\Throwable $e) {
                // Do nothing when a default cannot be retrieved
            }

            $this->models[$modelClass]['attributes'][$column] = [
                'type' => DB::getSchemaBuilder()->getColumnType($table, $column),
                'default' => $default,
                'fillable' => in_array($column, $instance->getFillable()),
                'hidden' => in_array($column, $instance->getHidden())
            ];
        }

        /* Loop the public methods */
        foreach ($classReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $publicMethod) {
            /* Ignore this this method if requested */
            if ($this->hasAttribute($publicMethod, 'IgnoreDiscovery')) {
                continue;
            }

            /* Only methods on the model class specifically, and are not __FUNCTION__ specifically */
            if ($publicMethod->class != get_class($instance)
                || $publicMethod->getName() == __FUNCTION__
            ) {
                continue;
            }

            try {
                if (substr($publicMethod->getName(), 0, 5) === 'scope'
                    && count($publicMethod->getParameters()) === 1
                ) {
                    /**
                     * Convert the method name to scope accessor.
                     *
                     * @var string $name
                     */
                    $name = Str::camel(substr($publicMethod->getName(), 5));

                    $this->models[$modelClass]['scopes'][$name] = [
                        'meta' => [
                            'name' => $this->parseAttribute($publicMethod, 'Name', $name),
                            'describe' => $this->parseAttribute($publicMethod, 'Describe'),
                        ]
                    ];

                    continue;
                }

                /* Relations will not have parameters */
                if (count($publicMethod->getParameters()) > 0
                    || substr($publicMethod->getName(), -9) === 'Attribute'
                ) {
                    continue;
                }

                /**
                 * Get the return data from the method.
                 *
                 * @var mixed $return
                 */
                $return = $publicMethod->invoke($instance);

                /* If we find a relation, register it */
                if ($return instanceof Relation) {
                    $this->models[$modelClass]['relations'][$publicMethod->getName()] = [
                        'type' => (new \ReflectionClass($return))->getShortName(),
                        'model' => (new \ReflectionClass($return->getRelated()))->getName(),
                        'meta' => [
                            'name' => $this->parseAttribute($publicMethod, 'Name', $publicMethod->getName()),
                            'describe' => $this->parseAttribute($publicMethod, 'Describe'),
                        ]
                    ];
                }

            } catch(\Throwable $e) {
                /* Ignore errors! */
                continue;
            }
        }
    }

    /**
     * Parse the meta attributes to get the first argument.
     *
     * @param \ReflectionClass|\ReflectionMethod $reflection    Reflection object to check
     * @param string                             $attributeName Attribute to find
     * @param string|null                        $default
     *
     * @return string|null
     */
    protected function parseAttribute($reflection, string $attributeName, $default = null)
    {
        /**
         * @var \ReflectionAttribute $attribute
         */
        foreach ($reflection->getAttributes() as $attribute) {
            if (Arr::last(explode('\\', $attribute->getName())) === $attributeName) {
                return Arr::first($attribute->getArguments());
            }
        }

        return $default;
    }

    /**
     * Look to see if the attribute exists on the reflection object.
     *
     * @param \ReflectionClass|\ReflectionMethod $reflection    Reflection object to check
     * @param string                             $attributeName Attribute to find
     *
     * @return boolean
     */
    protected function hasAttribute($reflection, string $attributeName)
    {
        /**
         * @var \ReflectionAttribute $attribute
         */
        foreach ($reflection->getAttributes() as $attribute) {
            if (Arr::last(explode('\\', $attribute->getName())) === $attributeName) {
                return true;
            }
        }

        return false;
    }
}
