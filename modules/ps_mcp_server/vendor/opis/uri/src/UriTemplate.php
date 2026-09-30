<?php


namespace Opis\Uri;

use Opis\String\UnicodeString;

class UriTemplate
{
    
    protected const TEMPLATE_VARSPEC_REGEX = '~^(?<varname>[a-zA-Z0-9\_\%\.]+)(?:(?<explode>\*)?|\:(?<prefix>\d+))?$~';

    
    protected const TEMPLATE_REGEX = <<<'REGEX'
~\{
(?<operator>[+#./;&=,!@|\?])?
(?<varlist>
  (?:(?P>varspec),)*
  (?<varspec>(?:
    [a-zA-Z0-9\_\%\.]+
    (?:\*|\:\d+)?
  ))
)
\}~x
REGEX;

    
    protected const TEMPLATE_TABLE = [
        '' => [
            'first' => '',
            'sep' => ',',
            'named' => false,
            'ifemp' => '',
            'allow' => false,
        ],
        '+' => [
            'first' => '',
            'sep' => ',',
            'named' => false,
            'ifemp' => '',
            'allow' => true,
        ],
        '.' => [
            'first' => '.',
            'sep' => '.',
            'named' => false,
            'ifemp' => '',
            'allow' => false,
        ],
        '/' => [
            'first' => '/',
            'sep' => '/',
            'named' => false,
            'ifemp' => '',
            'allow' => false,
        ],
        ';' => [
            'first' => ';',
            'sep' => ';',
            'named' => true,
            'ifemp' => '',
            'allow' => false,
        ],
        '?' => [
            'first' => '?',
            'sep' => '&',
            'named' => true,
            'ifemp' => '=',
            'allow' => false,
        ],
        '&' => [
            'first' => '&',
            'sep' => '&',
            'named' => true,
            'ifemp' => '=',
            'allow' => false,
        ],
        '#' => [
            'first' => '#',
            'sep' => ',',
            'named' => false,
            'ifemp' => '',
            'allow' => true,
        ],
    ];

    protected string $uri;

    
    protected $parsed = false;

    
    public function __construct(string $uri_template)
    {
        $this->uri = $uri_template;
    }

    
    public function resolve(array $vars): string
    {
        if ($this->parsed === false) {
            $this->parsed = $this->parse($this->uri);
        }
        if ($this->parsed === null || !$vars) {
            return $this->uri;
        }

        $data = '';
        $vars = $this->prepareVars($vars);

        foreach ($this->parsed as $item) {
            if (!is_array($item)) {
                $data .= $item;
                continue;
            }

            $data .= $this->parseTemplateExpression(
                self::TEMPLATE_TABLE[$item['operator']],
                $this->resolveVars($item['vars'], $vars)
            );
        }

        return $data;
    }

    
    public function hasPlaceholders(): bool
    {
        if ($this->parsed === false) {
            $this->parse($this->uri);
        }

        return $this->parsed !== null;
    }

    
    protected function parse(string $uri): ?array
    {
        $placeholders = null;
        preg_match_all(self::TEMPLATE_REGEX, $uri, $placeholders, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (!$placeholders) {
            return null;
        }

        $dataIndex = -1;
        $data = [];

        $hasVars = false;
        $nextOffset = 0;
        foreach ($placeholders as &$p) {
            $offset = $p[0][1];
            if ($nextOffset < $offset) {
                $data[] = substr($uri, $nextOffset, $offset - $nextOffset);
                $dataIndex++;
            }
            $matched = $p[0][0];
            $nextOffset = $offset + strlen($matched);

            $operator = $p['operator'][0] ?? null;
            if ($operator === null || !isset(self::TEMPLATE_TABLE[$operator])) {
                if ($dataIndex >= 0 && is_string($data[$dataIndex])) {
                    $data[$dataIndex] .= $matched;
                } else {
                    $data[] = $matched;
                    $dataIndex++;
                }
                continue;
            }

            $varList = $p['varlist'][0] ?? '';
            $varList = $varList === '' ? [] : explode(',', $varList);
            $p = null;

            $varData = [];

            foreach ($varList as $var) {
                if (!preg_match(self::TEMPLATE_VARSPEC_REGEX, $var, $spec)) {
                    continue;
                }

                $varData[] = [
                    'name' => $spec['varname'],
                    'explode' => isset($spec['explode']) && $spec['explode'] === '*',
                    'prefix' => isset($spec['prefix']) ? (int)$spec['prefix'] : 0,
                ];

                unset($var, $spec);
            }

            if ($varData) {
                $hasVars = true;
                $data[] = [
                    'operator' => $operator,
                    'vars' => $varData,
                ];
                $dataIndex++;
            } else {
                if ($dataIndex >= 0 && is_string($data[$dataIndex])) {
                    $data[$dataIndex] .= $matched;
                } else {
                    $data[] = $matched;
                    $dataIndex++;
                }
            }

            unset($varData, $varList, $operator);
        }

        if (!$hasVars) {
            return null;
        }

        $matched = substr($uri, $nextOffset);
        if ($matched !== false && $matched !== '') {
            if ($dataIndex >= 0 && is_string($data[$dataIndex])) {
                $data[$dataIndex] .= $matched;
            } else {
                $data[] = $matched;
            }
        }

        return $data;
    }

    
    protected function prepareVars(array $vars): array
    {
        foreach ($vars as &$value) {
            if (is_scalar($value)) {
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                continue;
            }

            if (!is_array($value)) {
                continue;
            }

            $len = count($value);
            for ($i = 0; $i < $len; $i++) {
                if (!array_key_exists($i, $value)) {
                    $value = (object)$value;
                    break;
                }
            }
        }

        return $vars;
    }

    
    protected function resolveVars(array $vars, array $data): array
    {
        $resolved = [];

        foreach ($vars as $info) {
            $name = $info['name'];

            if (!isset($data[$name])) {
                continue;
            }

            $resolved[] = $info + ['value' => &$data[$name]];
        }

        return $resolved;
    }

    
    protected function parseTemplateExpression(array $table, array $data): string
    {
        $result = [];
        foreach ($data as $var) {
            $str = "";
            if (is_string($var['value'])) {
                if ($table['named']) {
                    $str .= $var['name'];
                    if ($var['value'] === '') {
                        $str .= $table['ifemp'];
                    } else {
                        $str .= '=';
                    }
                }
                if ($var['prefix']) {
                    $str .= $this->encodeTemplateString(self::prefix($var['value'], $var['prefix']), $table['allow']);
                } else {
                    $str .= $this->encodeTemplateString($var['value'], $table['allow']);
                }
            } elseif ($var['explode']) {
                $list = [];
                if ($table['named']) {
                    if (is_array($var['value'])) {
                        foreach ($var['value'] as $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $v = $this->encodeTemplateString((string)$v, $table['allow']);
                            if ($v === '') {
                                $list[] = $var['name'] . $table['ifemp'];
                            } else {
                                $list[] = $var['name'] . '=' . $v;
                            }
                        }
                    } elseif (is_object($var['value'])) {
                        foreach ($var['value'] as $prop => $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $v = $this->encodeTemplateString((string)$v, $table['allow']);
                            $prop = $this->encodeTemplateString((string)$prop, $table['allow']);
                            if ($v === '') {
                                $list[] = $prop . $table['ifemp'];
                            } else {
                                $list[] = $prop . '=' . $v;
                            }
                        }
                    }
                } else {
                    if (is_array($var['value'])) {
                        foreach ($var['value'] as $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $list[] = $this->encodeTemplateString($v, $table['allow']);
                        }
                    } elseif (is_object($var['value'])) {
                        foreach ($var['value'] as $prop => $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $v = $this->encodeTemplateString((string)$v, $table['allow']);
                            $prop = $this->encodeTemplateString((string)$prop, $table['allow']);
                            $list[] = $prop . '=' . $v;
                        }
                    }
                }

                if ($list) {
                    $str .= implode($table['sep'], $list);
                }
                unset($list);
            } else {
                if ($table['named']) {
                    $str .= $var['name'];
                    if ($var['value'] === '') {
                        $str .= $table['ifemp'];
                    } else {
                        $str .= '=';
                    }
                }
                $list = [];
                if (is_array($var['value'])) {
                    foreach ($var['value'] as $v) {
                        $list[] = $this->encodeTemplateString($v, $table['allow']);
                    }
                } elseif (is_object($var['value'])) {
                    foreach ($var['value'] as $prop => $v) {
                        $list[] = $this->encodeTemplateString((string)$prop, $table['allow']);
                        $list[] = $this->encodeTemplateString((string)$v, $table['allow']);
                    }
                }
                if ($list) {
                    $str .= implode(',', $list);
                }
                unset($list);
            }

            if ($str !== '') {
                $result[] = $str;
            }
        }

        if (!$result) {
            return '';
        }

        $result = implode($table['sep'], $result);

        if ($result !== '') {
            $result = $table['first'] . $result;
        }

        return $result;
    }

    
    protected function encodeTemplateString(string $data, bool $reserved): string
    {
        $skip = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~';

        if ($reserved) {
            $skip .= ':/?#[]@!$&\'()*+,;=';
        }

        $result = '';
        $temp = '';
        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            if (strpos($skip, $data[$i]) !== false) {
                if ($temp !== '') {
                    $result .= Uri::encodeComponent($temp);
                    $temp = '';
                }
                $result .= $data[$i];
                continue;
            }
            if ($reserved && $data[$i] === '%') {
                if (isset($data[$i + 1]) && isset($data[$i + 2])
                    && strpos('ABCDEF0123456789', $data[$i + 1]) !== false
                    && strpos('ABCDEF0123456789', $data[$i + 2]) !== false) {
                    if ($temp !== '') {
                        $result .= Uri::encodeComponent($temp);
                    }
                    $result .= '%' . $data[$i + 1] . $data[$i + 2];
                    $i += 3;
                    continue;
                }
            }
            $temp .= $data[$i];
        }

        if ($temp !== '') {
            $result .= Uri::encodeComponent($temp);
        }

        return $result;
    }

    
    public function value(): string
    {
        return $this->uri;
    }

    public function __toString(): string
    {
        return $this->uri;
    }

    
    public static function isTemplate(string $uri): bool
    {
        $open = substr_count($uri, '{');
        if ($open === 0) {
            return false;
        }
        $close = substr_count($uri, '}');
        if ($open !== $close) {
            return false;
        }

        return (bool)preg_match(self::TEMPLATE_REGEX, $uri);
    }

    
    protected static function prefix(string $str, int $len): string
    {
        if ($len === 0) {
            return '';
        }

        if ($len >= strlen($str)) {
            
            return $str;
        }

        return (string)UnicodeString::from($str)->substring(0, $len);
    }
}