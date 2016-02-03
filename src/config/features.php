<?php

return [

    /**
     * All features are defined here.
     *
     * There are few different ways to define features. For the simplest features, that are either on or off, you can
     * simply specify the odds of it being on.
     *
     * You can also define features with multiple variants. You do this by providing an array. Each variant will have
     * equal odds of being selected.
     *
     * If you would like to have a feature with multiple variants but incomplete coverage you will need to specify odds
     * for each variant.
     *
     * You can also mix the two options. The variants without odds specified will be distributed evenly. In the last
     * example the 'auto_scaling_variant_a' and 'auto_scaling_variant_b' will each have 25% chance of being selected.
     */
    'example_simple_feature' => 30,

    'example_feature_with_variants' => [
        'variant_a',
        'variant_b',
    ],

    'example_feature_incomplete_coverage' => [
        'variant_1' => 20,
        'variant_2' => 30,
    ],

    'example_mixed_feature' => [
        'variant_with_defined_odds' => 50,
        'auto_scaling_variant_a',
        'auto_scaling_variant_b',
    ],

];
