# SmurfWorks / Model Finder

Laravel package for finding models and building a utility index in existing Laravel projects.

- Author:  [Glyn Simpson](https://www.smurfworks.com) / [@SmurfWorks](https://www.twitter.com/smurfworks)
- [Change Log](./CHANGELOG.md)
- [License](./LICENSE.md)  

## Key features

- Configure discovery of one or more namespaces to scan for Model classes
- Ignore discovery of specific models
- Discover available scopes and relations
- Discover information about attributes
- Apply custom meta to individual scopes, relations and models via PHP8 attributes

## About

This utility repository was created as a provided service for common functionality that I needed between multiple projects, both open source and proprietary - so it made sense to open source and release it so it can be used as a dependency in both contexts.

An example of model finder being used as a dependency an open source package: https://www.github.com/SmurfWorks/Sieve

## Install

```bash
composer require smurfworks/model-finder
```

Laravel should automatically discover the service provider and add an alias. The provider will only scan models once per request as it's a singleton service that will cache the result. 

## Usage

```php
/**
 * Get the index (implement laravel caching around this as you need)
 *
 * @var array $index
 */
$index = app('model-finder')->discover();

dd($index);
```

```php
// Trimmed for verbosity (...)
array:3 [
 "SmurfWorks\ModelFinderTests\SampleModels\User" => array:4 [
    "meta" => array:2 [
      "name" => "User"
      "describe" => "A user record represents a person's access to this system"
    ]
    "attributes" => array:9 [
      "id" => array:4 [
        "type" => "integer"
        "default" => null
        "fillable" => false
        "hidden" => false
      ]
      "name" => array:4 [
        "type" => "string"
        "default" => null
        "fillable" => true
        "hidden" => false
      ]
      "email" => array:4 [
        "type" => "string"
        "default" => null
        "fillable" => true
        "hidden" => false
      ]
      "password" => array:4 [
        "type" => "string"
        "default" => null
        "fillable" => true
        "hidden" => false
      ]

      ...
    ]
    "relations" => array:1 [
      "role" => array:3 [
        "type" => "BelongsTo"
        "model" => "SmurfWorks\ModelFinderTests\SampleModels\User\Role"
        "meta" => array:2 [
          "name" => "User role"
          "describe" => "The user's system role"
        ]
      ]
    ]
    "scopes" => array:2 [
      "activated" => array:1 [
        "meta" => array:2 [
          "name" => "Activated users"
          "describe" => "Activated users have set a password."
        ]
      ]
      "subscribed" => array:1 [
        "meta" => array:2 [
          "name" => "Subscribed"
          "describe" => "Users that are opted in to receive the newsletter."
        ]
      ]
    ]
  ]
  "SmurfWorks\ModelFinderTests\SampleModels\User\Permission" => array:4 [
    "meta" => array:2 [
      "name" => "User permission"
      "describe" => null
    ]

    ...
  ]

  ...
]
```

## Testing

The testing namespace contains schema migrations to setup some simple tables, and respective sample models. Because a part of the model finding queries the schema for table data, the unit tests will run these migrations to test model discovery.

```bash
./vendor/bin/phpunit
```

## Contributions

Contributions and issue reporting are welcome but this project is mostly the foundation for other projects I work on, so be warned there may be a significant amount of arbitrary decision making when proceeding.
