# Baukasten CMS Cache Implementation

## Overview

This implementation adds comprehensive caching to the Kirby CMS JSON endpoints to improve performance for the Astro frontend integration. The cache system is designed to be automatic, self-invalidating, and transparent to the frontend.

## Features Implemented

### 1. API Endpoint Caching

- **Cached Endpoints**: `global.json` and `index.json`
- **Cache Duration**:
  - `global.json`: 60 minutes (site configuration changes infrequently)
  - `index.json`: 30 minutes (page index updates more frequently)
- **Multi-language Support**: Separate cache keys per language
- **Cache Headers**: `X-Cache-Status` header indicates HIT/MISS

### 2. Automatic Cache Invalidation

The cache automatically clears when content changes:

- Page creation, update, or deletion
- Site configuration updates
- File upload, update, or deletion
- Works across all languages

### 3. Page Caching

- Enabled for all pages except error pages and preview pages
- Improves overall site performance
- Ignores pages with `preview` in the URI

### 4. Cache Management Interface

- **Status Page**: Visit `/cache-status` to view cache information
- **Manual Clearing**: Admin users can clear caches via web interface
- **API Endpoints**: POST to `/cache-clear` or `/cache-clear/{type}`

## Technical Implementation

### Configuration Changes

```php
// site/config/config.php
'cache' => [
    'pages' => [
        'active' => true,
        'ignore' => function ($page) {
            return $page->template()->name() === 'error' ||
                   str_contains($page->uri(), 'preview');
        }
    ],
    'api' => [
        'active' => true,
        'type' => 'file'
    ]
],
'hooks' => [
    // Automatic cache invalidation on content changes
    'page.create:after' => function ($page) {
        kirby()->cache('api')->flush();
    },
    // ... more hooks for updates and deletions
]
```

### Cached Function Implementation

#### Before (uncached):

```php
function globalJson() {
    // Direct processing every time
    return Response::json($data);
}
```

#### After (cached):

```php
function globalJsonCached() {
    $cache = kirby()->cache('api');
    $cacheKey = 'global.' . $language;

    $cached = $cache->get($cacheKey);
    if ($cached !== null) {
        $response = Response::json($cached);
        $response->header('X-Cache-Status', 'HIT');
        return $response;
    }

    $data = globalJsonData();
    $cache->set($cacheKey, $data, 60); // Cache for 60 minutes

    $response = Response::json($data);
    $response->header('X-Cache-Status', 'MISS');
    return $response;
}
```

## Benefits for Astro Frontend

### 1. Performance Improvements

- **Faster Response Times**: Cached responses serve in milliseconds instead of seconds
- **Reduced Server Load**: Less processing for each request
- **Better UX**: Faster page loads in Astro development and production

### 2. Astro Compatibility

- **Same Response Format**: JSON structure remains identical
- **Cache Headers**: Astro can read `X-Cache-Status` for debugging
- **Language Support**: Multi-language caching works seamlessly
- **Build Process**: No changes needed to Astro build process

### 3. Development Experience

- **Cache Status**: Visit `/cache-status` to monitor cache performance
- **Manual Control**: Clear cache when needed via web interface
- **Automatic Updates**: Content changes automatically refresh cache

## Cache File Structure

```
storage/cache/
├── api/
│   ├── global.default
│   ├── global.en
│   ├── global.de
│   ├── index.default
│   ├── index.en
│   └── index.de
└── pages/
    └── [page cache files]
```

## Monitoring and Debugging

### 1. Cache Status Page

Visit `https://your-cms-domain.com/cache-status` to see:

- Cache activation status
- Cache type and location
- Cache size and keys
- Manual clearing controls

### 2. Response Headers

Check the `X-Cache-Status` header in API responses:

- `HIT`: Response served from cache
- `MISS`: Response generated fresh and cached

### 3. Performance Monitoring

- Monitor API response times before/after implementation
- Check cache hit rates via status page
- Observe reduced server load

## Compatibility Notes

### Astro Frontend

- ✅ No changes required to existing Astro code
- ✅ Same JSON response format
- ✅ Same endpoint URLs
- ✅ Multi-language support maintained
- ✅ Preview functionality preserved

### Kirby CMS

- ✅ Compatible with Kirby 4.x
- ✅ Works with existing plugins
- ✅ Panel functionality unchanged
- ✅ Content editing workflow unchanged

## Troubleshooting

### Cache Not Working

1. Check `/cache-status` page for configuration
2. Verify `storage/cache` directory is writable
3. Check if hooks are firing on content updates

### Stale Content

1. Manually clear cache via `/cache-status` page
2. Check if hooks are properly configured
3. Verify cache duration settings

### Performance Issues

1. Monitor cache hit rates
2. Adjust cache durations if needed
3. Consider using different cache drivers (memcached, redis)

## Future Enhancements

### Phase 2 Planned Improvements

1. **Split Endpoints**: Break `global.json` into focused endpoints
2. **Conditional Requests**: Add ETag support
3. **Response Compression**: Gzip JSON responses
4. **Cache Warming**: Pre-populate cache after content updates
5. **Analytics**: Detailed cache performance metrics

### Advanced Cache Drivers

Consider upgrading to:

- **Memcached**: For multi-server setups
- **Redis**: For advanced features and persistence
- **APCu**: For single-server high-performance setups

## Implementation Timeline

✅ **Completed**: Basic API endpoint caching
✅ **Completed**: Automatic cache invalidation
✅ **Completed**: Cache management interface
✅ **Completed**: Multi-language support
✅ **Completed**: Astro compatibility verification

## Testing

The implementation has been tested for:

- ✅ PHP syntax validation
- ✅ Kirby 4.x compatibility
- ✅ Multi-language functionality
- ✅ Automatic cache invalidation
- ✅ Astro frontend compatibility

## Conclusion

This cache implementation provides significant performance improvements for the Baukasten CMS → Astro frontend integration while maintaining full compatibility and adding powerful management features. The automatic invalidation ensures content freshness, while the management interface provides visibility and control.
