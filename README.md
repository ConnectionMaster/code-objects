# Code Objects
PHP Library which includes classes for the resolution of class names and callables, as well as the manipulation of
code construct strings such as class names, method names, CSS classes, HTML5 ids, table names, and more.

Using this library?  I'd love to hear about it! Star the repo, or mention [@jonesiscoding](https://twitter.com/jonesiscoding/)
in a tweet!

## Construct Objects

There are three main construct objects: `ConstructString`, `ClassString` and `MethodString`.  Both `ClassString` and
`MethodString` are extensions of `ConstructString`.

The base `ConstructString` class to work with the name of any coding construct such as a table, class, method, key, etc.

The more specific `ClassString` and `MethodString` contain additional methods for dealing with situations specific to a
method name or a class name such as extracting the class name, the namespace, the function name, etc.

### Common Methods

| Method     | Purpose                                                                                   |
|------------|-------------------------------------------------------------------------------------------|
| `css`      | Sanitizes a string for use as a CSS class, or HTML5 id.                                   |
| `camelize` | Changes _class_name_ to _className_ (camelCase)                                           |
| `classify` | Changes _class_name_ to _ClassName_ (PascalCase)                                          |
| `slugify`  | Removes all special characters and replaced spacers with dashes or the provided separator | 
| `snakeize`  | Changes _ClassName_ to _class_name_ (snake_case)                                          |

## Resolver Classes

The resolver classes are aimed at allowing for the resolution of PHP classes and callables.  At most times during
development, the use of autoloaders and PSR-0 or PSR-4 structure would prevent the need for the resolution of classes
and callables. 

These classes can be useful, however, in situations where callables are configured outside your code base, such as
in a YAML or XML configuration file or in a database. Often in these situations, callables and classes are referred to
with a key or id, rather than the fully qualified class name

Another situation in which these classes can be useful is when you have multiple classes that could be used, and these
classes exist in multiple name spaces. For example, when some classes that implement a specific interface are part of
your code base, but others may be part of a vendor library.

Each class contains one main method: `resolve`, which will resolve the given string, array, or object.

### Class Resolver

At instantiation, this class is provided a namespace or "sibling" class (from which to derive a namespace).  An array
of namespaces or sibling classes may also be provided.

When the `resolve` method is called, it resolves the provided string into a fully qualified class name by matching to
existing classes within the namespaces provided to the resolver at instantiation.

### Implementation Resolver

This class functions the same as the `ClassResolver`, but only resolves classes that implement the interface given at 
the time of instantiation.

### Callback Resolver

The `CallbackResolver` class is also provided a namespace or "sibling" class at instantiation, and will also take an
array of namespaces or sibling classes.

When the `resolve` method is called, it resolves the provided string, array, or object into a `\Closure` by first
resolving any class as needed, instantiating and needed object (if possible), verifying that the requested method is
callable, then converting the callable into a `\Closure`.

The item to resolve may be in any of these formats:  

* `ClassName::methodString` (works with non-static methods too)
* `\Fully\Qualified\ClassName::methodString` (works with non-static methods too)
* `['ClassName', 'methodString']`
* `['\Fully\Qualified\ClassName', 'methodString']`
* `'InvokableClassName'`
* `'\Fully\Qualified\InvokableClassName`

These may also be provided. Though they would need no resolution, they would be validated to be callable and turned
into a `\Closure`.

* `InvokableObject`
* [Object, 'methodString']

### ContainerAwareCallbackResolver

The `ContainerAwareCallbackResolver` must be provided a *PSR-11 Container Interface* at instantiation, and may also be
provided optional namespaces or "sibling" classes at instantiation.  The `ContainerInterface` object is used as a
dependency injection service locator, for example, the container used in the Symfony framework.

When the `resolve` method is called, this resolver type will attempt to match the *class* portion of any item to an 
*id* of a service in the `ContainerInterface`, then proceed with resolving the rest of the callback.

### Closure

The `Closure` class is a super-charged drop-in replacement for PHP 7.1's `\Closure::fromCallable`, and works using a
method of the same name.  This class is not intended for separate use, but instead does the "heavy lifting" for the 
`CallbackResolver`, taking either an array or a string that should be callable, instantiates any class into an object
(if necessary and possible), then creates a closure from the resulting callable.

In PHP 7.1+, the original `\Closure::fromCallable` is used for the final step.  In previous versions, the resulting
`\Closure` is created "the old fashioned way"


