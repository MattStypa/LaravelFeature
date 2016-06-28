# LaravelFeature

[![Build Status](https://travis-ci.org/MattStypa/LaravelFeature.svg?branch=master)](https://travis-ci.org/SpartzInc/LaravelFeature)
[![Latest Stable Version](https://poser.pugx.org/dose/laravelfeature/v/stable)](https://packagist.org/packages/dose/laravelfeature)
[![Total Downloads](https://poser.pugx.org/dose/laravelfeature/downloads)](https://packagist.org/packages/dose/laravelfeature)
[![Latest Unstable Version](https://poser.pugx.org/dose/laravelfeature/v/unstable)](https://packagist.org/packages/dose/laravelfeature)
[![License](https://poser.pugx.org/dose/laravelfeature/license)](https://packagist.org/packages/dose/laravelfeature)

Rampup and AB testing library inspired by Etsy\Feature for Laravel.

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

There is a ton of flexibility in defining features. We will start with the most verbose and then work down.

Each feature can have any number of variants with each variant defining it's own odds.

```
'feature' => [
    'variant_a' => 25,
    'variant_b' => 25,
    'variant_c' => 50,
],
```

In the above example, each variant has the specified chance of being selected. Variants with odds below 0 are normalized to 0. The variants are processed top to bottom. If the sum of odds exceeds 100 the feature is saturated and any variants above that threshold have no chance of being selected.

```
'feature' => [
    'variant_a' => 100,
    'variant_b' => 25,
    'variant_c' => 50,
],
```

The above feature will always return variant_a.

You can omit the odds and just specify the variants. In this case, the variants are evenly spread out.

```
'feature' => [
    'variant_a',
    'variant_b',
],
```

In this case, both variants have 50% chance of being selected.

These approaches can be mixed. It is important to note that variants with specified odds will be processed first and
then any remaining odds are distributed among the auto-scaled variants.

```
'feature' => [
    'variant_a' => 50,
    'variant_b',
    'variant_c',
],
```

The first variant has 50% chance of coming up while the other two have 25% chance each.

To specify a simple ON/OFF feature we just include a single variant.

```
'feature' => [
    'enabled' => 100,
],
```

This feature is always on. We can adjust the odds to turn it off completely or achieve a ramp-up functionality.

It is also possible to shorten the above example by just defining the odds for the feature itself.

```
'feature' => 50,
```

This feature will be on for 50% of the users.

## URL override
If the URL override is enabled you can force a particular variant by specifying it in the URL

```
?features=example_feature:variant_1
```
