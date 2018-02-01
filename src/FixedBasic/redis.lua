-- Gets keys and arguments from command
local resourceKey = KEYS[1]
local ttl = tonumber(ARGV[1])
local worth = tonumber(ARGV[2])

-- By default remaining window is the ttl else we get it by calling ttl command
local remaining = ttl

-- Increments the key and if it was first time, issues setex command
local hits = tonumber(redis.call('incrby', resourceKey, worth))
if hits == worth then
    redis.call('setex', resourceKey, ttl, worth)
else
    remaining = tonumber(redis.call('ttl', resourceKey))
end

-- Returns current hits and remaining window secs
return {
    hits,
    remaining
}
