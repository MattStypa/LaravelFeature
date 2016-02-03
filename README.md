# LaravelFeature

Rampup and AB testing library inspired for Laravel.

## How to use
After you have added this package to your composer file you will need to register it's Service Provider.
```
'Dose\LaravelFeature\LaravelFeatureServiceProvider'
```

You also want to publish configuration so you can define your features.
```
php artisan config:publish dose/laravelfeature // Laravel 4
php artisan vendor:publish --provider="Dose\LaravelFeature\LaravelFeatureServiceProvider" // Laravel 5
```

To gain access to the API you need to resolve an instance out of the Service Container.
```
$feature = app()->make('Dose\LaravelFeature\LaravelFeatureInterface');
```
Of course, dependency injection can also be used.

### Get Variant
```
$feature->getVariant($name);
```
This will return a selected variant. It may also return NULL if the feature does not exist or if none of the variants were selected (features with incomplete coverage).

### Set Context
```
$feature->setContext($context);
```
This is not something commonly used but it lets you change the context in which a variant is selected. By default, user's session id is used. This means that the variant will be the same for the span of the session.

Sometimes you might want to rotate variants based on different criteria. For example, if you want all posts by a certain author to have a set of features consistent for all users. You can do that by setting the context.
```
$feature->setContext('post_author:' . $postAuthor);
```

## AB Testing
It is a common use case to run multiple AB tests with multiple variants at the same time. In order to get valuable test results, only one test can be active at once. In order to achieve this, you need to define a wrapper feature. The variants will represent each test.
 ```
 'tests' => [
     'test_a',
     'test_b',
     'test_c',
 ],
 'test_a' => [
    'variant_a',
    'variant_b',
 ],
 'test_b' => [
    'variant_a',
    'variant_b',
 ],
 'test_c' => [
    'variant_a',
    'variant_b',
 ],
 ```
This allows us to check if a particular test is active and then retrieve it's variant.
```
if ($feature->getVariant('tests') == 'test_a') {
    $variant = $feature->getVariant('test_a');
}
```

## Config
There are two configuration files that you can use to control the behavior.

### config.php
In this file, you can specify if you want users to be able to override the variant selection with a specific URL parameter.
```
'allow_override' => false,
```
This is disabled by default. You may control who can use the URL override by defining your own logic.
```
'allow_override' => Gate::allows('feature_url_override'),
```

### features.php
All your features are defined here.

#### Explicitly defined odds
```
'example_feature' => [
    'variant_1' => 25,
    'variant_2' => 25,
],
```
In this example variant_1 and variant_2 will be selected 25% of the time each and no variant will be selected 50% of the time.

You can also omit the odds. In this case these variants will be auto-scaled to fill remaining probability.
```
'example_feature' => [
    'variant_1' => 20,
    'variant_2',
    'variant_3',
],
```
In this case, variant_1 will be select 20% of the time and variant_2 and variant_3 40% of the time each.

In order to specify a simple on/off feature just add a single variant.
```
'example_feature' => [
    'on' => 50,
],
```

## URL override
If the URL override is enabled you can force a particular variant by specifying it in the URL
```
?features=example_feature:variant_1
```
