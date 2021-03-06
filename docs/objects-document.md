# Document
[Back to Navigation](README.md)

## Description

The `Document` object represents the [Top Level](http://jsonapi.org/format/#document-top-level) of a JSON API response. You can create it using [Helper\Parser](helper-parser.md).

This object implements the [Accessable interface](objects-introduction.md#value-access).

Property of: _none_

### Properties

_[Symbols definition](objects-introduction.md#symbols)_

You can use the [Accessable interface](objects-introduction.md#value-access) to access this properties.

|     | Key | Value | Note |
| --- | --- | ----- | ---- |
| #   | data | - [Resource Null object](objects-resource-null.md)<br />- [Resource Identifier object](objects-resource-identifier.md)<br />- [Resource Item object](objects-resource-item.md)<br />- [Resource Collection object](objects-resource-collection.md) | not allowed, if 'errors' exists |
| #   | errors | [Error Collection object](objects-error-collection.md) | not allowed, if 'data' exists |
| #   | meta | [Meta object](objects-meta.md) | |
| ?   | jsonapi | [Jsonapi object](objects-jsonapi.md) | |
| ?   | links | [Document Link object](objects-document-link.md) | |
| ?   | included | [Resource Collection object](objects-resource-collection.md) | not allowed, if 'data' doesn't exist |

## Usage

### Check if a value exist

You can check for all possible values using the `has()` method.

```php
use Art4\JsonApiClient\Helper\Parser;

$jsonapiString = '{"meta":{"info":"Testing the JsonApiClient library."}}';

$document = Parser::parseResponseString($jsonapiString);

var_dump($document->has('data'));
var_dump($document->has('errors'));
var_dump($document->has('meta'));
var_dump($document->has('jsonapi'));
var_dump($document->has('links'));
var_dump($document->has('included'));
```

This returns:

```php
false
false
true
false
false
false
```

### Get the keys of all existing values

You can get the keys of all existing values using the `getKeys()` method. Assume we have the same `$document` like in the last example.

```php
var_dump($document->getKeys());
```

This returns:

```php
array(
  0 => 'meta'
)
```

This can be useful to get available values:

```php
foreach($document->getKeys() as $key) {
    $value = $document->get($key);
}
```

### Get the containing data

You can get all (existing) data using the `get()` method.

```php
$data     = $document->get('data');
$errors   = $document->get('errors');
$meta     = $document->get('meta');
$jsonapi  = $document->get('jsonapi');
$links    = $document->get('links');
$included = $document->get('included');
```

> **Note:** Using `get()` on a non-existing value will throw an [AccessException](exception-introduction.md#accessexception). Use `has()` or `getKeys()` to check if a value exists.
