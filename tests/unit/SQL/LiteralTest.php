<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class LiteralTest extends \PHPUnit_Framework_TestCase
{
       /**
        * @covers  SQL\Literal::__construct
        */
       public function test_constructor()
       {
               $value = new \stdClass;
               $literal = new Literal($value);

               $this->assertSame($value, $literal->value);
       }
}
