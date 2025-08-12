# Changelog

All notable changes to this project will be documented in this file.

## [5.1.0] - 2024-01-XX

### Added
- **Multi-Strategy Converter Architecture**: Introduced three different conversion strategies to optimize for different use cases
  - `MemoryOptimizedConverter`: Minimal memory footprint (~400KB), ideal for web requests
  - `CachedConverter`: Full caching (~4MB), 2-3x faster for repeated conversions
  - `SmartConverter`: Adaptive optimization (600KB-1.5MB), balances memory and performance
- `ConverterFactory` for creating strategy-specific converters
- Strategy selection methods: `useMemoryOptimized()`, `useCached()`, `useSmart()`, `useAutoStrategy()`
- Memory usage tracking with `getMemoryUsage()` method
- Comprehensive benchmark tools:
  - `benchmark/run.php`: Enhanced with multi-strategy comparison
  - `benchmark/compare-strategies.php`: Detailed performance analysis
  - `bin/benchmark-strategy`: Command-line benchmark tool
- New test suites for converter strategies and memory usage

### Changed
- Default converter now uses the memory-optimized strategy
- `Pinyin::converter()` now accepts an optional strategy parameter
- Improved benchmark output with strategy comparison tables

### Performance Improvements
- Cached strategy provides 2-3x speedup for batch processing
- Smart strategy reduces memory usage for short texts by skipping unnecessary dictionaries
- Memory-optimized strategy maintains low memory footprint while processing

### Documentation
- Added comprehensive performance optimization guide in README
- Added strategy selection guidelines
- Added memory management best practices
- Added performance comparison data

### Backward Compatibility
- All existing APIs remain unchanged and fully compatible
- Default behavior remains the same (memory-optimized)
- New features are opt-in

## [5.0.0] - Previous Release

### Changed
- Minimum PHP version requirement to 8.0.2
- ...