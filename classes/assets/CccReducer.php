<?php

/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use Symfony\Component\Filesystem\Filesystem;

class CccReducerCore
{
    private $cacheDir;
    protected $filesystem;

    use PrestaShop\PrestaShop\Adapter\Assets\AssetUrlGeneratorTrait;

    public function __construct($cacheDir, ConfigurationInterface $configuration, Filesystem $filesystem)
    {
        $this->cacheDir = $cacheDir;
        $this->configuration = $configuration;
        $this->filesystem = $filesystem;

        if (!is_dir($this->cacheDir)) {
            $this->filesystem->mkdir($this->cacheDir);
        }
    }

    public function reduceCss($cssFileList)
    {
        $files = array();
        foreach ($cssFileList['external'] as $css) {
            if ('all' === $css['media']) {
                $files[] = $this->getPathFromUri($css['path']);
            }
        }

        $cccFilename = 'theme-'.$this->getFileNameIdentifierFromList($files).'.css';
        $destinationPath = $this->cacheDir.$cccFilename;

        if (!$this->filesystem->exists($destinationPath)) {
            CssMinifier::minify($files, $destinationPath);
        }

        $cssFileList['external'] = [
            'theme-ccc' => [
                "id" => "theme-ccc",
                "type" => "external",
                "path" => $destinationPath,
                "uri" => $this->getFQDN().$this->getUriFromPath($destinationPath),
                "media" => "all",
                "priority" => StylesheetManager::DEFAULT_PRIORITY,
            ]
        ];

        return $cssFileList;
    }

    public function reduceJs($jsFileList)
    {
        foreach ($jsFileList as $position => &$list) {
            $files = array();
            foreach ($list['external'] as $key => $js) {
                // We only CCC the file without 'refer' or 'async'
                if ('' === $js['attribute']) {
                    $files[] = $this->getPathFromUri($js['path']);
                    unset($list['external'][$key]);
                }
            }

            if (empty($files)) {
                // No file to CCC
                continue;
            }

            $cccFilename = $position.'-'.$this->getFileNameIdentifierFromList($files).'.js';
            $destinationPath = $this->cacheDir.$cccFilename;

            if (!$this->filesystem->exists($destinationPath)) {
                JsMinifier::minify($files, $destinationPath);
            }

            $cccItem = [];
            $cccItem[$position.'-js-ccc'] = [
                'id' => $position.'-js-ccc',
                'type' => 'external',
                'path' => $destinationPath,
                'uri' => $this->getFQDN().$this->getUriFromPath($destinationPath),
                'priority' => JavascriptManager::DEFAULT_PRIORITY,
                'attribute' => '',
            ];
            $list['external'] = array_merge($cccItem, $list['external']);
        }

        return $jsFileList;
    }

    private function getFileNameIdentifierFromList(array $files)
    {
        return substr(sha1(implode('|', $files)), 0, 6);
    }
}
