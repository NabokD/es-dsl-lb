<?php

declare(strict_types = 1);

namespace CodeTool\ElasticSearch\DSL\Aggregation\Bucket;

use CodeTool\ElasticSearch\DSL\Aggregation\ElasticSearchAggregationInterface;
use CodeTool\ElasticSearch\DSL\Sort\ElasticSearchSortInterface;
use CodeTool\ElasticSearch\ElasticSearchScript;

/**
 * TermsAggregation is a multi-bucket value source based aggregation
 * where buckets are dynamically built - one per unique value.
 * @see: http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html
 */
class ElasticSearchAggregationBucketTerms implements ElasticSearchAggregationInterface
{
    private $field = '';

    /**
     * @var ElasticSearchAggregationInterface[]
     */
    private $subAggregations = [];

    /**
     * @var string[]
     */
    private $meta = [];

    /**
     * @var int|null
     */
    private $size;

    /**
     * @var int|null
     */
    private $shardSize;

    /**
     * @var int|null
     */
    private $requiredSize;

    /**
     * @var int|null
     */
    private $minDocCount;

    /**
     * @var int|null
     */
    private $shardMinDocCount;

    /**
     * @var string
     */
    private $valueType = '';

    /**
     * @var string
     */
    private $order = '';

    /**
     * @var bool
     */
    private $orderAsc = false;

    /**
     * @var string
     */
    private $includePattern = '';

    /**
     * @var int|null
     */
    private $includeFlags;

    /**
     * @var string
     */
    private $excludePattern = '';

    /**
     * @var int|null
     */
    private $excludeFlags;

    /**
     * @var string
     */
    private $executionHint = '';

    /**
     * @var string
     */
    private $collectionMode = '';

    /**
     * @var bool|null
     */
    private $showTermDocCountError;

    /**
     * @var string[]
     */
    private $includeTerms = [];

    /**
     * @var string[]
     */
    private $excludeTerms = [];

    /**
     * @var ElasticSearchScript
     */
    private $script = '';

    public function field(string $field)
    {
        $this->field = $field;

        return $this;
    }

    public function subAggregation(string $name, ElasticSearchAggregationInterface $subAggregation)
    {
        $this->subAggregations[$name] = $subAggregation;

        return $this;
    }

    public function meta(array $metaData)
    {
        $this->meta = $metaData;

        return $this;
    }

    public function size(int $size): ElasticSearchAggregationBucketTerms
    {
        $this->size = $size;

        return $this;
    }

    public function requiredSize(int $requiredSize)
    {
        $this->requiredSize = $requiredSize;

        return $this;
    }

    public function shardSize(int $shardSize)
    {
        $this->shardSize = $shardSize;

        return $this;
    }

    public function minDocCount(int $minDocCount)
    {
        $this->minDocCount = $minDocCount;

        return $this;
    }

    public function shardMinDocCount(int $shardMinDocCount)
    {
        $this->minDocCount = $shardMinDocCount;

        return $this;
    }

    public function includePattern(string $regexp)
    {
        $this->includePattern = $regexp;

        return $this;
    }

    public function includeWithFlags(string $regexp, int $flags)
    {
        $this->includePattern = $regexp;
        $this->includeFlags = $flags;

        return $this;
    }

    public function exclude(string $regexp)
    {
        $this->excludePattern = $regexp;

        return $this;
    }

    public function ExcludeWithFlags(string $regexp, int $flags)
    {
        $this->excludePattern = $regexp;
        $this->excludeFlags = $flags;

        return $this;
    }

    // ValueType can be string, long, or double.
    public function valueType(string $valueType)
    {
        $this->valueType = $valueType;

        return $this;
    }

    public function order(string $order, bool $asc)
    {
        $this->order = $order;
        $this->orderAsc = $asc;

        return $this;
    }

    public function orderByCount(bool $asc)
    {
        // "order" : { "_count" : "asc" }
        $this->order = '_count';
        $this->orderAsc = $asc;

        return $this;
    }

    public function orderByCountAsc()
    {
        return $this->orderByCount(true);
    }

    public function orderByCountDesc()
    {
        return $this->orderByCount(false);
    }

    public function orderByTerm(bool $asc)
    {
        // "order" : { "_term" : "asc" }
        $this->order = '_term';
        $this->orderAsc = $asc;

        return $this;
    }

    public function orderByTermAsc()
    {
        return $this->orderByTerm(true);
    }

    public function orderByTermDesc()
    {
        return $this->orderByTerm(false);
    }

    // OrderByAggregation creates a bucket ordering strategy which sorts buckets
    // based on a single-valued calc get.
    public function orderByAggregation(string $aggName, bool $asc)
    {
        // {
        //     "aggs" : {
        //         "genders" : {
        //             "terms" : {
        //                 "field" : "gender",
        //                 "order" : { "avg_height" : "desc" }
        //             },
        //             "aggs" : {
        //                 "avg_height" : { "avg" : { "field" : "height" } }
        //             }
        //         }
        //     }
        // }
        $this->order = $aggName;
        $this->orderAsc = $asc;

        return $this;
    }

    // OrderByAggregationAndMetric creates a bucket ordering strategy which
    // sorts buckets based on a multi-valued calc get.
    public function orderByAggregationAndMetric($aggName, string $metric, bool $asc)
    {
        // {
        //     "aggs" : {
        //         "genders" : {
        //             "terms" : {
        //                 "field" : "gender",
        //                 "order" : { "height_stats.avg" : "desc" }
        //             },
        //             "aggs" : {
        //                 "height_stats" : { "stats" : { "field" : "height" } }
        //             }
        //         }
        //     }
        // }
        $this->order = $aggName . '.' . $metric;
        $this->orderAsc = $asc;

        return $this;
    }


    // Collection mode can be depth_first or breadth_first as of 1.4.0.
    public function collectionMode(string $collectionMode)
    {
        $this->collectionMode = $collectionMode;

        return $this;
    }

    public function showTermDocCountError(bool $showTermDocCountError)
    {
        $this->showTermDocCountError = $showTermDocCountError;

        return $this;
    }

    public function includeTerms(string ...$terms)
    {
        $this->includeTerms = array_merge($this->includeTerms, $terms);

        return $this;
    }

    public function excludeTerms(string ...$terms)
    {
        $this->excludeTerms = array_merge($this->excludeTerms, $terms);

        return $this;
    }

    public function script(ElasticSearchScript $script)
    {
        $this->script = $script;

        return $this;
    }

    public function jsonSerialize()
    {
        $opts = [];

        if ('' !== $this->field) {
            $opts['field'] = $this->field;
        }

        if ('' !== $this->script) {
            $opts['script'] = $this->script;
        }

        if (null !== $this->size && 0 <= $this->size) {
            $opts['size'] = $this->size;
        }

        if ($this->shardSize !== null && $this->shardSize >= 0) {
            $opts['shard_size'] = $this->shardSize;
        }

        if ($this->requiredSize !== null && $this->requiredSize >= 0) {
            $opts['required_size'] = $this->requiredSize;
        }

        if ($this->minDocCount !== null && $this->minDocCount >= 0) {
            $opts['min_doc_count'] = $this->minDocCount;
        }

        if ($this->shardMinDocCount !== null && $this->shardMinDocCount >= 0) {
            $opts['shard_min_doc_count'] = $this->shardMinDocCount;
        }

        if ($this->showTermDocCountError !== null) {
            $opts['show_term_doc_count_error'] = $this->showTermDocCountError;
        }

        if ('' !== $this->collectionMode) {
            $opts['collect_mode'] = $this->collectionMode;
        }

        if ('' !== $this->valueType) {
            $opts['value_type'] = $this->valueType;
        }

        if ('' !== $this->order) {
            if (true === $this->orderAsc) {
                $opts['order'][$this->order] = ElasticSearchSortInterface::ASC;
            }else{
                $opts['order'][$this->order] = ElasticSearchSortInterface::DESC;
            }
        }

        if ([] !== $this->includeTerms) {
            $opts['include'] = $this->includeTerms;
        }

        if ('' !== $this->includePattern) {
            if (null === $this->includeFlags || 0 === $this->includeFlags) {
                $opts['include'] = $this->includePattern;
            } else {
                $opts['include'] = ['pattern' => $this->includePattern, 'flags' => $this->includeFlags];
            }
        }


        if ([] !== $this->excludeTerms) {
            $opts['exclude'] = $this->excludeTerms;
        }

        if ('' !== $this->excludePattern) {
            if (null === $this->excludePattern || 0 === $this->excludePattern) {
                $opts['exclude'] = $this->excludePattern;
            } else {
                $opts['exclude'] = ['pattern' => $this->excludePattern, 'flags' => $this->excludeFlags];
            }
        }

        if ('' !== $this->executionHint) {
            $opts['execution_hint'] = $this->executionHint;
        }

        $result['terms'] = $opts;

        if (0 !== \count($this->subAggregations)) {
            $result['aggregations'] = array_map(
                function (ElasticSearchAggregationInterface $searchAggregation) {
                    return $searchAggregation->jsonSerialize();
                },
                $this->subAggregations
            );
        }

        if (0 !== \count($this->meta)) {
            $result['meta'] = $this->meta;
        }

        return $result;
    }
}
