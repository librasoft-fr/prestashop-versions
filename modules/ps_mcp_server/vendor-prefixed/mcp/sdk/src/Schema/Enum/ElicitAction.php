<?php

namespace PsMcpServerDeps\Mcp\Schema\Enum;

enum ElicitAction: string
{
    case Accept = 'accept';
    case Decline = 'decline';
    case Cancel = 'cancel';
}
