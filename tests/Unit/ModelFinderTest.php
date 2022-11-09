<?php

namespace SmurfWorks\ModelFinderTests\Unit;

use SmurfWorks\ModelFinderTests\SampleModels as Models;

class ModelFinderTest extends \SmurfWorks\ModelFinderTests\TestCase
{
    /**
     * The namespace(s) to use when testing the model finder.
     *
     * @var array
     */
    protected $namespaces = [
        'SmurfWorks\\ModelFinderTests\\SampleModels'
    ];

    /**
     * Test that attributes are found on the models.
     *
     * @return void
     */
    public function testAttributeDiscovery() : void
    {
        /**
         * Get a list of discovered classes.
         *
         * @var array $discovered
         */
        $discovered = app('model-finder')->configure($this->namespaces)->discover();

        $this->assertCount(3, $discovered);

        $this->assertContains(Models\User::class, array_keys($discovered));
        $this->assertContains(Models\User\Role::class, array_keys($discovered));
        $this->assertContains(Models\User\Permission::class, array_keys($discovered));

        /**
         * Get the attribute keys.
         *
         * @var array $attributes
         */
        $attributes = $discovered[Models\User::class]['attributes'];

        $this->assertContains('name', array_keys($attributes));
        $this->assertContains('email', array_keys($attributes));
        $this->assertContains('password', array_keys($attributes));
        $this->assertContains('role_id', array_keys($attributes));
        $this->assertContains('created_at', array_keys($attributes));
        $this->assertContains('updated_at', array_keys($attributes));
        $this->assertContains('deleted_at', array_keys($attributes));

        $this->assertEquals('string', $attributes['name']['type']);
        $this->assertEquals(null, $attributes['name']['default']);

        $this->assertEquals('boolean', $attributes['receive_newsletter']['type']);
        $this->assertEquals(false, $attributes['receive_newsletter']['default']);
    }

    /**
     * Test relations are found on the models.
     *
     * @return void
     */
    public function testRelationDiscovery() : void
    {
        /**
         * Get a list of discovered classes.
         *
         * @var array $discovered
         */
        $discovered = app('model-finder')->configure($this->namespaces)->discover();

        $this->assertContains('role', array_keys($discovered[Models\User::class]['relations']));
        $this->assertContains('users', array_keys($discovered[Models\User\Role::class]['relations']));
        $this->assertContains('permissions', array_keys($discovered[Models\User\Role::class]['relations']));
        $this->assertContains('roles', array_keys($discovered[Models\User\Permission::class]['relations']));
    }

    /**
     * Test that scope methods are found on the models.
     *
     * @return void
     */
    public function testScopeDiscovery() : void
    {
        /**
         * Get a list of discovered classes.
         *
         * @var array $discovered
         */
        $discovered = app('model-finder')->configure($this->namespaces)->discover();

        $this->assertContains('activated', array_keys($discovered[Models\User::class]['scopes']));
    }

    /**
     * Test doc-attributes are found on the models.
     *
     * @return void
     */
    public function testMetaDiscovery() : void
    {
        /**
         * Get a list of discovered classes.
         *
         * @var array $discovered
         */
        $discovered = app('model-finder')->configure($this->namespaces)->discover();

        $this->assertContains('name', array_keys($discovered[Models\User::class]['meta']));
        $this->assertContains('describe', array_keys($discovered[Models\User::class]['meta']));

        $this->assertNotNull($discovered[Models\User::class]['meta']['describe']);
    }
}
