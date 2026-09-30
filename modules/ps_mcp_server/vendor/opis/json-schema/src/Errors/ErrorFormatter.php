<?php


namespace Opis\JsonSchema\Errors;

use Opis\JsonSchema\JsonPointer;

class ErrorFormatter
{
    
    public function format(
        ValidationError $error,
        bool $multiple = true,
        ?callable $formatter = null,
        ?callable $key_formatter = null
    ): array {
        if (!$formatter) {
            $formatter = [$this, 'formatErrorMessage'];
        }

        if (!$key_formatter) {
            $key_formatter = [$this, 'formatErrorKey'];
        }

        $list = [];

        

        foreach ($this->getErrors($error) as $error => $message) {
            $key = $key_formatter($error);

            if ($multiple) {
                if (!isset($list[$key])) {
                    $list[$key] = [];
                }
                $list[$key][] = $formatter($error, $message);
            } else {
                if (!isset($list[$key])) {
                    $list[$key] = $formatter($error, $message);
                }
            }
        }

        return $list;
    }

    
    public function formatOutput(?ValidationError $error, string $mode = "flag"): array
    {
        if ($error === null) {
            return ['valid' => true];
        }

        if ($mode === 'flag') {
            return ['valid' => false];
        }

        if ($mode === 'basic') {
            return [
                'valid' => false,
                'errors' => $this->formatFlat($error, [$this, 'formatOutputError']),
            ];
        }

        if ($mode === 'detailed' || $mode === 'verbose') {
            $isVerbose = $mode === 'verbose';

            return $this->getNestedErrors($error, function (ValidationError $error, ?array $subErrors = null) use ($isVerbose) {
                    $info = $this->formatOutputError($error);

                    $info['valid'] = false;

                    if ($isVerbose) {
                        $id = $error->schema()->info();
                        $id = $id->root() ?? $id->id();
                        if ($id) {
                            $id = rtrim($id, '#');
                        }
                        $info['absoluteKeywordLocation'] = $id . $info['keywordLocation'];
                    }

                    if ($subErrors) {
                        $info['errors'] = $subErrors;
                        if (!$isVerbose) {
                            unset($info['error']);
                        }
                    }

                    return $info;
                }
            );
        }

        return ['valid' => false];
    }

    
    public function formatNested(ValidationError $error, ?callable $formatter = null)
    {
        if (!$formatter) {
            $formatter = function (ValidationError $error, ?array $subErrors = null): array {
                $ret = [
                    'message' => $this->formatErrorMessage($error),
                    'keyword' => $error->keyword(),
                    'path' => $this->formatErrorKey($error),
                ];

                if ($subErrors) {
                    $ret['errors'] = $subErrors;
                }

                return $ret;
            };
        }

        return $this->getNestedErrors($error, $formatter);
    }

    
    public function formatFlat(ValidationError $error, ?callable $formatter = null): array
    {
        if (!$formatter) {
            $formatter = [$this, 'formatErrorMessage'];
        }

        $list = [];

        foreach ($this->getFlatErrors($error) as $error) {
            $list[] = $formatter($error);
        }

        return $list;
    }

    
    public function formatKeyed(
        ValidationError $error,
        ?callable $formatter = null,
        ?callable $key_formatter = null
    ): array {
        if (!$formatter) {
            $formatter = [$this, 'formatErrorMessage'];
        }

        if (!$key_formatter) {
            $key_formatter = [$this, 'formatErrorKey'];
        }

        $list = [];

        foreach ($this->getLeafErrors($error) as $error) {
            $key = $key_formatter($error);

            if (!isset($list[$key])) {
                $list[$key] = [];
            }

            $list[$key][] = $formatter($error);
        }

        return $list;
    }

    
    public function formatErrorMessage(ValidationError $error, ?string $message = null): string
    {
        $message ??= $error->message();
        $args = $this->getDefaultArgs($error) + $error->args();

        if (!$args) {
            return $message;
        }

        return preg_replace_callback(
            '~{([^}]+)}~imu',
            static function (array $m) use ($args) {
                if (!array_key_exists($m[1], $args)) {
                    return $m[0];
                }

                $value = $args[$m[1]];

                if (is_array($value)) {
                    return implode(', ', $value);
                }

                return (string) $value;
            },
            $message
        );
    }

    public function formatErrorKey(ValidationError $error): string
    {
        return JsonPointer::pathToString($error->data()->fullPath());
    }

    protected function getDefaultArgs(ValidationError $error): array
    {
        $data = $error->data();
        $info = $error->schema()->info();

        $path = $info->path();
        $path[] = $error->keyword();

        return [
            'data:type' => $data->type(),
            'data:value' => $data->value(),
            'data:path' => JsonPointer::pathToString($data->fullPath()),

            'schema:id' => $info->id(),
            'schema:root' => $info->root(),
            'schema:base' => $info->base(),
            'schema:draft' => $info->draft(),
            'schema:keyword' => $error->keyword(),
            'schema:path' => JsonPointer::pathToString($path),
        ];
    }

    protected function formatOutputError(ValidationError $error): array
    {
        $path = $error->schema()->info()->path();

        $path[] = $error->keyword();

        return [
            'keywordLocation' => JsonPointer::pathToFragment($path),
            'instanceLocation' => JsonPointer::pathToFragment($error->data()->fullPath()),
            'error' => $this->formatErrorMessage($error),
        ];
    }

    
    protected function getNestedErrors(ValidationError $error, callable $formatter)
    {
        if ($subErrors = $error->subErrors()) {
            foreach ($subErrors as &$subError) {
                $subError = $this->getNestedErrors($subError, $formatter);
                unset($subError);
            }
        }

        return $formatter($error, $subErrors);
    }

    
    protected function getFlatErrors(ValidationError $error): iterable
    {
        yield $error;

        foreach ($error->subErrors() as $subError) {
            yield from $this->getFlatErrors($subError);
        }
    }

    
    protected function getLeafErrors(ValidationError $error): iterable
    {
        if ($subErrors = $error->subErrors()) {
            foreach ($subErrors as $subError) {
                yield from $this->getLeafErrors($subError);
            }
        } else {
            yield $error;
        }
    }

    
    protected function getErrors(ValidationError $error): iterable
    {
        $data = $error->schema()->info()->data();

        $map = null;
        $pMap = null;

        if (is_object($data)) {
            switch ($error->keyword()) {
                case 'required':
                    if (isset($data->{'$error'}->required) && is_object($data->{'$error'}->required)) {
                        $e = $data->{'$error'}->required;
                        $found = false;
                        foreach ($error->args()['missing'] as $prop) {
                            if (isset($e->{$prop})) {
                                yield $error => $e->{$prop};
                                $found = true;
                            }
                        }
                        if ($found) {
                            return;
                        }
                        if (isset($e->{'*'})) {
                            yield $error => $e->{'*'};
                            return;
                        }
                        unset($e, $found, $prop);
                    }
                    break;
                case '$filters':
                    if (($args = $error->args()) && isset($args['args']['$error'])) {
                        yield $error => $args['args']['$error'];
                        return;
                    }
                    unset($args);
                    break;
            }

            if (isset($data->{'$error'})) {
                $map = $data->{'$error'};

                if (is_string($map)) {
                    
                    yield $error => $map;
                    return;
                }

                if (is_object($map)) {
                    if (isset($map->{$error->keyword()})) {
                        $pMap = $map->{'*'} ?? null;
                        $map = $map->{$error->keyword()};
                        if (is_string($map)) {
                            yield $error => $map;
                            return;
                        }
                    } elseif (isset($map->{'*'})) {
                        yield $error => $map->{'*'};
                        return;
                    }
                }
            }
        }

        if (!is_object($map)) {
            $map = null;
        }

        $subErrors = $error->subErrors();

        if (!$subErrors) {
            yield $error => $pMap ?? $error->message();
            return;
        }

        if (!$map) {
            foreach ($subErrors as $subError) {
                yield from $this->getErrors($subError);
            }
            return;
        }

        foreach ($subErrors as $subError) {
            $path = $subError->data()->path();
            if (count($path) !== 1) {
                yield from $this->getErrors($subError);
            } else {
                $path = $path[0];
                if (isset($map->{$path})) {
                    yield $subError => $map->{$path};
                } elseif (isset($map->{'*'})) {
                    yield $subError => $map->{'*'};
                } else {
                    yield from $this->getErrors($subError);
                }
            }
        }
    }
}