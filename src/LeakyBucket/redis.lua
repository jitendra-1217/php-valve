-- Gets keys and arguments from command
local key = KEYS[1]
local maxBucketSize = tonumber(ARGV[1])
local leakRateValue = tonumber(ARGV[2])
local leakRateDuration = tonumber(ARGV[3])
local leakFullTime = tonumber(ARGV[4])
local now = tonumber(ARGV[5])
local cost = tonumber(ARGV[6])

local retryAfter = -1
local leak = 0
local allowAttempt = false
local lastUpdatedKey = 'lastUpdated'
local bucketSizeKey = 'bucketSize'

-- Read value of hash with given key, extracts last updated and bucket size
local current = redis.call('hmget', key, lastUpdatedKey, bucketSizeKey)
local lastUpdated = tonumber(current[1]) or 0
local bucketSize = tonumber(current[2]) or 0

-- Calculates units that should have been leaked between now and the time key got last updated
leak = math.floor((now - lastUpdated) * leakRateValue / leakRateDuration)
lastUpdated = now
bucketSize = math.max(0, bucketSize - leak)

-- Determine now if attempt for cost should be allowed or not.
-- If allowed fill in cost unit in the bucket and change related vars accordingly
allowAttempt = bucketSize + cost <= maxBucketSize
if allowAttempt then
      bucketSize = bucketSize + cost
else
      retryAfter = now + leakRateDuration
end

-- Finally set the values and TTL for the redis hash
redis.call('hmset', key, lastUpdatedKey, lastUpdated, bucketSizeKey, bucketSize)
redis.call('expire', key, leakFullTime)

-- Returns:
-- - Whether to allow attempt
-- - Max bucket size
-- - Remaining allowed attempts
-- - Reset time
-- - Minimum retry after time
return {
      allowAttempt,
      maxBucketSize,
      maxBucketSize - bucketSize,
      now + leakFullTime,
      retryAfter
}
