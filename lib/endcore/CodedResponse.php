<?php
/**
 * CodedResponse exposes the protected $code property from Apaapi's Response
 * via a typed accessor. Replaces fragile ReflectionClass-based extraction.
 */

namespace Endcore;

class CodedResponse extends \Apaapi\lib\Response
{
    public function getStatusCode(): int
    {
        return (int) $this->code;
    }
}
