<?php

namespace PsMcpServerDeps\Opis\JsonSchema;

interface ContentMediaType
{
    public function validate(string $content, string $media_type): bool;
}
