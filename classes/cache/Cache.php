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

abstract class CacheCore
{
    /**
     * Name of keys index
     */
    const KEYS_NAME = '__keys__';

    /**
     * Name of SQL cache index
     */
    const SQL_TABLES_NAME = 'tablesCached';

    /**
     * @var Cache
     */
    protected static $instance;

    /**
     * @var array List all keys of cached data and their associated ttl
     */
    protected $keys = array();

    /**
     * @var array Store list of tables and their associated keys for SQL cache (warning: this var must not be initialized here !)
     */
    protected $sql_tables_cached;

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
     * @var array Store local cache
     */
    protected $local = array();

        /**
     * Store a data in cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    abstract public function set($key, $value, $ttl = 0);

    /**
     * Retrieve a data from cache
     *
     * @param string $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * Check if a data is cached
     *
     * @param string $key
     * @return bool
     */
    abstract public function exists($key);

    /**
     * Delete one or several data from cache (* joker can be used)
     * 	E.g.: delete('*'); delete('my_prefix_*'); delete('my_key_name');
     *
     * @param string $key
     * @return array List of deleted keys
     */
    abstract public function delete($key);

    /**
     * Clean all cached data
     *
     * @return bool
     */
    abstract public function flush();

    /**
     * @return Cache
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }

        if (_PS_CACHE_ENABLED_) {
            $caching_system = _PS_CACHING_SYSTEM_;
            self::$instance = new $caching_system();
        }
        else {
            self::$instance = new CacheArray();
        }
        return self::$instance;
    }

    /**
     * Cache a data
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    protected function _set($key, $value, $ttl = 0)
    {
        return $this->set($key, $value, $ttl);
    }

    /**
     * Retrieve a cached data by key
     *
     * @param string $key
     * @return mixed
     */
    protected function _get($key)
    {
        return $this->get($key);
    }

    /**
     * Check if a data is cached by key
     *
     * @param string $key
     * @return bool
     */
    protected function _exists($key)
    {
        return $this->exists($key);
    }

    /**
     * Delete a data from the cache by key
     *
     * @param string $key
     * @return bool
     */
    protected function _delete($key)
    {
        return self::getInstance()->delete($key);
    }
    
    public static function store($key, $value)
    {
        return self::getInstance()->set($key, $value);
    }

    public static function retrieve($key)
    {
        return self::getInstance()->get($key);
    }

    public static function retrieveAll()
    {
        return array();
    }

    public static function isStored($key)
    {
        return self::getInstance()->exists($key);
    }

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

    /**
     * Store a query in cache
     *
     * @param string $query
     * @param array $result
     */
    public function setQuery($query, $result)
    {
        if ($this->isBlacklist($query)) {
            return true;
        }

        if (empty($result) || $result === false) {
            $result = array();
        }

        if (is_null($this->sql_tables_cached)) {
            $this->sql_tables_cached = $this->get(Tools::hashIV(self::SQL_TABLES_NAME));
            if (!is_array($this->sql_tables_cached)) {
                $this->sql_tables_cached = array();
            }
        }

        // Store query results in cache
        $key = Tools::hashIV($query);
        // no need to check the key existence before the set : if the query is already
        // in the cache, setQuery is not invoked
        $this->set($key, $result);

        // Get all table from the query and save them in cache
        if ($tables = $this->getTables($query)) {
            foreach ($tables as $table) {
                if (!isset($this->sql_tables_cached[$table][$key])) {
                    $this->adjustTableCacheSize($table);
                    $this->sql_tables_cached[$table][$key] = true;
                }
            }
        }
        $this->set(Tools::hashIV(self::SQL_TABLES_NAME), $this->sql_tables_cached);
    }

    /**
     * Autoadjust the table cache size to avoid storing too big elements in the cache
     *
     * @param $table
     */
    protected function adjustTableCacheSize($table)
    {
        if (isset($this->sql_tables_cached[$table])
            && count($this->sql_tables_cached[$table]) > 5000) {
            // make sure the cache doesn't contains too many elements : delete the first 1000
            $table_buffer = array_slice($this->sql_tables_cached[$table], 0, 1000, true);
            foreach ($table_buffer as $fs_key => $value) {
                $this->delete($fs_key);
                $this->delete($fs_key.'_nrows');
                unset($this->sql_tables_cached[$table][$fs_key]);
            }
        }
    }

    protected function getTables($string)
    {
        if (preg_match_all('/(?:from|join|update|into)\s+`?('._DB_PREFIX_.'[0-9a-z_-]+)(?:`?\s{0,},\s{0,}`?('._DB_PREFIX_.'[0-9a-z_-]+)`?)?(?:`|\s+|\Z)(?!\s*,)/Umsi', $string, $res)) {
            foreach ($res[2] as $table) {
                if ($table != '') {
                    $res[1][] = $table;
                }
            }
            return array_unique($res[1]);
        } else {
            return false;
        }
    }

    /**
     * Delete a query from cache
     *
     * @param string $query
     */
    public function deleteQuery($query)
    {
        if ($this->isBlacklist($query)) {
            return;
        }

        if (is_null($this->sql_tables_cached)) {
            $this->sql_tables_cached = $this->get(Tools::hashIV(self::SQL_TABLES_NAME));
            if (!is_array($this->sql_tables_cached)) {
                $this->sql_tables_cached = array();
            }
        }

        if ($tables = $this->getTables($query)) {
            foreach ($tables as $table) {
                if (isset($this->sql_tables_cached[$table])) {
                    foreach (array_keys($this->sql_tables_cached[$table]) as $fs_key) {
                        $this->delete($fs_key);
                        $this->delete($fs_key.'_nrows');
                    }
                    unset($this->sql_tables_cached[$table]);
                }
            }
        }
        $this->set(Tools::hashIV(self::SQL_TABLES_NAME), $this->sql_tables_cached);
    }

    /**
     * Check if a query contain blacklisted tables
     *
     * @param string $query
     * @return bool
     */
    protected function isBlacklist($query)
    {
        foreach ($this->blacklist as $find) {
            if (false !== strpos($query, _DB_PREFIX_.$find)) {
                return true;
            }
        }
        return false;
    }

    public static function clean($key)
    {
        if (strpos($key, '*') !== false) {
            $regexp = str_replace('\\*', '.*', preg_quote($key, '#'));
            foreach (array_keys(self::retrieveAll()) as $key => $value) {
                if (preg_match('#^'.$regexp.'$#', $key)) {
                    $this->delete($key);
                }
            }
        } else {
            $this->delete($key);
        }
    }
}
