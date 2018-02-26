-- Gets keys and arguments from command
local lastUpdatedKey   = KEYS[1]
local bucketSizeKey    = KEYS[2]
local maxBucketSize    = tonumber(ARGV[1])
local leakRateValue    = tonumber(ARGV[2])
local leakRateDuration = tonumber(ARGV[3])
local leakFullTime     = tonumber(ARGV[4])
local now              = tonumber(ARGV[5])
local worth            = tonumber(ARGV[6])

local retryAfter = -1

-- Reads values of 2 keys - last updated and current bucket size against key
local lastUpdated = tonumber(redis.call('get', lastUpdatedKey))
if lastUpdated == nil then lastUpdated = 0 end

local bucketSize = tonumber(redis.call('get', bucketSizeKey))
if bucketSize == nil then bucketSize = 0 end

-- Calculates units that should have been leaked between time the key got last updated and now
local leak = math.floor((now - lastUpdated) * leakRateValue / leakRateDuration)
bucketSize = math.max(0, bucketSize - leak)
lastUpdated = now

-- Determine now if attempt for worth should be allowed or not. If allowed fill
-- in worth unit in the bucket and change related vars accordingly
local allowAttempt = bucketSize + worth <= maxBucketSize
if allowAttempt then
      bucketSize = bucketSize + worth
      allowAttempt = 1
else
      allowAttempt = 0
      retryAfter = now + leakRateDuration
end

-- Finally set the values and TTL for the 2 REDIS keys
redis.call('setex', lastUpdatedKey, leakFullTime, lastUpdated)
redis.call('setex', bucketSizeKey, leakFullTime, bucketSize)

-- Returns:
-- - Whether to allow attempt
-- - Max bucket size
-- - Current bucket size
-- - Reset time
-- - Minimum retry after time
return {
      allowAttempt,
      maxBucketSize,
      maxBucketSize - bucketSize,
      now + leakFullTime,
      retryAfter
}
