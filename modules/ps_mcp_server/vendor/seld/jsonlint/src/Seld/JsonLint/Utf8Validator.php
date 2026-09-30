<?php



namespace Seld\JsonLint;


class Utf8Validator
{
    
    const CONTINUATION_OCTET_MINIMUM = 128;

    
    const CONTINUATION_OCTET_MAXIMUM = 191;

    
    public static function validate($input)
    {
        
        
        $useFastPath = PHP_VERSION_ID >= 50400;
        if ($useFastPath) {
            if (function_exists("mb_check_encoding")) {
                if (mb_check_encoding($input, 'UTF-8')) {
                    return;
                }
            } else {
                if (preg_match('//u', $input) === 1) {
                    return;
                }
            }
        }

        $currentOctet = null;
        $continuationOctetNeeded = 0;
        $offsetInOctetsFromStringStart = 0;
        $offsetInCharactersFromStringStart = -1;
        $characterStartPositionFromStringStart = 0;
        $currentLineNumber = 1;
        $offsetInOctetsFromLineStart = -1;
        $offsetInCharactersFromLineStart = -1;
        $characterStartPositionFromLineStart = -1;

        
        
        $currentContinuationOctetMinimum = self::CONTINUATION_OCTET_MINIMUM;
        $currentContinuationOctetMaximum = self::CONTINUATION_OCTET_MAXIMUM;
        $messageForContinuationOctetAboveMaximum = null;

        for ($i = 0, $max = strlen($input); $i < $max; ++$i) {
            $currentOctet = ord($input[$i]);
            $offsetInOctetsFromStringStart = $i;
            ++$offsetInOctetsFromLineStart;

            
            if (
                $currentOctet === 192
                || $currentOctet === 193
                || $currentOctet === 245
                || $currentOctet === 255
            ) {
                throw self::createException(
                    " which is one of the four forbidden values (C0, C1, F5, FF).",
                    (string) $currentOctet,
                    array(
                        'current_octet' => $currentOctet,
                        'continuation_octet_needed' => $continuationOctetNeeded,
                        'offset_in_octets_from_string_start' => $offsetInOctetsFromStringStart,
                        'offset_in_characters_from_string_start' => $offsetInCharactersFromStringStart,
                        'character_start_position_from_string_start' => $characterStartPositionFromStringStart,
                        'line' => $currentLineNumber,
                        'offset_in_octets_from_line_start' => $offsetInOctetsFromLineStart,
                        'offset_in_characters_from_line_start' => $offsetInCharactersFromLineStart,
                        'character_start_position_from_line_start' => $characterStartPositionFromLineStart,
                        'current_continuation_octet_minimum' => $currentContinuationOctetMinimum,
                        'current_continuation_octet_maximum' => $currentContinuationOctetMaximum,
                    )
                );
            }

            
            if ($continuationOctetNeeded > 0) {
                if (
                    $currentOctet < $currentContinuationOctetMinimum
                    || $currentOctet > $currentContinuationOctetMaximum
                ) {
                    $reason =
                        $messageForContinuationOctetAboveMaximum !== null
                        && $currentOctet > $currentContinuationOctetMaximum
                        ? $messageForContinuationOctetAboveMaximum
                        : " which is not a continuation octet.";
                    throw self::createException(
                        $reason,
                        (string) $currentOctet,
                        array(
                            'current_octet' => $currentOctet,
                            'continuation_octet_needed' => $continuationOctetNeeded,
                            'offset_in_octets_from_string_start' => $offsetInOctetsFromStringStart,
                            'offset_in_characters_from_string_start' => $offsetInCharactersFromStringStart,
                            'character_start_position_from_string_start' => $characterStartPositionFromStringStart,
                            'line' => $currentLineNumber,
                            'offset_in_octets_from_line_start' => $offsetInOctetsFromLineStart,
                            'offset_in_characters_from_line_start' => $offsetInCharactersFromLineStart,
                            'character_start_position_from_line_start' => $characterStartPositionFromLineStart,
                            'current_continuation_octet_minimum' => $currentContinuationOctetMinimum,
                            'current_continuation_octet_maximum' => $currentContinuationOctetMaximum,
                        )
                    );
                }
                --$continuationOctetNeeded;
                $currentContinuationOctetMinimum = self::CONTINUATION_OCTET_MINIMUM;
                $currentContinuationOctetMaximum = self::CONTINUATION_OCTET_MAXIMUM;
                $messageForContinuationOctetAboveMaximum = null;
                continue;
            }

            ++$offsetInCharactersFromStringStart;
            ++$offsetInCharactersFromLineStart;
            $characterStartPositionFromStringStart = $offsetInOctetsFromStringStart;
            $characterStartPositionFromLineStart = $offsetInOctetsFromLineStart;

            if ($currentOctet < 128) { 
                
                if ($currentOctet === 10) {
                    ++$currentLineNumber;
                    $offsetInOctetsFromLineStart = -1;
                    $offsetInCharactersFromLineStart = -1;
                }
                continue;
            }

            if ($currentOctet < 192) {
                throw self::createException(
                    " which is a continuation octet.",
                    (string) $currentOctet,
                    array(
                        'current_octet' => $currentOctet,
                        'continuation_octet_needed' => $continuationOctetNeeded,
                        'offset_in_octets_from_string_start' => $offsetInOctetsFromStringStart,
                        'offset_in_characters_from_string_start' => $offsetInCharactersFromStringStart,
                        'character_start_position_from_string_start' => $characterStartPositionFromStringStart,
                        'line' => $currentLineNumber,
                        'offset_in_octets_from_line_start' => $offsetInOctetsFromLineStart,
                        'offset_in_characters_from_line_start' => $offsetInCharactersFromLineStart,
                        'character_start_position_from_line_start' => $characterStartPositionFromLineStart,
                        'current_continuation_octet_minimum' => $currentContinuationOctetMinimum,
                        'current_continuation_octet_maximum' => $currentContinuationOctetMaximum,
                    )
                );
            }

            if ($currentOctet < 224) {
                
                $continuationOctetNeeded = 1;
                continue;
            }
            if ($currentOctet < 240) {
                
                $continuationOctetNeeded = 2;
                
                if ($currentOctet === 224) {
                    $currentContinuationOctetMinimum = 160;
                    $currentContinuationOctetMaximum = 191;  
                }
                if ($currentOctet === 237) {
                    $currentContinuationOctetMinimum = 128;  
                    $currentContinuationOctetMaximum = 159;
                    $messageForContinuationOctetAboveMaximum =
                        " which is into the forbidden range of surrogate pairs.";
                }
                continue;
            }
            if ($currentOctet <  245) {
                
                $continuationOctetNeeded = 3;
                
                if ($currentOctet === 240) {
                    $currentContinuationOctetMinimum = 144;
                    $currentContinuationOctetMaximum = 191;  
                }
                if ($currentOctet === 244) {
                    $currentContinuationOctetMinimum = 128;  
                    $currentContinuationOctetMaximum = 143;
                }
                continue;
            }
            throw self::createException(
                " which is invalid.",
                (string) $currentOctet,
                array(
                    'current_octet' => $currentOctet,
                    'continuation_octet_needed' => $continuationOctetNeeded,
                    'offset_in_octets_from_string_start' => $offsetInOctetsFromStringStart,
                    'offset_in_characters_from_string_start' => $offsetInCharactersFromStringStart,
                    'character_start_position_from_string_start' => $characterStartPositionFromStringStart,
                    'line' => $currentLineNumber,
                    'offset_in_octets_from_line_start' => $offsetInOctetsFromLineStart,
                    'offset_in_characters_from_line_start' => $offsetInCharactersFromLineStart,
                    'character_start_position_from_line_start' => $characterStartPositionFromLineStart,
                    'current_continuation_octet_minimum' => $currentContinuationOctetMinimum,
                    'current_continuation_octet_maximum' => $currentContinuationOctetMaximum,
                )
            );
        }
        if ($continuationOctetNeeded > 0) {
            throw self::createException(
                "",
                "0",
                array(
                    'current_octet' => $currentOctet,
                    'continuation_octet_needed' => $continuationOctetNeeded,
                    'offset_in_octets_from_string_start' => $offsetInOctetsFromStringStart,
                    'offset_in_characters_from_string_start' => $offsetInCharactersFromStringStart,
                    'character_start_position_from_string_start' => $characterStartPositionFromStringStart,
                    'line' => $currentLineNumber,
                    'offset_in_octets_from_line_start' => $offsetInOctetsFromLineStart,
                    'offset_in_characters_from_line_start' => $offsetInCharactersFromLineStart,
                    'character_start_position_from_line_start' => $characterStartPositionFromLineStart,
                    'current_continuation_octet_minimum' => $currentContinuationOctetMinimum,
                    'current_continuation_octet_maximum' => $currentContinuationOctetMaximum,
                ),
                true
            );
        }
        
        
        
        
        if ($useFastPath) {
            throw new InvalidEncodingException(
                "Fast-path detected an error that the manual scan could not find. "
                ."Please report it at https://github.com/Seldaek/jsonlint/issues.",
                "0",
                array(
                    'current_octet' => $currentOctet,
                    'continuation_octet_needed' => $continuationOctetNeeded,
                    'offset_in_octets_from_string_start' => $offsetInOctetsFromStringStart,
                    'offset_in_characters_from_string_start' => $offsetInCharactersFromStringStart,
                    'character_start_position_from_string_start' => $characterStartPositionFromStringStart,
                    'line' => $currentLineNumber,
                    'offset_in_octets_from_line_start' => $offsetInOctetsFromLineStart,
                    'offset_in_characters_from_line_start' => $offsetInCharactersFromLineStart,
                    'character_start_position_from_line_start' => $characterStartPositionFromLineStart,
                    'current_continuation_octet_minimum' => $currentContinuationOctetMinimum,
                    'current_continuation_octet_maximum' => $currentContinuationOctetMaximum,
                )
            );
        }
    }

    
    private static function createException($reason, $key, array $details, $endOfInput = false)
    {
        if ($endOfInput) {
            $middle =
                "; at octet "
                .($details['offset_in_octets_from_line_start'] + 1)
                .", part of the character "
                .($details['offset_in_characters_from_line_start'] + 1)
                .", end of string was found instead of a continuation octet.";
        } else {
            $middle =
                "; the octet "
                .($details['offset_in_octets_from_line_start'] + 1)
                .", part of the character "
                .($details['offset_in_characters_from_line_start'] + 1)
                .", has value "
                .$details['current_octet']
                .$reason;
        }

        $message =
            "Non-UTF8 character found on line "
            .$details['line']
            .$middle
            ." This character starts at octet "
            .($details['character_start_position_from_line_start'] + 1)
            ." of the current line."
            ." (Sequential positions without line splitting:"
            ." This is at character "
            .($details['offset_in_characters_from_string_start'] + 1)
            ." and octet "
            .($details['offset_in_octets_from_string_start'] + 1)
            ."."
            ." This character starts at octet "
            .($details['character_start_position_from_string_start'] + 1)
            .".)";

        return new InvalidEncodingException($message, $key, $details);
    }
}
