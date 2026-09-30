<?php

namespace PsMcpServerDeps\Mcp\Schema\Enum;

enum Role: string
{
    case User = 'user';
    case Assistant = 'assistant';
}
