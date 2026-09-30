<?php



if (\PHP_VERSION_ID < 80400) {
    enum RoundingMode
    {
        case HalfAwayFromZero;
        case HalfTowardsZero;
        case HalfEven;
        case HalfOdd;
        case TowardsZero;
        case AwayFromZero;
        case NegativeInfinity;
        case PositiveInfinity;
    }
}
