-- Gets keys and arguments from command
local key   = KEYS[1]
local ttl   = tonumber(ARGV[1])
local worth = tonumber(ARGV[2])

-- Increments the key and if it was first time, issues setex command
local hits = tonumber(redis.call('incrby', key, worth))
if hits == worth then
    redis.call('expire', key, ttl)
else
    -- By default remaining window(i.e. ttl) is the input provided else we get
    -- it by calling redis command
    ttl = tonumber(redis.call('ttl', key))
end

-- Returns current hits and remaining window secs(i.e. ttl)
return {
    hits,
    ttl
}
