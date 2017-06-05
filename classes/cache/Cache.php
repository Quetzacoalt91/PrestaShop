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

use Psr\Cache\CacheItemPoolInterface;

abstract class CacheCore implements CacheItemPoolInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    protected static $instance;

    /**
     * @var array List of blacklisted tables for SQL cache, these tables won't be indexed
     */
    protected $blacklist = array(
        'cart',
        'cart_cart_rule',
        'cart_product',
        'connections',
        'connections_source',
        'connections_page',
        'customer',
        'customer_group',
        'customized_data',
        'guest',
        'pagenotfound',
        'page_viewed',
        'employee',
    );


    /**
     * @return CacheItemPoolInterface
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Cache\Adapter\PHPArray\ArrayCachePool();
        }
        return self::$instance;
    }

    /**
     * Unit testing purpose only
     * @param $test_instance Cache
     */
    public static function setInstanceForTesting($test_instance)
    {
        self::$instance = $test_instance;
    }

    /**
     * Unit testing purpose only
     */
    public static function deleteTestingInstance()
    {
        self::$instance = null;
    }

    public static function store($key, $value)
    {
        $item = self::getInstance()->getItem($key)->set($value);
        self::getInstance()->save($item);
    }

    public static function retrieve($key)
    {
        return self::getInstance()->hasItem($key) ? self::getInstance()->getItem($key)->get() : null;
    }

    public static function retrieveAll()
    {
        return self::getInstance()->getItems();
    }

    public static function isStored($key)
    {
        return self::getInstance()->hasItem($key);
    }

    public static function clean($key)
    {
        if (is_array($key)) {
            self::getInstance()->deleteItems($key);
        } else {
            self::getInstance()->deleteItem($key);
        }
    }
}
