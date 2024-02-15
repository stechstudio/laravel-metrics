<?php

namespace STS\Metrics;

enum MetricType {
    case COUNTER;
    case SUMMARY;
    case GAUGE;
    case HISTOGRAM;
}