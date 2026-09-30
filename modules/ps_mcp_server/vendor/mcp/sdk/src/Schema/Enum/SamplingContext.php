<?php



namespace Mcp\Schema\Enum;

enum SamplingContext: string
{
    case NONE = 'none';
    case THIS_SERVER = 'thisServer';
    case ALL_SERVERS = 'allServers';
}
