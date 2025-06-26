---
# https://vitepress.dev/reference/default-theme-home-page
layout: home

hero:
  name: "Beacon Metrics"
  text: "Simple Metrics for Laravel"
  tagline: "<code>composer require beacon-hq/metrics</code>"
  image: /assets/images/icon.png
  actions:
    - theme: brand
      text: Get Started
      link: ./install

features:
  - title: Aggregate Values
    details: Simple value metrics (e.g. count or average for a month)
  - title: Trends
    details: Calculate data over time, whether it's minutes, days, months, or years, or anything in between.
  - title: Previous Period Comparison
    details: Compare values between periods (e.g. count for this month and last month).
  - title: Projections
    details: Predict when you will reach a threshold, or what the value will be at a certain point in the future.
---

## What is Beacon Metrics?

Beacon Metrics was created to provide a simple way to calculate metrics and trends for [Beacon](https://beacon-hq.dev) and other Laravel applications.

It provides a fluent API that enables you to easily and quickly calculate metrics and trends for your application.

```php
Metrics::query(MyModel::query())
    ->count()
    ->byDay()
    ->between(now()->subMonth(), now())
    ->trends(); 
```
