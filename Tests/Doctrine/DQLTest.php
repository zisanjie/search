<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\RecordFilterBundle\Tests\Doctrine;

use Rollerworks\Bundle\RecordFilterBundle\Type\DateTimeExtended;
use Rollerworks\Bundle\RecordFilterBundle\Doctrine\Orm\WhereBuilder;
use Rollerworks\Bundle\RecordFilterBundle\Mapping\Loader\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Metadata\MetadataFactory;

class DQLTest extends OrmTestCase
{
    /**
     * @dataProvider provideBasicsTests
     *
     * @param string $filterQuery
     * @param string $expectedDql
     * @param array  $params
     */
    public function testBasics($filterQuery, $expectedDql, $params)
    {
        $input = $this->newInput($filterQuery);
        $this->assertTrue($this->formatter->formatInput($input));

        $container       = $this->createContainer();
        $metadataFactory = new MetadataFactory(new AnnotationDriver($this->newAnnotationsReader()));
        $whereBuilder    = new WhereBuilder($metadataFactory, $container, $this->em);

        $query = $this->em->createQuery("SELECT I FROM Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\BaseBundle\Entity\ECommerce\ECommerceInvoice I WHERE ");

        $whereCase = $this->cleanSql($whereBuilder->getWhereClause(
            $this->formatter,
            array('Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\BaseBundle\Entity\ECommerce\ECommerceInvoice' => 'I'),
            $query
        ));

        $this->assertEquals($expectedDql, $whereCase);

        // Parameter handling changed in Doctrine ORM 2.3
        if (version_compare(\Doctrine\ORM\Version::VERSION, '2.3.0', '>=')) {
            foreach ($params as $name => $value) {
                $paramVal = $query->getParameter($name);
                $this->assertInstanceOf('Doctrine\ORM\Query\Parameter', $paramVal);
                $this->assertEquals($query->getParameter($name)->getValue(), (is_object($value) ? $value : (string) $value));
            }
        } else {
            // XXX THIS ACTUALLY NEEDS TESTING
            foreach ($params as $name => $value) {
                $paramVal = $query->getParameter($name);
                $this->assertInstanceOf('Doctrine\ORM\Query\Parameter', $paramVal);
                $this->assertEquals($query->getParameter($name)->getValue(), (is_object($value) ? $value : (string) $value));
            }
        }

        if ('' !== $expectedDql) {
            $query->useQueryCache(false);
            $dql = $query->getDQL() . $whereCase;
            $query->setDQL($dql);
            $query->getSQL();
        }
    }

    public function testEmptyResult()
    {
        $input = $this->newInput('no_field=2;');
        $this->assertTrue($this->formatter->formatInput($input));

        $container = $this->createContainer();
        $metadataFactory = new MetadataFactory(new AnnotationDriver($this->newAnnotationsReader()));
        $whereBuilder    = new WhereBuilder($metadataFactory, $container, $this->em);

        $whereCase = $this->cleanSql($whereBuilder->getWhereClause($this->formatter));
        $this->assertNull($whereCase);
    }

    /**
     * @dataProvider provideSqlConvertTests
     *
     * @param string $filterQuery
     * @param string $expectedDql
     * @param array  $params
     */
    public function testValueConvert($filterQuery, $expectedDql, $params)
    {
        $input = $this->newInput($filterQuery, 'customer');
        $this->assertTrue($this->formatter->formatInput($input));

        $container = $this->createContainer();
        $container->set('customer_conversion', new \Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\CustomerConversion());

        $metadataFactory = new MetadataFactory(new AnnotationDriver($this->newAnnotationsReader()));
        $whereBuilder    = new WhereBuilder($metadataFactory, $container, $this->em);

        $query = $this->em->createQuery("SELECT C FROM Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\BaseBundle\Entity\ECommerce\ECommerceCustomer C WHERE ");

        $whereCase = $this->cleanSql($whereBuilder->getWhereClause(
            $this->formatter,
            array('Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\BaseBundle\Entity\ECommerce\ECommerceCustomer' => 'C'),
            $query
        ));

        $this->assertEquals($expectedDql, $whereCase);

        // Parameter handling changed in Doctrine ORM 2.3
        if (version_compare(\Doctrine\ORM\Version::VERSION, '2.3.0', '>=')) {
            foreach ($params as $name => $value) {
                $paramVal = $query->getParameter($name);
                $this->assertInstanceOf('Doctrine\ORM\Query\Parameter', $paramVal);
                $this->assertEquals($query->getParameter($name)->getValue(), (is_object($value) ? $value : (string) $value));
            }
        } else {
            // XXX THIS ACTUALLY NEEDS TESTING
            foreach ($params as $name => $value) {
                $paramVal = $query->getParameter($name);
                $this->assertInstanceOf('Doctrine\ORM\Query\Parameter', $paramVal);
                $this->assertEquals($query->getParameter($name)->getValue(), (is_object($value) ? $value : (string) $value));
            }
        }

        if ('' !== $expectedDql) {
            $query->useQueryCache(false);
            $dql = $query->getDQL() . $whereCase;
            $query->setDQL($dql);
            $query->getSQL();
        }
    }

    /**
     * @dataProvider provideFieldConversionTests
     *
     * @param string $filterQuery
     * @param string $expectedDql
     * @param array  $params
     */
    public function testFieldConvert($filterQuery, $expectedDql, $params)
    {
        $input = $this->newInput($filterQuery, 'invoice');
        $this->assertTrue($this->formatter->formatInput($input));

        $container = $this->createContainer();
        $container->set('customer_conversion', new \Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\CustomerConversion());

        $metadataFactory = new MetadataFactory(new AnnotationDriver($this->newAnnotationsReader()));
        $whereBuilder    = new WhereBuilder($metadataFactory, $container, $this->em);
        $whereBuilder->setFieldConversion('invoice_customer', $container->get('customer_conversion'));

        $query = $this->em->createQuery("SELECT I FROM Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\BaseBundle\Entity\ECommerce\ECommerceInvoice I WHERE ");

        $whereCase = $this->cleanSql($whereBuilder->getWhereClause(
            $this->formatter,
            array('Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures\BaseBundle\Entity\ECommerce\ECommerceInvoice' => 'I'),
            $query
        ));

        $this->assertEquals($expectedDql, $whereCase);

        // Parameter handling changed in Doctrine ORM 2.3
        if (version_compare(\Doctrine\ORM\Version::VERSION, '2.3.0', '>=')) {
            foreach ($params as $name => $value) {
                $paramVal = $query->getParameter($name);
                $this->assertInstanceOf('Doctrine\ORM\Query\Parameter', $paramVal);
                $this->assertEquals($query->getParameter($name)->getValue(), (is_object($value) ? $value : (string) $value));
            }
        } else {
            // XXX THIS ACTUALLY NEEDS TESTING
            foreach ($params as $name => $value) {
                $paramVal = $query->getParameter($name);
                $this->assertInstanceOf('Doctrine\ORM\Query\Parameter', $paramVal);
                $this->assertEquals($query->getParameter($name)->getValue(), (is_object($value) ? $value : (string) $value));
            }
        }

        if ('' !== $expectedDql) {
            $query->useQueryCache(false);
            $dql = $query->getDQL() . $whereCase;
            $query->setDQL($dql);
            $query->getSQL();
        }
    }

    public static function provideBasicsTests()
    {
        return array(
            array('invoice_customer=2;', '(I.customer IN(:invoice_customer_0))', array('invoice_customer_0' => 2)),
            array('invoice_label=F2012-4242;', '(I.label IN(:invoice_label_0))', array('invoice_label_0' => 'F2012-4242')),
            array('invoice_customer=2, 5;', '(I.customer IN(:invoice_customer_0, :invoice_customer_1))', array('invoice_customer_0' => 2, 'invoice_customer_1' => 5)),
            array('invoice_customer=2-5;', '((I.customer BETWEEN :invoice_customer_0 AND :invoice_customer_1))', array('invoice_customer_0' => 2, 'invoice_customer_1' => 5)),
            array('invoice_customer=2-5, 8;', '(I.customer IN(:invoice_customer_0) AND (I.customer BETWEEN :invoice_customer_1 AND :invoice_customer_2))', array('invoice_customer_0' => 8, 'invoice_customer_1' => 2, 'invoice_customer_2' =>5)),
            array('invoice_customer=2-5,!8-10;', '((I.customer BETWEEN :invoice_customer_0 AND :invoice_customer_1) AND (I.customer NOT BETWEEN :invoice_customer_2 AND :invoice_customer_3))', array('invoice_customer_0' => 2, 'invoice_customer_1' => 5, 'invoice_customer_2' => 8, 'invoice_customer_3' => 10)),
            array('invoice_customer=2-5, !8;', '(I.customer NOT IN(:invoice_customer_0) AND (I.customer BETWEEN :invoice_customer_1 AND :invoice_customer_2))', array('invoice_customer_0' => 8, 'invoice_customer_1' => 2, 'invoice_customer_2' => 5)),
            array('invoice_customer=2-5, >8;', '((I.customer BETWEEN :invoice_customer_0 AND :invoice_customer_1) AND I.customer > :invoice_customer_2)', array('invoice_customer_0' => 2, 'invoice_customer_1' => 5, 'invoice_customer_2' => 8)),

            array('(invoice_customer=2;),(invoice_customer=3;)', '(I.customer IN(:invoice_customer_0)) OR (I.customer IN(:invoice_customer_1))', array('invoice_customer_0' => 2, 'invoice_customer_1' => 3)),
            array('(invoice_customer=2,3;),(invoice_customer=3,5;)', '(I.customer IN(:invoice_customer_0, :invoice_customer_1)) OR (I.customer IN(:invoice_customer_2, :invoice_customer_3))', array('invoice_customer_0' => 2, 'invoice_customer_1' => 3, 'invoice_customer_2' => 3, 'invoice_customer_3' => 5)),
            array('(invoice_customer=2,3; invoice_status=Active;),(invoice_customer=3,5;)', '(I.customer IN(:invoice_customer_0, :invoice_customer_1) AND I.status IN(:invoice_status_0)) OR (I.customer IN(:invoice_customer_2, :invoice_customer_3))', array('invoice_customer_0' => 2, 'invoice_customer_1' => 3, 'invoice_customer_2' => 3, 'invoice_customer_3' => 5, 'invoice_status_0' => 1)),
            array('invoice_date=06/13/2012;', '(I.date IN(:invoice_date_0))', array('invoice_date_0' => new DateTimeExtended('2012-06-13'))),

            // Expects empty as there is no field with that name
            array('(user=2;),(user=2;)', '', array()),
        );
    }

    public static function provideFieldConversionTests()
    {
        return array(
            array('invoice_customer=2;', "(RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_0))", array('invoice_customer_0' => 2)),
            array('invoice_label=F2012-4242;', "(I.label IN(:invoice_label_0))", array('invoice_label_0' => 'F2012-4242')),
            array('invoice_customer=2, 5;', "(RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_0, :invoice_customer_1))", array('invoice_customer_0' => 2, 'invoice_customer_1' => 5)),
            array('invoice_customer=2-5;', "((RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) BETWEEN :invoice_customer_0 AND :invoice_customer_1))", array('invoice_customer_0' => 2, 'invoice_customer_1' => 5)),
            array('invoice_customer=2-5, 8;', "(RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_0) AND (RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) BETWEEN :invoice_customer_1 AND :invoice_customer_2))", array('invoice_customer_0' => 8, 'invoice_customer_1' => 2, 'invoice_customer_2' =>5)),
            array('invoice_customer=2-5,!8-10;', "((RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) BETWEEN :invoice_customer_0 AND :invoice_customer_1) AND (RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) NOT BETWEEN :invoice_customer_2 AND :invoice_customer_3))", array('invoice_customer_0' => 2, 'invoice_customer_1' => 5, 'invoice_customer_2' => 8, 'invoice_customer_3' => 10)),
            array('invoice_customer=2-5, !8;', "(RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) NOT IN(:invoice_customer_0) AND (RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) BETWEEN :invoice_customer_1 AND :invoice_customer_2))", array('invoice_customer_0' => 8, 'invoice_customer_1' => 2, 'invoice_customer_2' => 5)),
            array('invoice_customer=2-5, >8;', "((RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) BETWEEN :invoice_customer_0 AND :invoice_customer_1) AND RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) > :invoice_customer_2)", array('invoice_customer_0' => 2, 'invoice_customer_1' => 5, 'invoice_customer_2' => 8)),

            array('(invoice_customer=2;),(invoice_customer=3;)', "(RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_0)) OR (RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_1))", array('invoice_customer_0' => 2, 'invoice_customer_1' => 3)),
            array('(invoice_customer=2,3;),(invoice_customer=3,5;)', "(RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_0, :invoice_customer_1)) OR (RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_2, :invoice_customer_3))", array('invoice_customer_0' => 2, 'invoice_customer_1' => 3, 'invoice_customer_2' => 3, 'invoice_customer_3' => 5)),
            array('(invoice_customer=2,3; invoice_status=Active;),(invoice_customer=3,5;)', "(RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_0, :invoice_customer_1) AND I.status IN(:invoice_status_0)) OR (RECORD_FILTER_FIELD_CONVERSION('invoice_customer', I.customer) IN(:invoice_customer_2, :invoice_customer_3))", array('invoice_customer_0' => 2, 'invoice_customer_1' => 3, 'invoice_customer_2' => 3, 'invoice_customer_3' => 5, 'invoice_status_0' => 1)),
            array('invoice_date=06/13/2012;', "(I.date IN(:invoice_date_0))", array('invoice_date_0' => new DateTimeExtended('2012-06-13'))),

            // Expects empty as there is no field with that name
            array('(user=2;),(user=2;)', '', array()),
        );
    }

    public static function provideSqlConvertTests()
    {
        return array(
            array('customer_id=2;', '(C.id IN(:customer_id_0))', array('customer_id_0' => 2)),
        );
    }
}
