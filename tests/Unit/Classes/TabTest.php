<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace Tests\Unit\Classes;

use Tests\TestCase\UnitTestCase;
use Tab;

class FakeTabCore extends Tab
{
    /**
     * Array of sample data defining what is the current position of some Tabs (= Admin side menu links)
     * @var array
     */
    public static $tabsToBeTested = array(
        'AdminModules' => '1',
        'AdminOrders' => '0',
        'AdminParentRequestSql' => '6',
    );

    /**
     * Overriding function which is requesting the database.
     * Data is taken from the current answers returned by the function.
     *
     * @param string $className
     * @return string|boolean
     */
    public static function getPositionByClassName($className)
    {
        if (array_key_exists($className, self::$tabsToBeTested)) {
            return self::$tabsToBeTested[$className];
        }
        return false;
    }
}

class TabTest extends UnitTestCase
{
    /**
     * @dataProvider positionBeforeClassProvider
     */
    public function testPositionBeforeClass($expected, $input)
    {
        $result = FakeTabCore::getPositionBeforeClassName($input);
        $this->assertSame($expected, $result, 'Expected Tab::getPositionBeforeClassName('.$input.') to return '. $expected.', got '.$result.' instead.');
    }

    /**
     * @dataProvider positionAfterClassProvider
     */
    public function testPositionAfterClass($expected, $input)
    {
        $result = FakeTabCore::getPositionAfterClassName($input);
        $this->assertSame($expected, $result, 'Expected Tab::getPositionAfterClassName('.$input.') to return '. $expected.', got '.$result.' instead.');
    }


    // --- providers ---

    public function positionBeforeClassProvider()
    {
        return array(
            array(0, 'AdminModules'),
            array(0, 'AdminOrders'),
            array(5, 'AdminParentRequestSql'),
            array(false, 'AdminNull'),
        );
    }

    public function positionAfterClassProvider()
    {
        return array(
            array(2, 'AdminModules'),
            array(1, 'AdminOrders'),
            array(7, 'AdminParentRequestSql'),
            array(false, 'AdminNull'),
        );
    }
}
