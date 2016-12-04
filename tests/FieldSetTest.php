<?php

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Tests;

use Rollerworks\Component\Search\FieldSet;

final class FieldSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_gets_a_field()
    {
        $fieldSet = new FieldSet([
            'id' => $idField = $this->createFieldMock('id'),
            'name' => $nameField = $this->createFieldMock('name'),
        ]);

        $this->assertSame($idField, $fieldSet->get('id'));
        $this->assertSame($nameField, $fieldSet->get('name'));

        $this->assertTrue($fieldSet->has('id'));
        $this->assertTrue($fieldSet->has('name'));
        $this->assertFalse($fieldSet->has('foo'));
    }

    /**
     * @param string $name
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createFieldMock(string $name)
    {
        $field = $this->createMock('Rollerworks\Component\Search\FieldConfigInterface');
        $field->expects(self::any())->method('getName')->willReturn($name);

        return $field;
    }
}