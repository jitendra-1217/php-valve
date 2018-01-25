[![Build Status](https://travis-ci.org/jitendra-1217/php-valve.svg?branch=master)](https://travis-ci.org/jitendra-1217/php-valve) [![Latest Version](https://img.shields.io/github/release/jitendra-1217/php-valve.svg)](https://github.com/jitendra-1217/php-valve/releases)

## php-valve

Resource or API rate limiting/throttling.

## Installation

```
composer require jitendra/php-valve
```

## Usage Example

```php
// Instantiate limiter that allows 200 attempts per minute.
// Optionally pass custom \Predis\Client client.
$limiter = new \Jitendra\PhpValve\FixedBasic\Redis(60000, 200);
// Attempts passed resource, Optionally pass worth value.
$limiter->attempt('ip:resource');

// [
//     1,              // Allowed?
//     200,            // X-RateLimit-Limit
//     199,            // X-RateLimit-Remaining
//     1516612980700,  // X-RateLimit-Reset (in milliseconds)
//     1516612980700,  // X-RateLimit-RetryAfter (in milliseconds, -1 if not rate limited)
// ]
```

## Implementation

- __Fixed Basic__

  (Redis)

  Rate limit attempt to a resource by specifying limit for fixed duration. E.g. 100 attempts per minute.

- __Leaky Bucket__

  (Redis)

  Rate limit attempt to a resource via standard leaky bucket logic. E.g. With max burst of 200 and leak rate of 10 requests per second.
